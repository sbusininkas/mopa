<?php

namespace App\Services;

use App\Models\Timetable;
use App\Models\TimetableGroup;

class TimetableGenerator
{
    /**
     * Generate timetable using Python OR-Tools constraint solver.
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
        $maxSameSubjectPerDay = $timetable->max_same_subject_per_day ?? 2;

        // Fetch groups to schedule, sorted by priority DESC (highest priority first, nulls last)
        $groups = TimetableGroup::query()
            ->where('timetable_id', $timetable->id)
            ->with(['subject','teacherLoginKey','room','students'])
            ->orderByRaw('CASE WHEN priority IS NULL THEN 1 ELSE 0 END, priority DESC')
            ->get();

        // Build JSON input for Python solver
        $groupsData = [];
        $totalUnits = 0;
        $existingSlots = [];  // Collect existing slots for locked groups
        
        foreach ($groups as $g) {
            $groupsData[] = [
                'id' => $g->id,
                'lessons_per_week' => (int)max(1, $g->lessons_per_week ?? 1),
                'teacher_id' => $g->teacher_login_key_id,
                'room_id' => $g->room_id,
                'subject_id' => $g->subject_id,
                'priority' => $g->priority,
                'can_merge' => (bool)($g->can_merge_with_same_subject ?? false),
                'student_ids' => $g->students->pluck('id')->toArray(),
                'is_locked' => (bool)($g->is_locked ?? false),
            ];
            $totalUnits += max(1, (int)($g->lessons_per_week ?? 1));
            
            // If group is locked, collect its existing slots
            if ($g->is_locked) {
                $slots = $g->slots()->get();
                foreach ($slots as $slot) {
                    $existingSlots[] = [
                        'timetable_group_id' => $g->id,
                        'day' => $slot->day,
                        'slot' => $slot->slot,
                    ];
                }
            }
        }

        // Collect teacher unavailability: map teacher_id -> day_name -> [[start,end],...]
        $teacherUnavail = [];
        $unavailRecords = $timetable->teacherUnavailabilities()->get();
        $dayMap = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri'];
        foreach ($unavailRecords as $rec) {
            $tid = (string)$rec->teacher_login_key_id;
            $dayName = $dayMap[$rec->day_of_week] ?? null;
            if (!$dayName) continue;
            $teacherUnavail[$tid] ??= [];
            $teacherUnavail[$tid][$dayName] ??= [];
            $teacherUnavail[$tid][$dayName][] = [
                substr($rec->start_time, 0, 5), // HH:MM
                substr($rec->end_time, 0, 5),
            ];
        }

        $inputData = [
            'days' => $days,
            'day_caps' => $dayCaps,
            'groups' => $groupsData,
            'teacher_unavailability' => $teacherUnavail,
            'max_same_subject_per_day' => $maxSameSubjectPerDay,
            'lesson_times' => $timetable->school->lesson_times,
            'existing_slots' => $existingSlots,  // Pass existing slots for locked groups
            'max_time_seconds' => 120,
            'num_workers' => 8,
        ];

        $jsonInput = json_encode($inputData, JSON_UNESCAPED_UNICODE);
        $scriptPath = base_path('python/timetable_solver.py');
        
        // Detect Python executable (try python3 first, fallback to python)
        $pythonCmd = 'python';
        if (PHP_OS_FAMILY === 'Windows') {
            // On Windows, try python first
            exec('python --version 2>&1', $pyOutput, $pyCode);
            if ($pyCode !== 0) {
                // Fallback to py launcher
                exec('py --version 2>&1', $pyOutput, $pyCode);
                if ($pyCode === 0) {
                    $pythonCmd = 'py';
                }
            }
        } else {
            // Unix: try python3 first
            exec('python3 --version 2>&1', $pyOutput, $pyCode);
            if ($pyCode === 0) {
                $pythonCmd = 'python3';
            } else {
                exec('python --version 2>&1', $pyOutput, $pyCode);
                if ($pyCode === 0) {
                    $pythonCmd = 'python';
                }
            }
        }

        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];
        
        // Save input for debugging
        file_put_contents(storage_path('logs/last_timetable_input.json'), $jsonInput);
        
        $process = proc_open("$pythonCmd \"$scriptPath\"", $descriptors, $pipes);
        if (!is_resource($process)) {
            throw new \Exception('Failed to start Python solver process');
        }

        fwrite($pipes[0], $jsonInput);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0 || !$output) {
            throw new \Exception("Python solver failed (exit $exitCode): $error");
        }

        // Extract JSON from output (last line that starts with '{')
        $lines = explode("\n", trim($output));
        $jsonLine = null;
        foreach (array_reverse($lines) as $line) {
            $line = trim($line);
            if ($line && $line[0] === '{') {
                $jsonLine = $line;
                break;
            }
        }
        
        if (!$jsonLine) {
            \Log::error('No JSON found in Python output: ' . $output);
            throw new \Exception("Python solver failed: no JSON output");
        }
        
        $result = json_decode($jsonLine, true);
        if (!$result || !($result['success'] ?? false)) {
            $errMsg = $result['error'] ?? 'Unknown error';
            throw new \Exception("Solver error: $errMsg");
        }

        $assignments = $result['assignments'] ?? [];
        $placedUnits = $result['placed_units'] ?? 0;
        $unplacedInfo = $result['unplaced_groups'] ?? [];

        // Build map of unplaced reasons from solver
        $unplacedReasons = [];
        foreach ($unplacedInfo as $info) {
            $unplacedReasons[$info['group_id']] = $info['reason'] ?? 'Nežinoma priežastis';
        }

        // Calculate unscheduled groups
        $scheduledGroupIds = array_unique(array_column($assignments, 'timetable_group_id'));
        $unscheduled = [];
        $unscheduledUnits = 0;
        foreach ($groups as $g) {
            $requested = max(1, (int)($g->lessons_per_week ?? 1));
            $scheduled = count(array_filter($assignments, fn($a) => $a['timetable_group_id'] === $g->id));
            if ($scheduled < $requested) {
                $remaining = $requested - $scheduled;
                $unscheduledUnits += $remaining;
                
                // Get reason from solver or use generic
                $reason = $unplacedReasons[$g->id] ?? 'Nepakanka tvarkaraščio vietos';
                
                $unscheduled[] = [
                    'group_id' => $g->id,
                    'group_name' => $g->name,
                    'subject_id' => $g->subject_id,
                    'subject_name' => $g->subject?->name,
                    'teacher_login_key_id' => $g->teacher_login_key_id,
                    'teacher_name' => $g->teacherLoginKey?->full_name,
                    'room_id' => $g->room_id,
                    'room_number' => $g->room?->number,
                    'room_name' => $g->room?->name,
                    'remaining_lessons' => $remaining,
                    'requested_lessons' => $requested,
                    'reason_text' => $reason,
                    'reasons' => ['solver_constraint' => 1],
                    'reasons_translated' => [
                        'solver_constraint' => ['label' => $reason, 'count' => 1]
                    ],
                ];
            }
        }

        $reasonSummary = ['solver_insufficient_capacity' => count($unscheduled)];
        $reasonSummaryTranslated = [
            'solver_insufficient_capacity' => ['label' => 'Nepakanka tvarkaraščio vietos', 'count' => count($unscheduled)]
        ];

        // Persist assignments
        $timetable->slots()->delete();
        if (!empty($assignments)) {
            foreach ($assignments as &$a) {
                $a['timetable_id'] = $timetable->id;
            }
            $timetable->slots()->insert($assignments);
        }

        if ($progressCallback) {
            $progressCallback(100);
        }

        return [
            'unscheduled' => $unscheduled,
            'passes' => 1,
            'attempts' => 1,
            'best_attempt' => 1,
            'attempt_summaries' => [],
            'total_units' => $totalUnits,
            'placed_units' => $placedUnits,
            'unscheduled_units' => $unscheduledUnits,
            'reason_summary' => $reasonSummary,
            'reason_summary_translated' => $reasonSummaryTranslated,
        ];
    }
}
