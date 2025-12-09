<?php

namespace App\Http\Controllers;

use App\Models\LoginKey;
use App\Models\School;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableGroup;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;

class TimetableGroupController extends Controller
{
    public function list(School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        
        $groups = $timetable->groups()
            ->with(['subject', 'teacherLoginKey', 'room', 'students'])
            ->get();
        
        // Get scheduled and unscheduled counts for each group
        $groupsData = $groups->map(function($group) use ($timetable) {
            $scheduled = $timetable->slots()
                ->where('timetable_group_id', $group->id)
                ->count();
            $unscheduled = max(0, ($group->lessons_per_week ?? 0) - $scheduled);
            
            return [
                'id' => $group->id,
                'name' => $group->name,
                'subject_name' => $group->subject?->name,
                'teacher_name' => $group->teacherLoginKey?->full_name,
                'room_number' => $group->room?->number,
                'room_name' => $group->room?->name,
                'week_type' => $group->week_type,
                'lessons_per_week' => $group->lessons_per_week ?? 0,
                'is_priority' => $group->is_priority ? true : false,
                'scheduled_count' => $scheduled,
                'unscheduled_count' => $unscheduled,
                'students_count' => $group->students->count(),
                'students' => $group->students->map(fn($s) => [
                    'id' => $s->id,
                    'full_name' => $s->full_name,
                    'class_name' => $s->class_name ?? ''
                ])
            ];
        });
        
        return response()->json([
            'success' => true,
            'groups' => $groupsData
        ]);
    }

    public function store(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_login_key_id' => 'nullable|exists:login_keys,id',
            'room_id' => 'nullable|exists:rooms,id',
            'week_type' => 'required|in:all,even,odd',
            'lessons_per_week' => 'required|integer|min:1|max:20',
            'is_priority' => 'nullable|boolean',
        ]);
        $validated['is_priority'] = $request->has('is_priority');

        $group = $timetable->groups()->create($validated);
        return redirect()->back()->with('success', 'Grupė sukurta');
    }

    public function editData(School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        $group->load(['subject', 'teacherLoginKey', 'room']);
        
        return response()->json([
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'subject_id' => $group->subject_id,
                'teacher_login_key_id' => $group->teacher_login_key_id,
                'room_id' => $group->room_id,
                'week_type' => $group->week_type,
                'lessons_per_week' => $group->lessons_per_week ?? 1,
                'is_priority' => $group->is_priority ? true : false,
            ],
            'subjects' => $school->subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
            'teachers' => $school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->orderBy('first_name')->get()->map(fn($t) => ['id' => $t->id, 'full_name' => $t->full_name]),
            'rooms' => $school->rooms->map(fn($r) => ['id' => $r->id, 'number' => $r->number, 'name' => $r->name]),
        ]);
    }

    public function update(Request $request, School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'teacher_login_key_id' => 'nullable|exists:login_keys,id',
            'room_id' => 'nullable|exists:rooms,id',
            'week_type' => 'required|in:all,even,odd',
            'lessons_per_week' => 'required|integer|min:1|max:20',
            'is_priority' => 'nullable|in:on,true,1',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:login_keys,id',
        ]);
        $validated['is_priority'] = $request->has('is_priority') ? true : false;
        
        // Update group
        $group->update(collect($validated)->except(['student_ids'])->toArray());
        
        // Sync students if provided
        if (isset($validated['student_ids'])) {
            $group->students()->sync($validated['student_ids']);
        }
        
        // Also update generation_report.unscheduled entry so UI reflects new teacher/subject/room
        $timetable->refresh();
        $report = $timetable->generation_report ?? [];
        if (isset($report['unscheduled']) && is_array($report['unscheduled'])) {
            $group->loadMissing(['subject', 'teacherLoginKey.user', 'room']);
            $teacherName = optional($group->teacherLoginKey?->user)->full_name
                ?? ($group->teacherLoginKey->full_name ?? null);
            foreach ($report['unscheduled'] as &$entry) {
                if (($entry['group_id'] ?? null) === $group->id) {
                    $entry['group_name'] = $group->name;
                    $entry['subject_id'] = $group->subject_id;
                    $entry['subject_name'] = $group->subject->name ?? ($entry['subject_name'] ?? null);
                    $entry['teacher_login_key_id'] = $group->teacher_login_key_id;
                    $entry['teacher_name'] = $teacherName ?? ($entry['teacher_name'] ?? null);
                    $entry['room_id'] = $group->room_id;
                    $entry['room_number'] = $group->room->number ?? ($entry['room_number'] ?? null);
                    $entry['room_name'] = $group->room->name ?? ($entry['room_name'] ?? null);
                    // If lessons_per_week changed, keep requested the same as lessons_per_week unless UI set requested separately
                    if (isset($entry['requested_lessons'])) {
                        $entry['requested_lessons'] = $group->lessons_per_week;
                        $entry['total_lessons'] = $group->lessons_per_week;
                        // Do not change remaining here; generation logic controls it
                    }
                    break;
                }
            }
            unset($entry);
            $timetable->update(['generation_report' => $report]);
        }
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Grupė atnaujinta']);
        }
        
        return redirect()->back()->with('success', 'Grupė atnaujinta');
    }

    public function destroy(School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        $group->delete();
        return redirect()->back()->with('success', 'Grupė pašalinta');
    }

    public function show(School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        $group->load(['students', 'subject', 'teacherLoginKey', 'room']);
        
        return response()->json([
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'subject' => $group->subject?->name,
                'teacher' => $group->teacherLoginKey?->full_name,
                'room' => $group->room ? ($group->room->number . ' ' . ($group->room->name ?? '')) : null,
            ],
            'students' => $group->students->map(fn($s) => [
                'id' => $s->id,
                'full_name' => $s->full_name
            ])
        ]);
    }

    public function details(School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);

        $group->load(['students', 'subject', 'teacherLoginKey', 'room']);
        $subjects = $school->subjects()->orderBy('name')->get();
        $teachers = $school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->orderBy('first_name')->get();
        $rooms = $school->rooms()->orderBy('number')->get();
        $allStudents = $school->loginKeys()
            ->where('type', 'student')
            ->leftJoin('classes', 'login_keys.class_id', '=', 'classes.id')
            ->select('login_keys.*')
            ->orderBy('classes.name')
            ->orderBy('login_keys.last_name')
            ->orderBy('login_keys.first_name')
            ->get();

        return view('admin.timetables.group-show', [
            'school' => $school,
            'timetable' => $timetable,
            'group' => $group,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'rooms' => $rooms,
            'allStudents' => $allStudents,
        ]);
    }

    public function assignStudents(Request $request, School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        // Support both student_ids and login_key_ids for backwards compatibility
        // Note: student_ids are actually login_key IDs (student user IDs)
        $validated = $request->validate([
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'login_key_ids' => 'nullable|array',
            'login_key_ids.*' => 'exists:login_keys,id',
            'action' => 'nullable|in:add,remove',
            'ignore_conflict' => 'nullable|boolean'
        ]);

        $ids = $validated['student_ids'] ?? $validated['login_key_ids'] ?? [];
        $action = $validated['action'] ?? 'add';
        $ignoreConflict = $validated['ignore_conflict'] ?? false;
        
        // Check for conflicts if not ignoring them and only when adding
        $conflicts = [];
        if (!$ignoreConflict && !empty($ids) && $action !== 'remove') {
            $conflicts = $this->checkScheduleConflicts($timetable, $group, $ids);
        }
        
        // If there are conflicts and we're not ignoring them, return conflict info
        if (!empty($conflicts) && !$ignoreConflict) {
            if ($request->expectsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'hasConflict' => true,
                    'conflicts' => $conflicts,
                    'message' => 'Yra tvarkaraščio konfliktai'
                ], 200);
            }
        }
        
        if ($action === 'remove') {
            // Remove specific students
            $group->students()->detach($ids);
        } else {
            // Add or sync students
            $group->students()->syncWithoutDetaching($ids);
        }
        
        // If request is AJAX, return JSON
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Priskyrimai atnaujinti'
            ]);
        }
        

        return redirect()->back()->with('success', 'Priskyrimai atnaujinti');
    }

    private function checkScheduleConflicts(Timetable $timetable, TimetableGroup $group, $studentIds)
    {
        $conflicts = [];
        
        // Get all slots for this group
        $groupSlots = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('timetable_group_id', $group->id)
            ->get();
        
        foreach ($studentIds as $studentId) {
            // Get all other slots for this student
            $studentSlots = TimetableSlot::where('timetable_id', $timetable->id)
                ->whereHas('group', function ($q) {
                    $q->whereHas('students');
                })
                ->get()
                ->filter(function ($slot) use ($studentId) {
                    return $slot->group->students()->where('login_key_id', $studentId)->exists();
                });
            
            // Check for overlapping slots
            foreach ($groupSlots as $groupSlot) {
                foreach ($studentSlots as $studentSlot) {
                    if ($groupSlot->day === $studentSlot->day && 
                        $groupSlot->hour === $studentSlot->hour &&
                        $groupSlot->id !== $studentSlot->id) {
                        $conflicts[] = [
                            'student_id' => $studentId,
                            'conflict_slot' => [
                                'day' => $studentSlot->day,
                                'hour' => $studentSlot->hour,
                                'group' => $studentSlot->group->name
                            ]
                        ];
                    }
                }
            }
        }
        
        return $conflicts;
    }

    public function getStudents(School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        $students = $group->students()->get()->map(function($student) {
            return [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'class_name' => $student->class_name ?? ''
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $students
        ]);
    }

    public function copyUnscheduled(Request $request, School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        $validated = $request->validate([
            'unscheduled_count' => 'required|integer|min:1|max:20',
            'name' => 'nullable|string|max:100',
            'teacher_login_key_id' => 'nullable|exists:login_keys,id',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        // Create a copy of the group with only unscheduled lessons
        $newGroup = $timetable->groups()->create([
            'name' => $validated['name'] ?? ($group->name . ' (kopija)'),
            'subject_id' => $group->subject_id,
            'teacher_login_key_id' => $validated['teacher_login_key_id'] ?? $group->teacher_login_key_id,
            'room_id' => $validated['room_id'] ?? null,
            'week_type' => $group->week_type,
            'lessons_per_week' => $validated['unscheduled_count'],
            'is_priority' => $group->is_priority,
            'priority' => $group->priority,
        ]);

        // Copy students from original group
        $studentIds = $group->students()->pluck('login_keys.id')->toArray();
        $newGroup->students()->sync($studentIds);

        return response()->json([
            'success' => true, 
            'message' => 'Grupės kopija sukurta su ' . $validated['unscheduled_count'] . ' pamokomis.',
            'group_id' => $newGroup->id
        ]);
    }

    public function subjectGroups(School $school, Timetable $timetable, string $subject)
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

        // Find subject by name
        $subjectModel = $school->subjects()->where('name', $subject)->first();
        
        // Get all groups for this subject in this timetable
        $groups = $timetable->groups()
            ->where('subject_id', $subjectModel?->id)
            ->with(['subject', 'teacherLoginKey', 'room', 'students'])
            ->get();

        // Prepare group data with scheduled/unscheduled counts
        $groupsData = $groups->map(function($group) use ($timetable) {
            $scheduled = $timetable->slots()
                ->where('timetable_group_id', $group->id)
                ->count();
            $unscheduled = max(0, ($group->lessons_per_week ?? 0) - $scheduled);
            
            // Get unique students for this group
            $uniqueStudents = $group->students()->distinct()->count();
            
            return [
                'id' => $group->id,
                'name' => $group->name,
                'subject_name' => $group->subject?->name,
                'teacher_name' => $group->teacherLoginKey?->full_name,
                'teacher_id' => $group->teacherLoginKey?->id,
                'room_number' => $group->room?->number,
                'room_name' => $group->room?->name,
                'week_type' => $group->week_type,
                'lessons_per_week' => $group->lessons_per_week ?? 0,
                'is_priority' => $group->is_priority ? true : false,
                'scheduled_count' => $scheduled,
                'unscheduled_count' => $unscheduled,
                'students_count' => $uniqueStudents,
            ];
        })->sortBy('name')->values();

        return view('admin.timetables.subject-groups', [
            'school' => $school,
            'timetable' => $timetable,
            'subject' => $subject,
            'groups' => $groupsData,
        ]);
    }

    public function getStudentSchedule(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        
        $studentId = $request->query('student_id');
        if (!$studentId) {
            return response()->json(['success' => false, 'message' => 'Missing student_id'], 400);
        }

        // Get all slots for this student in this timetable
        // Join through timetable_group_student pivot table to find groups this student is in
        $slots = TimetableSlot::where('timetable_id', $timetable->id)
            ->whereHas('group.students', function ($q) use ($studentId) {
                $q->where('login_key_id', $studentId);
            })
            ->get()
            ->map(function ($slot) {
                return [
                    'day' => $slot->day,
                    'hour' => (int)$slot->slot, // Use 'slot' column as hour, cast to int
                    'group_name' => $slot->group->name ?? '',
                    // Ensure we return the correct subject name attribute
                    'subject_name' => optional($slot->group->subject)->name ?? optional($slot->group->subject)->pavadinimas ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'schedule' => $slots
        ]);
    }

    public function getGroupSchedule(Request $request, School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        
        // Get all slots for this group in this timetable
        $slots = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('timetable_group_id', $group->id)
            ->get()
            ->map(function ($slot) {
                return [
                    'day' => $slot->day,
                    'hour' => (int)$slot->slot,
                ];
            });

        return response()->json([
            'success' => true,
            'schedule' => $slots
        ]);
    }

    public function getRoomSchedule(Request $request, School $school, Timetable $timetable)
    {
        abort_unless($timetable->school_id === $school->id, 404);
        
        $roomId = $request->query('room_id');
        if (!$roomId) {
            return response()->json(['success' => false, 'message' => 'Missing room_id'], 400);
        }

        // Get all slots for this room in this timetable
        $slots = TimetableSlot::where('timetable_id', $timetable->id)
            ->whereHas('group', function ($q) use ($roomId) {
                $q->where('room_id', $roomId);
            })
            ->get()
            ->map(function ($slot) {
                return [
                    'day' => $slot->day,
                    'hour' => $slot->hour,
                    'group_name' => $slot->group->name ?? '',
                    'subject_name' => $slot->group->subject->pavadinimas ?? '',
                    'teacher_name' => $slot->group->teacher->full_name ?? '',
                ];
            });

        return response()->json([
            'success' => true,
            'schedule' => $slots
        ]);
    }
}
