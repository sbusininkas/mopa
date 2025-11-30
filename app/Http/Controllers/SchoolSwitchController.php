<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;

class SchoolSwitchController extends Controller
{
    public function switch(Request $request, School $school)
    {
        $user = $request->user();

        // supervisors may switch to any school; system admins also; school admins only to their schools
        if (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->isSchoolAdmin($school->id)) {
            abort(403, 'You do not have access to this school.');
        }

        session(['active_school_id' => $school->id]);

        return redirect()->back()->with('success', 'Pakeista aktyvi mokykla: ' . $school->name);
    }
}
