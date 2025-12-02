<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Helpers\SchoolYearHelper;

class ActiveSchoolController extends Controller
{
    /**
     * Get the active school from request (injected by middleware)
     */
    private function getSchool(Request $request)
    {
        return $request->get('activeSchool');
    }

    /**
     * Show school dashboard
     */
    public function dashboard(Request $request): View
    {
        $school = $this->getSchool($request);
        $school->load(['classes', 'timetables']);
        
        // Calculate statistics - use count() on relationships to execute queries
        $stats = [
            'classes_count' => $school->classes()->count(),
            'students_count' => $school->loginKeys()->where('type', 'student')->where('used', true)->count(),
            'teachers_count' => $school->loginKeys()->where('type', 'teacher')->where('used', true)->count(),
            'timetables_count' => $school->timetables()->count(),
            'active_timetables_count' => $school->timetables()->where('is_public', true)->count(),
        ];

        return view('admin.schools.dashboard', compact('school', 'stats'));
    }

    /**
     * Show classes page with active school
     */
    public function classes(Request $request)
    {
        $school = $this->getSchool($request);
        $classes = $school->classes()->orderBy('name')->get();
        return view('admin.classes.index', compact('school', 'classes'));
    }

    /**
     * Show timetables page with active school
     */
    public function timetables(Request $request)
    {
        $school = $this->getSchool($request);
        $timetables = $school->timetables()->orderByDesc('is_public')->orderBy('name')->get();
        return view('admin.timetables.index', compact('school', 'timetables'));
    }

    /**
     * Show login keys page with active school
     */
    public function loginKeys(Request $request)
    {
        $school = $this->getSchool($request);
        $loginKeys = $school->loginKeys()->with(['user', 'class'])->orderBy('type')->orderBy('last_name')->orderBy('first_name')->get();
        return view('admin.login_keys.index', compact('school', 'loginKeys'));
    }

    /**
     * Show subjects page with active school
     */
    public function subjects(Request $request)
    {
        $school = $this->getSchool($request);
        $subjects = $school->subjects()->orderBy('name')->get();
        return view('admin.subjects.index', compact('school', 'subjects'));
    }

    /**
     * Show rooms page with active school
     */
    public function rooms(Request $request)
    {
        $school = $this->getSchool($request);
        $rooms = $school->rooms()->orderBy('name')->get();
        return view('admin.rooms.index', compact('school', 'rooms'));
    }

    /**
     * Show import page with active school
     */
    public function import(Request $request)
    {
        $school = $this->getSchool($request);
        return view('admin.login_keys.import', compact('school'));
    }

    /**
     * Show settings page with active school
     */
    public function settings(Request $request)
    {
        $school = $this->getSchool($request);
        $users = \App\Models\User::orderBy('name')->get();
        return view('admin.schools.edit', compact('school', 'users'));
    }

    /**
     * Show contacts page with active school
     */
    public function contacts(Request $request)
    {
        $school = $this->getSchool($request);
        return view('admin.schools.edit-contacts', compact('school'));
    }
}
