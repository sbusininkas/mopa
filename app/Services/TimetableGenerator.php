<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableGroup;
use App\Models\Teacher; // If exists
use App\Models\Room; // If exists
use Illuminate\Support\Arr;

class TimetableGenerator
{
    /**
     * Generate timetable with simple greedy heuristic.
     * Accepts a progress callback receiving integer percent (0..100).
     */
    public function generate(Timetable $timetable, ?callable $progressCallback = null): array
    {
        $days = ['Mon','Tue','Wed','Thu','Fri'];
        $dayCaps = [
            'Mon' => $timetable->max_lessons_monday ?? 9,
            'Tue' => $timetable->max_lessons_tuesday ?? 9,
            'Wed' => $timetable->max_lessons_wednesday ?? 9,
            'Thu' => $timetable->max_lessons_thursday ?? 9,
            'Fri' => $timetable->max_lessons_friday ?? 9,
        ];

        // Fetch groups to schedule, sorted by priority DESC (highest priority first, nulls last)
        $groups = TimetableGroup::query()
            ->where('timetable_id', $timetable->id)
            ->with(['subject','teacherLoginKey','room','students'])
            ->orderByRaw('CASE WHEN priority IS NULL THEN 1 ELSE 0 END, priority DESC')
            ->get();

        $teacherBusy = [];
        $roomBusy = [];
        $studentBusy = [];
        // Track how many times a specific group (subject instance for a class) is placed per day
        // We previously tracked by subject_id globally, which incorrectly limited teachers.
        $subjectCountPerDay = [];
        $assignments = [];
        $totalUnits = 0;
        foreach ($groups as $g) {
            $totalUnits += max(1, (int)($g->lessons_per_week ?? 1));
        }
        // Attempt multi-run (5 attempts) and choose best (least unscheduled, then least conflicts)
        $attemptsTotal = 5;
        $best = null;
        $bestScore = null;
        $attemptSummaries = [];

        for ($attempt = 1; $attempt <= $attemptsTotal; $attempt++) {
            // Reset per-attempt state
            $teacherBusy = [];
            $roomBusy = [];
            $studentBusy = [];
            $subjectCountPerDay = [];
            $assignmentsAttempt = [];
            $remaining = [];
            foreach ($groups as $g) { $remaining[$g->id] = max(1, (int)($g->lessons_per_week ?? 1)); }
            $placedUnitsAttempt = 0;
            $reasonCounters = [];
            $passes = 0; // refinement passes inside one attempt
            while ($passes < 3) { // keep internal refinement at 3
                foreach ($groups as $group) {
                    $target = $remaining[$group->id] ?? 0;
                    if ($target <= 0) continue;
                    $placedForGroup = 0;
                    $dayOrder = $days;
                    if ($passes > 0) { $dayOrder = $days; shuffle($dayOrder); }
                    $loopGuard = 0;
                    while ($placedForGroup < $target && $loopGuard < ($target * 3)) {
                        foreach ($dayOrder as $day) {
                            if ($placedForGroup >= $target) break;
                            $subjectCountPerDay[$day] ??= [];
                            $subjectCountPerDay[$day][$group->id] = $subjectCountPerDay[$day][$group->id] ?? 0;
                            $maxSame = $timetable->max_same_subject_per_day ?? 2;
                            if ($subjectCountPerDay[$day][$group->id] >= $maxSame) {
                                $reasonCounters[$group->id]['subject_limit'] = ($reasonCounters[$group->id]['subject_limit'] ?? 0) + 1;
                                continue;
                            }
                            $slotList = range(1, $dayCaps[$day]);
                            // For high priority groups (priority >= 3), prefer slots 1-5 first
                            if (($group->priority ?? 0) >= 3) {
                                $earlySlots = array_filter($slotList, fn($s) => $s >= 1 && $s <= 5);
                                $lateSlots = array_filter($slotList, fn($s) => $s > 5);
                                $slotList = array_merge($earlySlots, $lateSlots);
                            }
                            if ($passes > 0) shuffle($slotList);
                            $foundSlot = false;
                            foreach ($slotList as $slot) {
                                if ($group->teacher_login_key_id && isset($teacherBusy[$day][$slot][$group->teacher_login_key_id])) { $reasonCounters[$group->id]['teacher_conflict'] = ($reasonCounters[$group->id]['teacher_conflict'] ?? 0) + 1; continue; }
                                if ($group->room_id && isset($roomBusy[$day][$slot][$group->room_id])) { $reasonCounters[$group->id]['room_conflict'] = ($reasonCounters[$group->id]['room_conflict'] ?? 0) + 1; continue; }
                                $conflict = false;
                                foreach ($group->students as $student) {
                                    if (isset($studentBusy[$day][$slot][$student->id])) { $conflict = true; break; }
                                }
                                if ($conflict) { $reasonCounters[$group->id]['student_conflict'] = ($reasonCounters[$group->id]['student_conflict'] ?? 0) + 1; continue; }
                                $assignmentsAttempt[] = [
                                    'timetable_id' => $timetable->id,
                                    'timetable_group_id' => $group->id,
                                    'day' => $day,
                                    'slot' => $slot,
                                ];
                                if ($group->teacher_login_key_id) { $teacherBusy[$day][$slot][$group->teacher_login_key_id] = true; }
                                if ($group->room_id) { $roomBusy[$day][$slot][$group->room_id] = true; }
                                foreach ($group->students as $student) { $studentBusy[$day][$slot][$student->id] = true; }
                                $subjectCountPerDay[$day][$group->id]++;
                                $placedForGroup++;
                                $placedUnitsAttempt++;
                                $remaining[$group->id]--;
                                if ($progressCallback) {
                                    $percent = (int) floor((($attempt - 1) + ($placedUnitsAttempt / max(1,$totalUnits))) / $attemptsTotal * 100);
                                    $progressCallback(min(100,$percent));
                                }
                                $foundSlot = true;
                                break;
                            }
                            if (!$foundSlot) {
                                $reasonCounters[$group->id]['no_slot'] = ($reasonCounters[$group->id]['no_slot'] ?? 0) + 1;
                            }
                        }
                        $loopGuard++;
                    }
                }
                $still = array_sum(array_filter($remaining, fn($v) => $v > 0));
                if ($still === 0) break;
                $passes++;
            }
            // Compile attempt metrics
            $unscheduled = [];
            $unscheduledUnits = 0;
            $reasonSummary = [
                'teacher_conflict' => 0,
                'room_conflict' => 0,
                'student_conflict' => 0,
                'subject_limit' => 0,
                'no_slot' => 0,
            ];
            foreach ($groups as $group) {
                if (($remaining[$group->id] ?? 0) > 0) {
                    $unscheduledUnits += $remaining[$group->id];
                    $reasons = $reasonCounters[$group->id] ?? [];
                    foreach ($reasons as $rk => $rv) { if (isset($reasonSummary[$rk])) { $reasonSummary[$rk] += $rv; } }
                    $reasonLabelsLt = [
                        'teacher_conflict' => 'Mokytojas užimtas',
                        'room_conflict' => 'Kabinetas užimtas',
                        'student_conflict' => 'Mokiniai užimti',
                        'subject_limit' => 'Per daug tos pačios pamokos tą dieną',
                        'no_slot' => 'Nerastas tinkamas laikas',
                    ];
                    $reasonsTranslated = [];
                    foreach ($reasons as $rk => $rv) {
                        $reasonsTranslated[$rk] = [ 'label' => $reasonLabelsLt[$rk] ?? $rk, 'count' => $rv ];
                    }
                    $unscheduled[] = [
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'subject_id' => $group->subject_id,
                        'subject_name' => $group->subject?->name,
                        'teacher_login_key_id' => $group->teacher_login_key_id,
                        'teacher_name' => $group->teacherLoginKey?->full_name,
                        'remaining_lessons' => $remaining[$group->id],
                        'requested_lessons' => max(1, (int)($group->lessons_per_week ?? 1)),
                        'reasons' => $reasons,
                        'reasons_translated' => $reasonsTranslated,
                    ];
                }
            }
            $reasonLabelsLt = [
                'teacher_conflict' => 'Mokytojas užimtas',
                'room_conflict' => 'Kabinetas užimtas',
                'student_conflict' => 'Mokiniai užimti',
                'subject_limit' => 'Per daug tos pačios pamokos tą dieną',
                'no_slot' => 'Nerastas tinkamas laikas',
            ];
            $reasonSummaryTranslated = [];
            foreach ($reasonSummary as $k => $v) {
                $reasonSummaryTranslated[$k] = [ 'label' => $reasonLabelsLt[$k] ?? $k, 'count' => $v ];
            }
            $conflictScore = ($unscheduledUnits * 10000) + array_sum($reasonSummary); // prioritize unscheduled
            $attemptSummaries[] = [
                'attempt' => $attempt,
                'passes' => $passes + 1,
                'placed_units' => $placedUnitsAttempt,
                'unscheduled_units' => $unscheduledUnits,
                'conflict_score' => $conflictScore,
                'reason_summary' => $reasonSummary,
                'reason_summary_translated' => $reasonSummaryTranslated,
            ];
            if ($best === null || $conflictScore < $bestScore) {
                $best = [
                    'assignments' => $assignmentsAttempt,
                    'unscheduled' => $unscheduled,
                    'unscheduled_units' => $unscheduledUnits,
                    'reason_summary' => $reasonSummary,
                    'reason_summary_translated' => $reasonSummaryTranslated,
                    'placed_units' => $placedUnitsAttempt,
                    'passes' => $passes + 1,
                    'attempt' => $attempt,
                ];
                $bestScore = $conflictScore;
            }
        }
        // Persist only best attempt
        if (method_exists($timetable, 'slots')) {
            $timetable->slots()->delete();
            if (!empty($best['assignments'])) {
                $timetable->slots()->insert($best['assignments']);
            }
        }
        return [
            'unscheduled' => $best['unscheduled'],
            'passes' => $best['passes'],
            'attempts' => $attemptsTotal,
            'best_attempt' => $best['attempt'],
            'attempt_summaries' => $attemptSummaries,
            'total_units' => $totalUnits,
            'placed_units' => $best['placed_units'],
            'unscheduled_units' => $best['unscheduled_units'],
            'reason_summary' => $best['reason_summary'],
            'reason_summary_translated' => $best['reason_summary_translated'],
        ];
    }
}
