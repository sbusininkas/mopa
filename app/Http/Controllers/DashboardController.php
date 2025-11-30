<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $totalUsers = \App\Models\User::count();
        
        return view('dashboard.index', [
            'user' => $user,
            'totalUsers' => $totalUsers,
        ]);
    }
}
