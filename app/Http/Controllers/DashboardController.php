<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard page.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        
        // Check if user has an active school
        $activeSchoolId = session('active_school_id');
        
        // If no active school and user has no schools, redirect to activation
        if (!$activeSchoolId && $user->schools()->count() === 0 && !$user->isSupervisor()) {
            return redirect()->route('activation.index');
        }
        
        $totalUsers = \App\Models\User::count();
        
        return view('dashboard.index', [
            'user' => $user,
            'totalUsers' => $totalUsers,
        ]);
    }
}
