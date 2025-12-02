<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Timetable;
use App\Jobs\GenerateTimetableJob;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TimetableController extends Controller
{
    use AuthorizesRequests;
    public function index(School $school)
    {
        $user = auth()->user();
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        if (!$user->isSupervisor()) {
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            if (!$activeSchoolId) {
                session(['active_school_id' => $school->id]);
            }
        }
        $timetables = $school->timetables()->orderByDesc('is_public')->orderBy('name')->get();
        return view('admin.timetables.index', compact('school', 'timetables'));
    }

    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $timetable = $school->timetables()->create($validated + ['is_public' => false]);

        return redirect()->route('schools.timetables.show', [$school, $timetable])
            ->with('success', 'Tvarkaraštis sukurtas');
    }

    public function show(School $school, Timetable $timetable)
    {
        $user = auth()->user();
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        if (!$user->isSupervisor()) {
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            if (!$activeSchoolId) {
                session(['active_school_id' => $school->id]);
            }
        }
        abort_unless($timetable->school_id === $school->id, 404);
        // Groups will be loaded via AJAX
        return view('admin.timetables.show', compact('school', 'timetable'));
    }

    public function addRandomGroups(Timetable $timetable)
    {
        $school = $timetable->school;
        $user = auth()->user();
        
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }

        // Run the seeder logic for this timetable's school
        $seeder = new \Database\Seeders\ClassesAndStudentsSeeder();
        $seeder->run();

        return redirect()->route('schools.timetables.show', [$school, $timetable])
            ->with('success', 'Random grupės sėkmingai pridėtos!');
    }

    /**
     * Teachers' timetable grid view.
     * Rows: numbering + teacher names; Columns: Monday..Friday, each 9 lessons.
     * Simple distribution: assign each group's lessons across week days round-robin and earliest free lesson slot.
     */
    public function teachersView(School $school, Timetable $timetable)
    {
        $user = auth()->user();
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        if (!$user->isSupervisor()) {
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            if (!$activeSchoolId) {
                session(['active_school_id' => $school->id]);
            }
        }
        abort_unless($timetable->school_id === $school->id, 404);

        $days = ['Mon' => 'Pirmadienis', 'Tue' => 'Antradienis', 'Wed' => 'Trečiadienis', 'Thu' => 'Ketvirtadienis', 'Fri' => 'Penktadienis'];
        // Determine per-day lesson caps from timetable settings
        $dayCaps = [
            'Mon' => $timetable->max_lessons_monday ?? 9,
            'Tue' => $timetable->max_lessons_tuesday ?? 9,
            'Wed' => $timetable->max_lessons_wednesday ?? 9,
            'Thu' => $timetable->max_lessons_thursday ?? 9,
            'Fri' => $timetable->max_lessons_friday ?? 9,
        ];

        // Collect teachers present in this timetable from groups
        $groups = $timetable->groups()->with(['subject', 'teacherLoginKey', 'room'])->get();
        $teachers = $groups->pluck('teacherLoginKey')->filter()->unique('id')->values();

        // Initialize slots map from persisted TimetableSlot entries
        $persisted = $timetable->slots()->with(['group.subject', 'group.room', 'group.teacherLoginKey'])->get();
        // Structure: [teacher_id][day_code][slot_index] => payload
        $slots = [];
        foreach ($teachers as $t) {
            $tid = $t->id;
            $slots[$tid] = [];
            foreach (array_keys($days) as $code) {
                $slots[$tid][$code] = array_fill(1, $dayCaps[$code], null);
            }
        }
        foreach ($persisted as $ps) {
            $g = $ps->group;
            if (!$g || !$g->teacherLoginKey) { continue; }
            $tid = $g->teacherLoginKey->id;
            $dayCode = $ps->day; // expects 'Mon','Tue',...
            $slotIndex = (int)$ps->slot;
            if (!isset($slots[$tid][$dayCode][$slotIndex])) {
                // ensure teacher and day are initialized even if no groups found
                $slots[$tid] = $slots[$tid] ?? [];
                $slots[$tid][$dayCode] = $slots[$tid][$dayCode] ?? array_fill(1, $dayCaps[$dayCode] ?? 9, null);
            }
            $slots[$tid][$dayCode][$slotIndex] = [
                'slot_id' => $ps->id,
                'group_id' => $g->id,
                'group' => $g->name,
                'subject' => $g->subject?->name,
                'room_number' => $g->room?->number,
                'room_name' => $g->room?->name,
                'teacher_id' => $tid,
                'teacher_name' => $g->teacherLoginKey?->full_name,
            ];
        }

        // Extract unscheduled groups from generation_report
        $unscheduled = [];
        if (is_array($timetable->generation_report) && !empty($timetable->generation_report['unscheduled'])) {
            foreach ($timetable->generation_report['unscheduled'] as $u) {
                if (($u['remaining_lessons'] ?? 0) > 0) { $unscheduled[] = $u; }
            }
        }

        return view('admin.timetables.teachers-view', [
            'school' => $school,
            'timetable' => $timetable,
            'days' => $days,
            'dayCaps' => $dayCaps,
            'teachers' => $teachers,
            'slots' => $slots,
            'unscheduled' => $unscheduled,
        ]);
    }

    /**
     * Single teacher timetable view: rows = lesson numbers, columns = days.
     */
    public function teacherView(School $school, Timetable $timetable, int $teacher)
    {
        $user = auth()->user();
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        if (!$user->isSupervisor()) {
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            if (!$activeSchoolId) {
                session(['active_school_id' => $school->id]);
            }
        }
        abort_unless($timetable->school_id === $school->id, 404);

        $days = ['Mon' => 'Pirmadienis', 'Tue' => 'Antradienis', 'Wed' => 'Trečiadienis', 'Thu' => 'Ketvirtadienis', 'Fri' => 'Penktadienis'];
        $dayCaps = [
            'Mon' => $timetable->max_lessons_monday ?? 9,
            'Tue' => $timetable->max_lessons_tuesday ?? 9,
            'Wed' => $timetable->max_lessons_wednesday ?? 9,
            'Thu' => $timetable->max_lessons_thursday ?? 9,
            'Fri' => $timetable->max_lessons_friday ?? 9,
        ];

        // Fetch teacher
        $teacherModel = $school->loginKeys()->where('type','teacher')->findOrFail($teacher);

        // Initialize empty grid [slot][day]
        $grid = [];
        $maxRows = max($dayCaps);
        for ($i=1;$i<=$maxRows;$i++){ $grid[$i] = []; }

        // Pull all slots for this teacher
        $slots = $timetable->slots()
            ->whereHas('group', function($q) use ($teacher){ $q->where('teacher_login_key_id', $teacher); })
            ->with(['group.subject','group.room'])
            ->get();

        foreach ($slots as $s) {
            $g = $s->group;
            if (!$g) continue;
            $grid[(int)$s->slot][$s->day] = [
                'slot_id' => $s->id,
                'group_id' => $g->id,
                'group' => $g->name,
                'subject' => $g->subject?->name,
                'room' => $g->room?->number ? ($g->room->number.' '.$g->room->name) : null,
                'teacher_id' => $teacher,
            ];
        }

        // Unscheduled groups filtered for this teacher
        $unscheduled = [];
        if (is_array($timetable->generation_report) && !empty($timetable->generation_report['unscheduled'])) {
            foreach ($timetable->generation_report['unscheduled'] as $u) {
                if (($u['remaining_lessons'] ?? 0) > 0 && ($u['teacher_login_key_id'] ?? null) == $teacher) {
                    $unscheduled[] = $u;
                }
            }
        }

        return view('admin.timetables.teacher-view', [
            'school' => $school,
            'timetable' => $timetable,
            'teacher' => $teacherModel,
            'days' => $days,
            'dayCaps' => $dayCaps,
            'maxRows' => $maxRows,
            'grid' => $grid,
            'unscheduled' => $unscheduled,
        ]);
    }

    public function unscheduled(School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $unscheduled = [];
        if (is_array($timetable->generation_report) && isset($timetable->generation_report['unscheduled'])) {
            foreach ($timetable->generation_report['unscheduled'] as $u) {
                if (($u['remaining_lessons'] ?? 0) > 0) { $unscheduled[] = $u; }
            }
        }
        return response()->json(['data' => $unscheduled]);
    }

    public function storeManualSlot(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $validated = $request->validate([
            'group_id' => 'required|integer',
            'day' => 'required|string',
            'slot' => 'required|integer|min:1',
            'teacher_id' => 'required|integer',
        ]);
        $group = $timetable->groups()->with(['subject','teacherLoginKey','students','room'])->find($validated['group_id']);
        if (!$group) { return response()->json(['error' => 'Grupė nerasta'], 404); }
        if (!$group->teacherLoginKey || $group->teacherLoginKey->id != $validated['teacher_id']) {
            return response()->json(['error' => 'Grupės mokytojas nesutampa su eilute'], 422);
        }
        // Check already scheduled lessons count
        $scheduledCount = $timetable->slots()->where('timetable_group_id', $group->id)->count();
        $needed = max(1,(int)($group->lessons_per_week ?? 1));
        if ($scheduledCount >= $needed) {
            return response()->json(['error' => 'Šiai grupei jau suplanuotos visos pamokos'], 422);
        }
        $day = $validated['day'];
        $slot = (int)$validated['slot'];
        // Conflict checks (teacher, room, students per same day+slot)
        $existing = $timetable->slots()->where('day',$day)->where('slot',$slot)->with(['group.teacherLoginKey','group.room','group.students','group.subject'])->get();
        foreach ($existing as $ex) {
            $eg = $ex->group;
            if ($eg && $eg->teacher_login_key_id === $group->teacher_login_key_id) {
                return response()->json(['error' => 'Mokytojas tuo laiku užimtas'], 422);
            }
            if ($eg && $group->room_id && $eg->room_id === $group->room_id) {
                $roomName = $group->room ? ($group->room->number . ' ' . $group->room->name) : 'Kabinetas';
                $occupantGroup = $eg->name ?? 'kita grupė';
                $occupantTeacher = $eg->teacherLoginKey?->full_name ?? 'nežinomas mokytojas';
                $occupantSubject = $eg->subject?->name ?? null;
                $parts = ['grupė ' . $occupantGroup];
                if ($occupantSubject) { $parts[] = 'dalykas ' . $occupantSubject; }
                $parts[] = 'mokytojas ' . $occupantTeacher;
                return response()->json(['error' => $roomName . ' tuo metu užimtas: ' . implode(', ', $parts)], 422);
            }
            if ($eg) {
                $studentIds = $group->students->pluck('id')->flip();
                $conflictingStudents = [];
                foreach ($eg->students as $st) {
                    if (isset($studentIds[$st->id])) {
                        $conflictingStudents[] = $st->full_name . ' (' . $eg->name . ')';
                    }
                }
                if (!empty($conflictingStudents)) {
                    return response()->json([
                        'error' => 'Užimti mokiniai: ' . implode(', ', $conflictingStudents)
                    ], 422);
                }
            }
        }
        // Subject per day limit (per group id rule)
        $sameDayCount = $timetable->slots()->where('timetable_group_id',$group->id)->where('day',$day)->count();
        $maxSame = $timetable->max_same_subject_per_day ?? 2;
        if ($sameDayCount >= $maxSame) {
            return response()->json(['error' => 'Viršytas pamokų skaičius tos pačios disciplinos tą dieną'], 422);
        }
        // Insert slot
        $slotModel = $timetable->slots()->create([
            'timetable_group_id' => $group->id,
            'day' => $day,
            'slot' => $slot,
        ]);
        // Update generation_report (decrement remaining)
        $report = $timetable->generation_report ?? [];
        if (isset($report['unscheduled'])) {
            foreach ($report['unscheduled'] as &$u) {
                if (($u['group_id'] ?? null) == $group->id && ($u['remaining_lessons'] ?? 0) > 0) {
                    $u['remaining_lessons'] = max(0, $u['remaining_lessons'] - 1);
                }
            }
            unset($u);
            // Recalculate counts
            $report['unscheduled_units'] = 0;
            $filtered = [];
            foreach ($report['unscheduled'] as $entry) {
                $report['unscheduled_units'] += $entry['remaining_lessons'] ?? 0;
                if (($entry['remaining_lessons'] ?? 0) > 0) { $filtered[] = $entry; }
            }
            $report['unscheduled'] = $filtered;
            $report['unscheduled_count'] = count($filtered);
            $timetable->update(['generation_report' => $report]);
        }
        return response()->json([
            'success' => true,
            'html' => [
                'group' => $group->name,
                'subject' => $group->subject?->name,
                'room' => $group->room?->number ? ($group->room->number.' '.$group->room->name) : null,
                'slot_id' => $slotModel->id,
                'teacher_id' => $group->teacherLoginKey?->id,
                'teacher_name' => $group->teacherLoginKey?->full_name,
            ],
        ]);
    }

    public function moveSlot(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $validated = $request->validate([
            'slot_id' => 'required|integer',
            'day' => 'required|string',
            'slot' => 'required|integer|min:1',
            'teacher_id' => 'required|integer',
            'swap' => 'nullable|boolean', // Allow swap operation
        ]);
        $slotModel = $timetable->slots()->with(['group.subject','group.room','group.teacherLoginKey','group.students'])->find($validated['slot_id']);
        if (!$slotModel) {
            return response()->json(['error' => 'Pamoka nerasta'], 404);
        }
        $group = $slotModel->group;
        if (!$group || !$group->teacherLoginKey) {
            return response()->json(['error' => 'Grupė arba mokytojas nerastas'], 404);
        }
        // Teacher row must match group's teacher
        if ((int)$group->teacherLoginKey->id !== (int)$validated['teacher_id']) {
            return response()->json(['error' => 'Šią grupę galima tempti tik ant jos mokytojo eilutės'], 422);
        }
        $day = $validated['day'];
        $slot = (int)$validated['slot'];
        $allowSwap = $validated['swap'] ?? false;
        
        // If moving to same position, no-op
        if ($slotModel->day === $day && (int)$slotModel->slot === $slot) {
            return response()->json(['success' => true, 'html' => [
                'group' => $group->name,
                'subject' => $group->subject?->name,
                'room' => $group->room?->number ? ($group->room->number.' '.$group->room->name) : null,
                'slot_id' => $slotModel->id,
            ]]);
        }
        
        // Check if target position is occupied
        $existingAtTarget = $timetable->slots()->where('day',$day)->where('slot',$slot)
            ->with(['group.teacherLoginKey','group.room','group.students','group.subject'])
            ->get()
            ->filter(fn($s) => $s->id !== $slotModel->id);
        
        // First, check for conflicts at target position (excluding the lesson that might be there)
        $hasConflictAtTarget = false;
        $conflictMessage = null;
        
        foreach ($existingAtTarget as $ex) {
            $eg = $ex->group;
            if ($eg && $eg->teacher_login_key_id === $group->teacher_login_key_id) {
                $hasConflictAtTarget = true;
                $conflictMessage = 'Mokytojas tuo metu užimtas';
                break;
            }
            if ($eg && $group->room_id && $eg->room_id === $group->room_id) {
                $hasConflictAtTarget = true;
                $roomName = $group->room ? ($group->room->number . ' ' . $group->room->name) : 'Kabinetas';
                $occupantGroup = $eg->name ?? 'kita grupė';
                $occupantTeacher = $eg->teacherLoginKey?->full_name ?? 'nežinomas mokytojas';
                $occupantSubject = $eg->subject?->name ?? null;
                $parts = ['grupė ' . $occupantGroup];
                if ($occupantSubject) { $parts[] = 'dalykas ' . $occupantSubject; }
                $parts[] = 'mokytojas ' . $occupantTeacher;
                $conflictMessage = $roomName . ' tuo metu užimtas: ' . implode(', ', $parts);
                break;
            }
            if ($eg) {
                $studentIds = $group->students->pluck('id')->flip();
                $conflictingStudents = [];
                foreach ($eg->students as $st) {
                    if (isset($studentIds[$st->id])) {
                        $conflictingStudents[] = $st->full_name . ' (' . $eg->name . ')';
                    }
                }
                if (!empty($conflictingStudents)) {
                    sort($conflictingStudents);
                    $hasConflictAtTarget = true;
                    $conflictMessage = 'Užimti mokiniai: ' . implode(', ', $conflictingStudents);
                    break;
                }
            }
        }
        
        // If there's a conflict at target, check if we can swap
        if ($hasConflictAtTarget && $existingAtTarget->isNotEmpty() && !$allowSwap) {
            $targetSlot = $existingAtTarget->first();
            $targetGroup = $targetSlot->group;
            
            // Check if swap is possible (validate conflicts if we swap)
            $canSwap = true;
            $swapConflicts = [];
            
            // Check if moving target group to source position causes conflicts
            $sourceDay = $slotModel->day;
            $sourceSlot = $slotModel->slot;
            
            // Check teacher conflict at source for target group
            $conflictsAtSource = $timetable->slots()->where('day', $sourceDay)->where('slot', $sourceSlot)
                ->with(['group.teacherLoginKey','group.room','group.students'])
                ->get()
                ->filter(fn($s) => $s->id !== $targetSlot->id && $s->id !== $slotModel->id);
            
            foreach ($conflictsAtSource as $cs) {
                $cg = $cs->group;
                if ($cg && $targetGroup && $cg->teacher_login_key_id === $targetGroup->teacher_login_key_id) {
                    $canSwap = false;
                    $swapConflicts[] = "Negalima sukeisti: mokytojas {$targetGroup->teacherLoginKey?->full_name} užimtas pradinėje pozicijoje";
                }
                if ($cg && $targetGroup && $targetGroup->room_id && $cg->room_id === $targetGroup->room_id) {
                    $canSwap = false;
                    $roomName = $targetGroup->room ? ($targetGroup->room->number . ' ' . $targetGroup->room->name) : 'Kabinetas';
                    $swapConflicts[] = "Negalima sukeisti: {$roomName} užimtas pradinėje pozicijoje";
                }
            }
            
            if ($canSwap) {
                return response()->json([
                    'needsSwap' => true,
                    'targetGroup' => $targetGroup->name,
                    'targetSubject' => $targetGroup->subject?->name,
                    'message' => $conflictMessage . "\n\nPozicijoje yra grupė \"{$targetGroup->name}\". Ar norite sukeisti vietomis?",
                ], 200);
            } else {
                return response()->json([
                    'error' => implode('; ', $swapConflicts),
                    'cannotSwap' => true,
                ], 422);
            }
        }
        
        // If there's a conflict but position is not occupied, just show error
        if ($hasConflictAtTarget) {
            return response()->json(['error' => $conflictMessage], 422);
        }
        
        // Perform swap if requested
        if ($allowSwap && $existingAtTarget->isNotEmpty()) {
            $targetSlot = $existingAtTarget->first();
            $sourceDay = $slotModel->day;
            $sourceSlot = $slotModel->slot;
            
            // Swap positions
            $targetSlot->day = $sourceDay;
            $targetSlot->slot = $sourceSlot;
            $targetSlot->save();
            
            $slotModel->day = $day;
            $slotModel->slot = $slot;
            $slotModel->save();
            
            return response()->json([
                'success' => true,
                'swapped' => true,
                'html' => [
                    'group' => $group->name,
                    'subject' => $group->subject?->name,
                    'room' => $group->room?->number ? ($group->room->number.' '.$group->room->name) : null,
                    'slot_id' => $slotModel->id,
                    'teacher_name' => $group->teacherLoginKey?->full_name,
                ],
                'swappedHtml' => [
                    'group' => $targetSlot->group->name,
                    'subject' => $targetSlot->group->subject?->name,
                    'room' => $targetSlot->group->room?->number ? ($targetSlot->group->room->number.' '.$targetSlot->group->room->name) : null,
                    'slot_id' => $targetSlot->id,
                    'day' => $sourceDay,
                    'slot' => $sourceSlot,
                    'teacher_name' => $targetSlot->group->teacherLoginKey?->full_name,
                ],
            ]);
        }
        
        // If target position has a lesson but no conflicts, just move and delete the target
        if ($existingAtTarget->isNotEmpty()) {
            foreach ($existingAtTarget as $ex) {
                $ex->delete();
            }
        }
        
        // Subject per day limit for group at target day
        $sameDayCount = $timetable->slots()->where('timetable_group_id',$group->id)->where('day',$day)
            ->where('id','<>',$slotModel->id)->count();
        $maxSame = $timetable->max_same_subject_per_day ?? 2;
        if ($sameDayCount >= $maxSame) {
            return response()->json(['error' => 'Viršytas pamokų skaičius tos pačios disciplinos tą dieną'], 422);
        }
        
        // Update slot position
        $slotModel->day = $day;
        $slotModel->slot = $slot;
        $slotModel->save();
        
        return response()->json([
            'success' => true,
            'html' => [
                'group' => $group->name,
                'subject' => $group->subject?->name,
                'room' => $group->room?->number ? ($group->room->number.' '.$group->room->name) : null,
                'slot_id' => $slotModel->id,
                'teacher_name' => $group->teacherLoginKey?->full_name,
            ],
        ]);
    }

    public function unscheduleSlot(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        
        $validated = $request->validate([
            'slot_id' => 'required|integer',
        ]);
        
        $slotModel = $timetable->slots()->with(['group'])->find($validated['slot_id']);
        if (!$slotModel) {
            return response()->json(['error' => 'Pamoka nerasta'], 404);
        }
        
        $group = $slotModel->group;
        if (!$group) {
            return response()->json(['error' => 'Grupė nerasta'], 404);
        }
        
        // Delete the slot
        $slotModel->delete();
        
        // Update generation_report (increment unscheduled count)
        $report = $timetable->generation_report ?? [];
        
        // Check if group already exists in unscheduled array
        $found = false;
        if (isset($report['unscheduled'])) {
            foreach ($report['unscheduled'] as &$u) {
                if (($u['group_id'] ?? null) == $group->id) {
                    $u['remaining_lessons'] = ($u['remaining_lessons'] ?? 0) + 1;
                    $found = true;
                    break;
                }
            }
            unset($u);
        } else {
            $report['unscheduled'] = [];
        }
        
        // If group not found in unscheduled, add it
        if (!$found) {
            $report['unscheduled'][] = [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'subject_name' => $group->subject?->name,
                'teacher_name' => $group->teacherLoginKey?->full_name,
                'teacher_login_key_id' => $group->teacher_login_key_id,
                'room_number' => $group->room?->number,
                'room_name' => $group->room?->name,
                'remaining_lessons' => 1,
                'reason' => 'Perkelta atgal rankiniu būdu',
            ];
        }
        
        // Recalculate counts
        $report['unscheduled_units'] = 0;
        $report['unscheduled_count'] = count($report['unscheduled']);
        foreach ($report['unscheduled'] as $entry) {
            $report['unscheduled_units'] += $entry['remaining_lessons'] ?? 0;
        }
        
        $timetable->update(['generation_report' => $report]);
        
        // Find the updated entry to get remaining_lessons
        $remainingLessons = 1;
        foreach ($report['unscheduled'] as $u) {
            if (($u['group_id'] ?? null) == $group->id) {
                $remainingLessons = $u['remaining_lessons'];
                break;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Pamoka perkelta į nesuplanuotas',
            'group_id' => $group->id,
            'remaining_lessons' => $remainingLessons,
            'group_data' => [
                'group_name' => $group->name,
                'subject_name' => $group->subject?->name ?? '',
                'teacher_login_key_id' => $group->teacher_login_key_id,
                'teacher_name' => $group->teacherLoginKey?->full_name ?? '',
            ],
        ]);
    }

    public function checkConflict(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $validated = $request->validate([
            'group_id' => 'required|integer',
            'day' => 'required|string',
            'slot' => 'required|integer|min:1',
            'teacher_id' => 'required|integer',
            'temp_room_id' => 'nullable|integer|exists:rooms,id',
        ]);
        
        $group = $timetable->groups()->with(['subject','teacherLoginKey','students','room'])->find($validated['group_id']);
        if (!$group) {
            return response()->json(['hasConflicts' => true, 'conflicts' => ['Grupė nerasta'], 'message' => 'Grupė nerasta']);
        }
        
        // Use temporary room ID if provided, otherwise use group's room
        $checkRoomId = $validated['temp_room_id'] ?? $group->room_id;
        
        $conflicts = [];
        $day = $validated['day'];
        $slot = (int)$validated['slot'];
        
        // CRITICAL: Check if group teacher matches row teacher - this is blocking
        if (!$group->teacherLoginKey) {
            return response()->json([
                'hasConflicts' => true, 
                'conflicts' => ['Grupei nepriskirtas mokytojas'], 
                'message' => 'Grupei nepriskirtas mokytojas'
            ]);
        }
        
        if ($group->teacherLoginKey->id != $validated['teacher_id']) {
            $rowTeacher = \App\Models\LoginKey::find($validated['teacher_id']);
            $rowTeacherName = $rowTeacher ? $rowTeacher->full_name : 'ID: ' . $validated['teacher_id'];
            return response()->json([
                'hasConflicts' => true, 
                'conflicts' => [
                    "Grupės mokytojas ({$group->teacherLoginKey->full_name}) nesutampa su pasirinkta eilute ({$rowTeacherName})",
                    "Šią grupę galima tempti tik ant {$group->teacherLoginKey->full_name} eilutės"
                ], 
                'message' => 'Grupės mokytojas nesutampa su pasirinkta eilute'
            ]);
        }
        
        // Check already scheduled lessons count
        $scheduledCount = $timetable->slots()->where('timetable_group_id', $group->id)->count();
        $needed = max(1,(int)($group->lessons_per_week ?? 1));
        if ($scheduledCount >= $needed) {
            $conflicts[] = 'Šiai grupei jau suplanuotos visos pamokos';
        }
        
        // Conflict checks (teacher, room, students per same day+slot)
        $existing = $timetable->slots()->where('day',$day)->where('slot',$slot)->with(['group.teacherLoginKey','group.room','group.students','group.subject'])->get();
        
        $teacherConflict = false;
        $roomConflict = false;
        $studentConflicts = [];
        $roomConflictDetails = null;
        
        foreach ($existing as $ex) {
            $eg = $ex->group;
            if (!$eg) continue;
            
            // Teacher conflict
            if ($eg->teacher_login_key_id === $group->teacher_login_key_id) {
                $teacherConflict = true;
            }
            
            // Room conflict
            if ($checkRoomId && $eg->room_id === $checkRoomId) {
                $roomConflict = true;
                if ($roomConflictDetails === null) {
                    $roomConflictDetails = [
                        'group' => $eg->name ?? null,
                        'teacher' => $eg->teacherLoginKey?->full_name ?? null,
                        'subject' => $eg->subject?->name ?? null,
                    ];
                }
            }
            
            // Student conflicts
            $studentIds = $group->students->pluck('id')->toArray();
            foreach ($eg->students as $st) {
                if (in_array($st->id, $studentIds)) {
                    $studentConflicts[] = [
                        'name' => $st->full_name ?? "ID: {$st->id}",
                        'group' => $eg->name ?? 'nežinoma grupė',
                        'subject' => $eg->subject?->name ?? '—',
                    ];
                }
            }
        }
        
        if ($teacherConflict) {
            $conflicts[] = "Mokytojas {$group->teacherLoginKey->full_name} tuo metu jau turi pamoką";
        }
        
        if ($roomConflict) {
            // Get room name - use temp room if provided, otherwise use group's room
            $roomName = 'Kabinetas';
            if ($checkRoomId) {
                if (isset($validated['temp_room_id']) && $validated['temp_room_id']) {
                    $tempRoom = \App\Models\Room::find($validated['temp_room_id']);
                    $roomName = $tempRoom ? ($tempRoom->number . ' ' . $tempRoom->name) : 'Kabinetas';
                } else if ($group->room) {
                    $roomName = $group->room->number . ' ' . $group->room->name;
                }
            }
            
            if ($roomConflictDetails) {
                $parts = [];
                if ($roomConflictDetails['group']) { $parts[] = 'grupė ' . $roomConflictDetails['group']; }
                if ($roomConflictDetails['subject']) { $parts[] = 'dalykas ' . $roomConflictDetails['subject']; }
                if ($roomConflictDetails['teacher']) { $parts[] = 'mokytojas ' . $roomConflictDetails['teacher']; }
                $conflicts[] = [
                    'type' => 'room',
                    'message' => $roomName . ' tuo metu užimtas: ' . implode(', ', $parts),
                    'details' => $roomConflictDetails
                ];
            } else {
                $conflicts[] = [
                    'type' => 'room',
                    'message' => $roomName . ' tuo metu užimtas',
                    'details' => null
                ];
            }
        }
        
        if (!empty($studentConflicts)) {
            // Sort students alphabetically
            usort($studentConflicts, function($a, $b) {
                return strcmp($a['name'], $b['name']);
            });
            $conflicts[] = [
                'type' => 'students',
                'message' => 'Užimti mokiniai: ' . implode(', ', array_map(function($sc) {
                    return "{$sc['name']} ({$sc['group']})";
                }, $studentConflicts)),
                'students' => $studentConflicts
            ];
        }
        
        // Subject per day limit (per group id rule)
        $sameDayCount = $timetable->slots()->where('timetable_group_id',$group->id)->where('day',$day)->count();
        $maxSame = $timetable->max_same_subject_per_day ?? 2;
        if ($sameDayCount >= $maxSame) {
            $conflicts[] = "Viršytas pamokų skaičius tos pačios grupės tą dieną ({$sameDayCount}/{$maxSame})";
        }
        
        $hasConflicts = !empty($conflicts);
        
        // Build message from conflicts (handling both string and array formats)
        $messageArray = array_map(function($c) {
            return is_array($c) ? $c['message'] : $c;
        }, $conflicts);
        $message = $hasConflicts ? implode('; ', $messageArray) : 'Konfliktų nerasta';
        
        return response()->json([
            'hasConflicts' => $hasConflicts,
            'conflicts' => $conflicts,
            'message' => $message,
        ]);
    }

    public function generate(Timetable $timetable)
    {
        $user = auth()->user();
        if ($timetable->generation_status === 'running') {
            return back()->with('success', 'Generavimas jau vyksta');
        }
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($timetable->school_id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        if (!$user->isSupervisor()) {
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $timetable->school_id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            if (!$activeSchoolId) {
                session(['active_school_id' => $timetable->school_id]);
            }
        }
        // Set initial generation state so UI can display progress bar immediately
        $timetable->update([
            'generation_status' => 'running',
            'generation_progress' => 0,
            'generation_started_at' => now(),
            'generation_finished_at' => null,
        ]);
        GenerateTimetableJob::dispatch($timetable, auth()->id());
        return back()->with('success', 'Tvarkaraščio generavimas pradėtas');
    }

    public function generationStatus(Timetable $timetable)
    {
        return response()->json([
            'status' => $timetable->generation_status,
            'progress' => (int)$timetable->generation_progress,
            'finished' => $timetable->generation_status === 'completed',
        ]);
    }

    public function update(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_public' => 'nullable',
            'max_lessons_monday' => 'nullable|integer|min:1|max:20',
            'max_lessons_tuesday' => 'nullable|integer|min:1|max:20',
            'max_lessons_wednesday' => 'nullable|integer|min:1|max:20',
            'max_lessons_thursday' => 'nullable|integer|min:1|max:20',
            'max_lessons_friday' => 'nullable|integer|min:1|max:20',
            'max_same_subject_per_day' => 'nullable|integer|min:1|max:20',
        ]);

        // Normalize checkbox to strict boolean
        $validated['is_public'] = $request->has('is_public');
        $timetable->update($validated);

        return redirect()->back()->with('success', 'Tvarkaraštis atnaujintas');
    }

    public function setPublic(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        // unset others
        $school->timetables()->update(['is_public' => false]);
        $timetable->update(['is_public' => true]);
        return redirect()->back()->with('success', 'Viešai rodomas tvarkaraštis nustatytas');
    }

    public function copy(School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $copy = $school->timetables()->create([
            'name' => $timetable->name . ' (kopija)',
            'is_public' => false,
            'copied_from_id' => $timetable->id,
        ]);

        // copy groups
        foreach ($timetable->groups as $group) {
            $newGroup = $copy->groups()->create([
                'name' => $group->name,
                'subject_id' => $group->subject_id,
                'teacher_login_key_id' => $group->teacher_login_key_id,
            ]);
            // do not copy students by default
        }

        return redirect()->route('schools.timetables.show', [$school, $copy])
            ->with('success', 'Tvarkaraštis nukopijuotas');
    }

    public function destroy(School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $timetable->delete();
        return redirect()->route('schools.timetables.index', $school)->with('success', 'Tvarkaraštis pašalintas');
    }
}
