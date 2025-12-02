<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\School;

class LoadActiveSchool
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get active school from session
        $activeSchoolId = session('active_school_id');
        
        if (!$activeSchoolId) {
            // If no active school, redirect to dashboard with message
            return redirect()->route('dashboard')
                ->with('error', 'Prašome pasirinkti mokyklą.');
        }

        // Load the school
        $school = School::find($activeSchoolId);
        
        if (!$school) {
            // School not found, clear session and redirect
            session()->forget('active_school_id');
            return redirect()->route('dashboard')
                ->with('error', 'Mokykla nerasta.');
        }

        // Check permissions
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'Neturite prieigos prie šios mokyklos.');
        }

        // Share school with all views
        view()->share('currentSchool', $school);
        
        // Add school to request
        $request->merge(['activeSchool' => $school]);

        return $next($request);
    }
}
