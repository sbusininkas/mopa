<?php

namespace App\Http\Controllers;

use App\Models\LoginKey;
use App\Models\School;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableGroup;
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
            'is_priority' => 'nullable|boolean',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:login_keys,id',
        ]);
        $validated['is_priority'] = $request->has('is_priority');
        
        // Update group
        $group->update(collect($validated)->except(['student_ids'])->toArray());
        
        // Sync students if provided
        if (isset($validated['student_ids'])) {
            $group->students()->sync($validated['student_ids']);
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

    public function assignStudents(Request $request, School $school, Timetable $timetable, TimetableGroup $group)
    {
        abort_unless($timetable->school_id === $school->id && $group->timetable_id === $timetable->id, 404);
        $validated = $request->validate([
            'login_key_ids' => 'nullable|array',
            'login_key_ids.*' => 'exists:login_keys,id',
        ]);

        $ids = $validated['login_key_ids'] ?? [];
        // sync to fully reflect selection, allowing removal when empty
        $group->students()->sync($ids);
        return redirect()->back()->with('success', 'Priskyrimai atnaujinti');
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
}
