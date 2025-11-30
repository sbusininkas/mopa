<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolAdminOrSupervisor
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $school = $request->route('school');
        $schoolId = is_object($school) ? $school->id : $school;
        if ($user && ($user->isSupervisor() || $user->isSchoolAdmin($schoolId))) {
            return $next($request);
        }
        abort(403);
    }
}
