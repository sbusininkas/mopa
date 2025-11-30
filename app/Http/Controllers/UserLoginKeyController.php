<?php

namespace App\Http\Controllers;

use App\Models\LoginKey;
use App\Models\School;
use Illuminate\Http\Request;

class UserLoginKeyController extends Controller
{
    /**
     * Show form to activate login key.
     */
    public function showActivateForm()
    {
        $user = auth()->user();
        $schools = $user->schools()->get();
        $pendingKeys = LoginKey::where('user_id', null)->get();

        return view('profile.activate-key', compact('user', 'schools', 'pendingKeys'));
    }

    /**
     * Activate a login key for current user.
     */
    public function activate(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'key' => 'required|string|size:12',
        ]);

        $loginKey = LoginKey::where('key', $request->key)->first();

        if (!$loginKey) {
            return back()->withError('Raktas nerastas. Patikrinkite ar teisingai jį įvedėte.');
        }

        if ($loginKey->used && $loginKey->user_id !== $user->id) {
            return back()->withError('Šis raktas jau panaudotas kitą vartotojui.');
        }

        // Check if user already has this key
        if ($loginKey->user_id === $user->id) {
            return back()->withInfo('Jūs jau naudojate šį raktą.');
        }

        // Activate the key
        $loginKey->update([
            'user_id' => $user->id,
            'used' => true,
        ]);

        // Add user to school if not already there
        $school = $loginKey->school;
        if (!$user->schools()->where('school_id', $school->id)->exists()) {
            $user->schools()->attach($school->id, ['is_admin' => 0]);
        }

        // Update user role based on key type
        if ($loginKey->type === 'teacher' && !in_array($user->role, ['supervisor', 'admin'])) {
            $user->update(['role' => 'teacher']);
        } elseif ($loginKey->type === 'student' && $user->role === 'student') {
            // Stay as student
        }

        return back()->with('success', sprintf(
            'Sėkmingai suaktyvinta! Jūs dabar priklausote %s mokyklai. Rolė: %s',
            $school->name,
            $loginKey->type === 'teacher' ? 'Mokytojas' : 'Mokinys'
        ));
    }

    /**
     * View user's schools and roles.
     */
    public function mySchools()
    {
        $user = auth()->user();
        $schools = $user->schools()->with('classes')->get();
        $loginKeys = $user->loginKeys()->with('school', 'class')->get();

        return view('profile.my-schools', compact('user', 'schools', 'loginKeys'));
    }

    /**
     * Deactivate a login key (remove from school).
     */
    public function deactivate(LoginKey $loginKey)
    {
        $user = auth()->user();

        if ($loginKey->user_id !== $user->id) {
            abort(403);
        }

        // Remove user from school
        $school = $loginKey->school;
        
        // Check if user has other keys from this school
        $otherKeys = $user->loginKeys()
            ->where('school_id', $school->id)
            ->where('id', '!=', $loginKey->id)
            ->exists();

        if (!$otherKeys) {
            // Remove from school
            $user->schools()->detach($school->id);
        }

        // Clear the key
        $loginKey->update([
            'user_id' => null,
            'used' => false,
        ]);

        return back()->with('success', 'Raktas deaktyvintas.');
    }
}
