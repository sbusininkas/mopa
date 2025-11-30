<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * Show the user management page (admin only).
     */
    public function index(): View
    {
        $user = auth()->user();
        
        if ($user->isSupervisor()) {
            // Supervisors see all users
            $users = User::orderBy('created_at', 'desc')->paginate(15);
            
            $stats = [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'students' => User::where('role', 'student')->count(),
            ];
        } elseif ($user->adminSchoolIds()->isNotEmpty()) {
            // School admins see only users from their schools
            $schoolIds = $user->adminSchoolIds();
            $users = User::whereHas('schools', function ($query) use ($schoolIds) {
                $query->whereIn('schools.id', $schoolIds);
            })->orderBy('created_at', 'desc')->paginate(15);
            
            $stats = [
                'total' => User::whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                })->count(),
                'admins' => User::where('role', 'admin')->whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                })->count(),
                'teachers' => User::where('role', 'teacher')->whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                })->count(),
                'students' => User::where('role', 'student')->whereHas('schools', function ($query) use ($schoolIds) {
                    $query->whereIn('schools.id', $schoolIds);
                })->count(),
            ];
        } else {
            abort(403, 'Neturite prieigos prie vartotojų valdymo.');
        }

        return view('admin.users.index', ['users' => $users, 'stats' => $stats]);
    }

    /**
     * Show user edit page.
     */
    public function edit(User $user): View
    {
        $currentUser = auth()->user();
        
        // Check authorization
        if (!$currentUser->isSupervisor()) {
            // School admins can only edit users from their schools
            $schoolIds = $currentUser->adminSchoolIds()->toArray();
            $hasAccess = $user->schools()->whereIn('schools.id', $schoolIds)->exists();
            
            if (!$hasAccess) {
                abort(403, 'Neturite prieigos redaguoti šį vartotoją.');
            }
            
            // Verify user is from ACTIVE school
            $this->authorizeSchoolUsers($schoolIds);
        }

        $user->load('schools');
        $schools = \App\Models\School::orderBy('name')->get();

        return view('admin.users.edit', ['user' => $user, 'schools' => $schools]);
    }

    /**
     * Update a user's information and role.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();
        
        // Check authorization
        if (!$currentUser->isSupervisor()) {
            // School admins can only update users from their schools
            $schoolIds = $currentUser->adminSchoolIds()->toArray();
            $hasAccess = $user->schools()->whereIn('schools.id', $schoolIds)->exists();
            
            if (!$hasAccess) {
                abort(403, 'Neturite prieigos redaguoti šį vartotoją.');
            }
            
            // Verify user is from ACTIVE school
            $this->authorizeSchoolUsers($schoolIds);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,teacher,student,supervisor',
            'schools' => 'array',
            'schools.*' => 'exists:schools,id',
            'school_admins' => 'array',
            'school_admins.*' => 'exists:schools,id',
        ];

        $validated = $request->validate($rules);

        // Tik supervisor gali priskirti globalią admin ar supervisor rolę
        if (!$currentUser->isSupervisor() && in_array($validated['role'], ['admin','supervisor'])) {
            // Mokyklos admin gali priskirti tik teacher/student rolę
            $validated['role'] = $user->role; // neleidžiam keisti į admin/supervisor
        }

        // Update basic fields
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        // Sync schools pivot (assign/unassign) and mark school-specific admins
        // School admins can only manage schools they are admins of
        $sync = [];
        $requestedSchools = $validated['schools'] ?? [];
        $requestedAdmins = $validated['school_admins'] ?? [];
        
        if (!$currentUser->isSupervisor()) {
            // School admins can only assign to their own schools
            $allowedSchoolIds = $currentUser->adminSchoolIds()->toArray();
            $requestedSchools = array_intersect($requestedSchools, $allowedSchoolIds);
            $requestedAdmins = array_intersect($requestedAdmins, $allowedSchoolIds);
        }

        foreach ($requestedSchools as $sid) {
            $sync[$sid] = ['is_admin' => in_array($sid, $requestedAdmins) ? 1 : 0];
        }
        $user->schools()->sync($sync);

        return redirect()->route('users.index')->with('success', "User {$user->name} updated successfully");
    }

    /**
     * Update a user's role (quick action).
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        // Only supervisors can change global roles
        if (!Auth::user()->isSupervisor()) {
            abort(403, 'Only supervisors can change global roles.');
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,teacher,student,supervisor',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $validated['role']]);

        return redirect()->route('users.index')->with('success', "User {$user->name} role changed from {$oldRole} to {$validated['role']}");
    }

    /**
     * Delete a user (admin only).
     */
    public function destroy(User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        
        // Check authorization
        if (!$currentUser->isSupervisor()) {
            // School admins can only delete users from their schools
            $schoolIds = $currentUser->adminSchoolIds()->toArray();
            $hasAccess = $user->schools()->whereIn('schools.id', $schoolIds)->exists();
            
            if (!$hasAccess) {
                abort(403, 'Neturite prieigos ištrinti šį vartotoją.');
            }
            
            // Verify user is from ACTIVE school
            $this->authorizeSchoolUsers($schoolIds);
        }

        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $user->delete();

        return redirect()->route('users.index')->with('success', "User {$userName} ({$userEmail}) has been deleted.");
    }

    /**
     * Search users.
     */
    public function search(Request $request): View
    {
        $currentUser = auth()->user();
        $query = $request->input('q');

        if ($currentUser->isSupervisor()) {
            // Supervisors search all users
            $users = User::where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

            $stats = [
                'total' => User::count(),
                'admins' => User::where('role', 'admin')->count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'students' => User::where('role', 'student')->count(),
            ];
        } elseif ($currentUser->adminSchoolIds()->isNotEmpty()) {
            // School admins search only their school users
            $schoolIds = $currentUser->adminSchoolIds();
            $users = User::whereHas('schools', function ($q) use ($schoolIds) {
                $q->whereIn('schools.id', $schoolIds);
            })
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

            $stats = [
                'total' => User::whereHas('schools', function ($q) use ($schoolIds) {
                    $q->whereIn('schools.id', $schoolIds);
                })->count(),
                'admins' => User::where('role', 'admin')->whereHas('schools', function ($q) use ($schoolIds) {
                    $q->whereIn('schools.id', $schoolIds);
                })->count(),
                'teachers' => User::where('role', 'teacher')->whereHas('schools', function ($q) use ($schoolIds) {
                    $q->whereIn('schools.id', $schoolIds);
                })->count(),
                'students' => User::where('role', 'student')->whereHas('schools', function ($q) use ($schoolIds) {
                    $q->whereIn('schools.id', $schoolIds);
                })->count(),
            ];
        } else {
            abort(403, 'Neturite prieigos prie vartotojų paieškos.');
        }

        return view('admin.users.index', ['users' => $users, 'stats' => $stats, 'searchQuery' => $query]);
    }

    /**
     * Check if user can manage a specific school's users.
     * School admins can only manage users from their ACTIVE school.
     */
    private function authorizeSchoolUsers($schoolIds)
    {
        $currentUser = auth()->user();
        
        // Supervisors can always access
        if ($currentUser->isSupervisor()) {
            return;
        }
        
        // School admins can only work with their ACTIVE school
        $activeSchoolId = session('active_school_id');
        if (!in_array($activeSchoolId, $schoolIds)) {
            abort(403, 'Galite valdyti tik aktyvios mokyklos vartotojus. Pirmiausia pasirinkite ją iš meniu.');
        }
    }
}
