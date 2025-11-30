<?php

namespace App\Http\Controllers;

use App\Models\LoginKey;
use App\Models\SchoolClass;
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
}
