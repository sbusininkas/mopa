<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Helpers\SchoolYearHelper;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        // Only supervisors or school-admins can view schools
        $user = auth()->user();
        if ($user->isSupervisor()) {
            // Supervisors see all schools
            $schools = School::withCount('users')->paginate(20);
        } elseif ($user->adminSchoolIds()->isNotEmpty()) {
            // School admins see only their schools and redirect to dashboard if only one
            $schools = School::whereIn('id', $user->adminSchoolIds())->withCount('users')->paginate(20);
        } else {
            abort(403, 'Only supervisors or school administrators can view schools.');
        }

        return view('admin.schools.index', compact('schools'));
    }

    /**
     * Show school dashboard (admin view).
     */
    public function dashboard(School $school)
    {
        $user = auth()->user();
        
        // First check if user is supervisor or school admin
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'You do not have permission to access this school.');
        }
        
        // For non-supervisors, enforce active school
        if (!$user->isSupervisor()) {
            // Check if already has active school set and it's different
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            
            // Set as active school
            session(['active_school_id' => $school->id]);
        }
        
        $school->load(['classes', 'loginKeys.user', 'loginKeys.class']);
        
        $schoolYears = SchoolYearHelper::getAvailableYears();

        return view('admin.schools.dashboard', compact('school', 'schoolYears'));
    }

    public function create()
    {
        // Only supervisors can create new schools
        if (!auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can create schools.');
        }

        return view('admin.schools.edit', ['school' => new School(), 'users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {

        // Only supervisors can create schools
        if (!auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can create schools.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:512',
            'phone' => 'nullable|string|max:50',
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'admins' => 'array',
            'admins.*' => 'exists:users,id',
        ]);

        $school = School::create($data);

        // Only `priziuretojas` should be allowed to attach users and set school admins.
        if (auth()->user()->isSupervisor()) {
            $attach = [];
            if (!empty($data['users'])) {
                foreach ($data['users'] as $uid) {
                    $attach[$uid] = ['is_admin' => in_array($uid, $data['admins'] ?? []) ? 1 : 0];
                }
            }

            if (!empty($attach)) {
                $school->users()->sync($attach);
            }
        }

        return redirect()->route('schools.index')->with('success', 'School created.');
    }

    public function edit(School $school)
    {
        // Only supervisors can edit schools
        if (!auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can edit schools.');
        }

        $users = User::orderBy('name')->get();
        $school->load('users');

        return view('admin.schools.edit', compact('school', 'users'));
    }

    public function update(Request $request, School $school)
    {
        // Only supervisors can update schools
        if (!auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can update schools.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:512',
            'phone' => 'nullable|string|max:50',
            'users' => 'array',
            'users.*' => 'exists:users,id',
            'admins' => 'array',
            'admins.*' => 'exists:users,id',
        ]);

        $school->update($data);

        // Only `priziuretojas` should be allowed to attach users and set school admins.
        if (auth()->user()->isSupervisor()) {
            $attach = [];
            if (!empty($data['users'])) {
                foreach ($data['users'] as $uid) {
                    $attach[$uid] = ['is_admin' => in_array($uid, $data['admins'] ?? []) ? 1 : 0];
                }
            }

            $school->users()->sync($attach);
        }

        return redirect()->route('schools.index')->with('success', 'School updated.');
    }

    public function destroy(School $school)
    {
        // Only supervisors can delete schools
        if (!auth()->user()->isSupervisor()) {
            abort(403, 'Only supervisors can delete schools.');
        }

        $school->delete();
        return redirect()->route('schools.index')->with('success', 'School deleted.');
    }

    /**
     * Show edit contacts form (phone, email) - accessible by school admins.
     */
    public function editContacts(School $school)
    {
        $user = auth()->user();
        
        // First check if user is supervisor or school admin
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'You do not have permission to access this school.');
        }
        
        // For non-supervisors, enforce active school
        if (!$user->isSupervisor()) {
            // Check if already has active school set and it's different
            $activeSchoolId = session('active_school_id');
            if ($activeSchoolId && $activeSchoolId !== $school->id) {
                abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
            }
            
            // Set as active school
            session(['active_school_id' => $school->id]);
        }

        return view('admin.schools.edit-contacts', compact('school'));
    }

    /**
     * Update school contacts (phone, email) - accessible by school admins.
     */
    public function updateContacts(Request $request, School $school)
    {
        $this->authorize($school);

        $data = $request->validate([
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $school->update($data);

        return redirect()->route('schools.dashboard', $school)
            ->with('success', 'Mokyklos kontaktai atnaujinti sėkmingai.');
    }

    /**
     * Check if user can manage this school (before session is set - for entry points).
     */
    private function authorizeBeforeSession(School $school)
    {
        $user = auth()->user();
        
        // Supervisors can always access
        if ($user->isSupervisor()) {
            return;
        }
        
        // School admins can only access their schools
        if (!$user->isSchoolAdmin($school->id)) {
            abort(403, 'You do not have permission to access this school.');
        }
    }

    /**
     * Check if user can manage this school (after session is set - enforces active school).
     */
    private function authorize(School $school)
    {
        $user = auth()->user();
        
        // Supervisors can always access
        if ($user->isSupervisor()) {
            return;
        }
        
        // School admins can only access their schools
        if (!$user->isSchoolAdmin($school->id)) {
            abort(403, 'You do not have permission to access this school.');
        }
        
        // School admins can only work with their ACTIVE school
        $activeSchoolId = session('active_school_id');
        if (!$activeSchoolId || $activeSchoolId !== $school->id) {
            abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
        }
    }
}
