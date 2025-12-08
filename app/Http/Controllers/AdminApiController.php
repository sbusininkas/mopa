<?php

namespace App\Http\Controllers;

use App\Models\LoginKey;
use App\Models\SchoolClass;
use App\Models\Timetable;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminApiController extends Controller
{
    /**
     * Return students (login keys) for a given class id within the current user's school.
     */
    public function studentsByClass($id)
    {
        $user = Auth::user();
        $class = SchoolClass::findOrFail($id);

        // Authorization: supervisors allowed; school admins must match active school and be admin for it
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== (int) $class->school_id || !$user->isSchoolAdmin($class->school_id)) {
                abort(403);
            }
        }

        $students = LoginKey::where('school_id', $class->school_id)
            ->where('type', 'student')
            ->where('class_id', $class->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($lk) {
                return [
                    'id' => $lk->id,
                    'full_name' => $lk->full_name,
                    'class_name' => $lk->class?->name ?? '',
                ];
            });

        return response()->json(['data' => $students]);
    }

    /**
     * Search students across all classes in the school
     */
    public function searchStudents(Request $request, $schoolId)
    {
        $user = Auth::user();
        $schoolId = (int) $schoolId;

        // Authorization: supervisors allowed; school admins must match active school and be admin for it
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== $schoolId || !$user->isSchoolAdmin($schoolId)) {
                abort(403);
            }
        }

        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $students = LoginKey::where('school_id', $schoolId)
            ->where('type', 'student')
            ->where(function($q) use ($query) {
                $q->where('first_name', 'like', '%' . $query . '%')
                  ->orWhere('last_name', 'like', '%' . $query . '%');
            })
            ->with('class')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(50)
            ->get()
            ->map(function ($lk) {
                return [
                    'id' => $lk->id,
                    'full_name' => $lk->full_name,
                    'class_name' => $lk->class?->name ?? '',
                ];
            });

        return response()->json(['data' => $students]);
    }

    /**
     * Get all students in the school
     */
    public function allStudents($schoolId)
    {
        $user = Auth::user();
        $schoolId = (int) $schoolId;

        // Authorization: supervisors allowed; school admins must match active school and be admin for it
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== $schoolId || !$user->isSchoolAdmin($schoolId)) {
                abort(403);
            }
        }

        $students = LoginKey::where('school_id', $schoolId)
            ->where('type', 'student')
            ->with('class')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($lk) {
                return [
                    'id' => $lk->id,
                    'full_name' => $lk->full_name,
                    'class_name' => $lk->class?->name ?? '',
                ];
            });

        return response()->json(['data' => $students]);
    }

    /**
     * Get active (public) timetable for a school
     */
    public function getActiveTimetable($schoolId)
    {
        $user = Auth::user();
        $schoolId = (int) $schoolId;

        // Authorization
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== $schoolId || !$user->isSchoolAdmin($schoolId)) {
                abort(403);
            }
        }

        $timetable = Timetable::where('school_id', $schoolId)
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->first();

        if (!$timetable) {
            return response()->json(['timetable_id' => null]);
        }

        return response()->json(['timetable_id' => $timetable->id]);
    }

    /**
     * Get student timetable grid data
     */
    public function getStudentTimetable($timetableId, $studentId)
    {
        $user = Auth::user();
        $timetable = Timetable::findOrFail($timetableId);

        // Authorization
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== $timetable->school_id || !$user->isSchoolAdmin($timetable->school_id)) {
                abort(403);
            }
        }

        // Fetch student - must belong to the school
        $studentModel = LoginKey::where('id', $studentId)
            ->where('school_id', $timetable->school_id)
            ->where('type', 'student')
            ->firstOrFail();

        $days = ['Mon' => 'Pirmadienis', 'Tue' => 'Antradienis', 'Wed' => 'Trečiadienis', 'Thu' => 'Ketvirtadienis', 'Fri' => 'Penktadienis'];
        $dayCaps = [
            'Mon' => $timetable->max_lessons_monday ?? 9,
            'Tue' => $timetable->max_lessons_tuesday ?? 9,
            'Wed' => $timetable->max_lessons_wednesday ?? 9,
            'Thu' => $timetable->max_lessons_thursday ?? 9,
            'Fri' => $timetable->max_lessons_friday ?? 9,
        ];

        // Initialize empty grid
        $grid = [];
        $maxRows = max($dayCaps);
        for ($i = 1; $i <= $maxRows; $i++) {
            $grid[$i] = [];
        }

        // Pull all slots for groups this student is in
        $slots = $timetable->slots()
            ->whereHas('group', function($q) use ($studentId) {
                $q->whereHas('students', function($sq) use ($studentId) {
                    $sq->where('login_key_id', $studentId);
                });
            })
            ->with(['group.subject', 'group.room', 'group.teacherLoginKey'])
            ->get();

        // Populate grid
        foreach ($slots as $slot) {
            if (isset($dayCaps[$slot->day])) {
                if (!isset($grid[$slot->slot])) {
                    $grid[$slot->slot] = [];
                }
                
                $grid[$slot->slot][$slot->day] = [
                    'group_name' => $slot->group?->name ?? '',
                    'subject' => $slot->group?->subject?->name ?? '',
                    'teacher' => $slot->group?->teacherLoginKey?->full_name ?? '',
                    'room' => $slot->group?->room?->name ?? '',
                    'room_id' => $slot->group?->room?->id ?? null,
                    'group_id' => $slot->group?->id ?? null,
                ];
            }
        }

        return response()->json([
            'student' => [
                'id' => $studentModel->id,
                'first_name' => $studentModel->first_name,
                'last_name' => $studentModel->last_name,
                'full_name' => $studentModel->full_name,
            ],
            'grid' => $grid,
            'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        ]);
    }

    /**
     * Get all groups for a timetable
     */
    public function timetableGroups($timetableId)
    {
        $user = Auth::user();
        $timetable = Timetable::findOrFail($timetableId);

        // Authorization
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== (int) $timetable->school_id || !$user->isSchoolAdmin($timetable->school_id)) {
                abort(403);
            }
        }

        $groups = $timetable->groups()
            ->with(['subject', 'teacherLoginKey'])
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'subject' => $group->subject?->name ?? '',
                    'teacher' => $group->teacherLoginKey?->full_name ?? '',
                ];
            });

        return response()->json(['data' => $groups]);
    }

    /**
     * Get groups scheduled at a specific day and slot across the school timetable
     */
    public function groupsBySlot($timetableId, $day, $slot)
    {
        $user = Auth::user();
        $timetable = Timetable::findOrFail($timetableId);

        // Authorization
        if (!$user->isSupervisor()) {
            $activeSchoolId = (int) session('active_school_id');
            if (!$activeSchoolId || $activeSchoolId !== (int) $timetable->school_id || !$user->isSchoolAdmin($timetable->school_id)) {
                abort(403);
            }
        }

        // Normalize inputs
        $day = strtoupper(substr($day, 0, 3)); // Mon/Tue/Wed/Thu/Fri
        $slot = (int) $slot;

        $slots = TimetableSlot::where('timetable_id', $timetable->id)
            ->where('day', $day)
            ->where('slot', $slot)
            ->with(['group.subject', 'group.teacherLoginKey'])
            ->get();

        // Unique groups
        $groups = $slots->pluck('group')->filter()->unique('id')->values()->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'subject' => $group->subject?->name ?? '',
                'teacher' => $group->teacherLoginKey?->full_name ?? '',
            ];
        });

        return response()->json(['data' => $groups]);
    }
}
