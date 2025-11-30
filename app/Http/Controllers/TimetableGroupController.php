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
            'teachers' => $school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->get()->map(fn($t) => ['id' => $t->id, 'full_name' => $t->full_name]),
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
        ]);
        $validated['is_priority'] = $request->has('is_priority');
        $group->update($validated);
        
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
}
