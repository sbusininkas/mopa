<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\LoginKey;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class SchoolActivationController extends Controller
{
    /**
     * Show the school activation page
     */
    public function index(): View
    {
        return view('activation.index');
    }

    /**
     * Activate school using admin key (assigns user as school admin)
     */
    public function activateWithAdminKey(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admin_key' => 'required|string',
        ]);

        // Find school by admin_key
        $school = School::where('admin_key', $validated['admin_key'])->first();

        if (!$school) {
            return back()->withErrors(['admin_key' => 'Neteisingas administratoriaus raktas.']);
        }

        $user = Auth::user();

        // Check if user is already attached to this school
        if (!$user->schools()->where('school_id', $school->id)->exists()) {
            // Attach user as admin
            $user->schools()->attach($school->id, ['is_admin' => true]);
        } else {
            // Update to ensure is_admin is true
            $user->schools()->updateExistingPivot($school->id, ['is_admin' => true]);
        }

        // Set as active school
        session(['active_school_id' => $school->id]);

        return redirect()->route('dashboard')->with('success', 'Sėkmingai prisijungėte prie mokyklos kaip administratorius!');
    }

    /**
     * Activate school using user login token
     */
    public function activateWithUserToken(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_token' => 'required|string',
        ]);

        // Find login key
        $loginKey = LoginKey::where('key', $validated['user_token'])->first();

        if (!$loginKey) {
            return back()->withErrors(['user_token' => 'Neteisingas prisijungimo raktas.']);
        }

        $user = Auth::user();

        // Attach login key to user
        if ($loginKey->user_id && $loginKey->user_id !== $user->id) {
            return back()->withErrors(['user_token' => 'Šis raktas jau naudojamas kito vartotojo.']);
        }

        // Update login key
        $loginKey->update([
            'user_id' => $user->id,
            'used' => true,
        ]);

        // Update user role based on login key type
        if ($loginKey->type === 'teacher') {
            $user->update(['role' => 'teacher']);
        } elseif ($loginKey->type === 'student') {
            $user->update(['role' => 'student']);
        }

        // Attach to school if not already
        $school = $loginKey->school;
        if (!$user->schools()->where('school_id', $school->id)->exists()) {
            $user->schools()->attach($school->id, ['is_admin' => false]);
        }

        // Set as active school
        session(['active_school_id' => $school->id]);

        return redirect()->route('dashboard')->with('success', 'Sėkmingai prisijungėte prie mokyklos!');
    }
}
