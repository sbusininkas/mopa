<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LoginKey;
use App\Helpers\SchoolYearHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Imports\StudentsImport;
use App\Imports\TeachersImport;

class LoginKeyController extends Controller
{
    /**
     * Show login keys management page.
     */
    public function index(School $school)
    {
        $this->setActiveSchoolIfNeeded($school);
        $this->authorize($school);

        $loginKeys = $school->loginKeys()
            ->with(['user', 'class.teacher', 'leadingClasses'])
            ->paginate(20);

        return view('admin.login-keys.index', compact('school', 'loginKeys'));
    }

    /**
     * Show import form.
     */
    public function import(School $school)
    {
        $this->setActiveSchoolIfNeeded($school);
        $this->authorize($school);

        $classes = $school->classes()->get();
        $schoolYears = SchoolYearHelper::getAvailableYears();

        return view('admin.login-keys.import', compact('school', 'classes', 'schoolYears'));
    }

    /**
     * Store imported students.
     */
    public function storeStudentImport(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'class_id' => 'required|exists:classes,id',
            'school_year' => 'required|in:' . implode(',', SchoolYearHelper::getAvailableYears()),
        ]);

        $class = SchoolClass::findOrFail($request->class_id);

        // Validate class belongs to school
        if ($class->school_id !== $school->id) {
            abort(403);
        }

        $import = new StudentsImport($school, $class, $request->school_year);
        $import->import($request->file('file'));

        return redirect()->route('schools.login-keys.index', $school)
            ->with('success', 'Mokiniai sėkmingai importuoti.');
    }

    /**
     * Store imported teachers.
     */
    public function storeTeacherImport(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'school_year' => 'required|in:' . implode(',', SchoolYearHelper::getAvailableYears()),
        ]);

        $import = new TeachersImport($school, $request->school_year);
        $import->import($request->file('file'));

        return redirect()->route('schools.login-keys.index', $school)
            ->with('success', 'Mokytojai sėkmingai importuoti.');
    }

    /**
     * Generate new login keys.
     */
    public function generate(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'count' => 'required|integer|min:1|max:1000',
            'type' => 'required|in:student,teacher',
            'class_id' => 'required_if:type,student|exists:classes,id',
        ]);

        $classId = $request->type === 'student' ? $request->class_id : null;

        for ($i = 0; $i < $request->count; $i++) {
            LoginKey::create([
                'school_id' => $school->id,
                'type' => $request->type,
                'class_id' => $classId,
                'key' => LoginKey::generateKey(),
            ]);
        }

        return redirect()->route('schools.login-keys.index', $school)
            ->with('success', sprintf('Sugeneruota %d prisijungimo raktų.', $request->count));
    }

    /**
     * Export login keys as PDF.
     */
    public function exportPdf(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'type' => 'nullable|in:student,teacher',
            'class_id' => 'nullable|exists:classes,id',
            'teacher_id' => 'nullable|exists:login_keys,id',
            'school_year' => 'nullable|string',
        ]);

        $query = $school->loginKeys()->with(['class.teacher', 'user', 'leadingClasses']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by teacher (when type is teacher)
        if ($request->filled('teacher_id')) {
            $query->where('id', $request->teacher_id);
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $classId = $request->class_id;
            
            // Jei filtruojame tik mokytojus, ieškoti tik vadovaujančių šiai klasei
            if ($request->filled('type') && $request->type === 'teacher') {
                $query->whereHas('leadingClasses', function($q) use ($classId) {
                    $q->where('id', $classId);
                });
            } 
            // Jei filtruojame tik mokinius, ieškoti mokinių šioje klasėje
            elseif ($request->filled('type') && $request->type === 'student') {
                $query->where('class_id', $classId);
            }
            // Jei nėra tipo filtro, rodyti abu
            else {
                $query->where(function($q) use ($classId) {
                    $q->where('class_id', $classId)
                      ->orWhereHas('leadingClasses', function($q2) use ($classId) {
                          $q2->where('id', $classId);
                      });
                });
            }
        }

        // Filter by school year
        if ($request->filled('school_year')) {
            $schoolYear = $request->school_year;
            
            $query->where(function($q) use ($schoolYear) {
                // Mokiniai - filtruoti pagal jų prisijungimo rakto mokslo metus
                $q->where(function($q1) use ($schoolYear) {
                    $q1->where('type', 'student')
                       ->where('school_year', $schoolYear);
                })
                // ARBA mokytojai - filtruoti pagal klasių, kurioms jie vadovauja, mokslo metus
                ->orWhere(function($q2) use ($schoolYear) {
                    $q2->where('type', 'teacher')
                       ->whereHas('leadingClasses', function($q3) use ($schoolYear) {
                           $q3->where('school_year', $schoolYear);
                       });
                });
            });
        }

        $loginKeys = $query->orderBy('type')->orderBy('last_name')->get();

        return view('admin.login-keys.pdf', compact('school', 'loginKeys'));
    }

    /**
     * Regenerate key for a specific entry.
     */
    public function regenerate(School $school, LoginKey $loginKey)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        if ($loginKey->school_id !== $school->id) {
            abort(403);
        }

        if ($loginKey->used) {
            abort(403, 'Jūs negalite regeneruoti jau naudoto rakto.');
        }

        $newKey = LoginKey::generateKey();
        $loginKey->update([
            'key' => $newKey,
        ]);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Raktas regeneruotas sėkmingai.',
                'key' => $newKey,
            ]);
        }

        return back()->with('success', 'Raktas regeneruotas sėkmingai.');
    }

    /**
     * Delete login key.
     */
    public function destroy(School $school, LoginKey $loginKey)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        if ($loginKey->school_id !== $school->id) {
            abort(403);
        }

        if ($loginKey->used) {
            abort(403, 'Jūs negalite ištrinti jau naudoto rakto.');
        }

        $loginKey->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Raktas ištrintas sėkmingai.',
            ]);
        }

        return back()->with('success', 'Raktas ištrintas sėkmingai.');
    }

    /**
     * Bulk regenerate keys.
     */
    public function bulkRegenerate(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:login_keys,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $loginKey = LoginKey::find($id);
            
            if ($loginKey && $loginKey->school_id === $school->id && !$loginKey->used) {
                $loginKey->update([
                    'key' => LoginKey::generateKey(),
                ]);
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sėkmingai regeneruota {$count} raktų.",
        ]);
    }

    /**
     * Bulk delete keys.
     */
    public function bulkDelete(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:login_keys,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $loginKey = LoginKey::find($id);
            
            if ($loginKey && $loginKey->school_id === $school->id && !$loginKey->used) {
                $loginKey->delete();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sėkmingai ištrinta {$count} raktų.",
        ]);
    }

    /**
     * Store a single teacher entry.
     */
    public function storeTeacher(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'school_year' => 'nullable|in:' . implode(',', SchoolYearHelper::getAvailableYears()),
        ]);

        $teacher = LoginKey::create([
            'school_id' => $school->id,
            'type' => 'teacher',
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'school_year' => $request->school_year,
            'key' => LoginKey::generateKey(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mokytojas pridėtas sėkmingai.',
                'key' => $teacher->key,
                'teacher' => $teacher,
            ]);
        }

        return back()->with('success', 'Mokytojas pridėtas sėkmingai.');
    }

    /**
     * Store a single student entry.
     */
    public function storeStudent(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'class_id' => 'required|exists:classes,id',
            'email' => 'nullable|email|max:255',
            'school_year' => 'nullable|in:' . implode(',', SchoolYearHelper::getAvailableYears()),
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        if ($class->school_id !== $school->id) {
            abort(403);
        }

        $student = LoginKey::create([
            'school_id' => $school->id,
            'class_id' => $class->id,
            'type' => 'student',
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'school_year' => $request->school_year,
            'key' => LoginKey::generateKey(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Mokinys pridėtas sėkmingai.',
                'key' => $student->key,
                'student' => $student,
            ]);
        }

        return back()->with('success', 'Mokinys pridėtas sėkmingai.');
    }

    public function updateStudent(Request $request, School $school, LoginKey $loginKey)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        // Verify the login key belongs to this school
        if ($loginKey->school_id !== $school->id || $loginKey->type !== 'student') {
            abort(403);
        }

        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);

        $loginKey->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
        ]);

        return back()->with('success', 'Mokinio duomenys atnaujinti sėkmingai.');
    }

    /**
     * Search login keys.
     */
    public function search(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $query = $request->q;

        $loginKeys = $school->loginKeys()
            ->where(function ($q) use ($query) {
                $q->where('key', 'like', "%$query%")
                    ->orWhere('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhere('email', 'like', "%$query%");
            })
            ->with(['user', 'class'])
            ->paginate(20);

        return view('admin.login-keys.index', compact('school', 'loginKeys'));
    }

    /**
     * Helper for entry points - sets active school if not set.
     */
    private function setActiveSchoolIfNeeded(School $school)
    {
        $user = auth()->user();
        
        // Supervisors can always access
        if ($user->isSupervisor()) {
            return;
        }
        
        // School admins can only access their schools
        if (!$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        
        // Check if already has active school set and it's different
        $activeSchoolId = session('active_school_id');
        if ($activeSchoolId && $activeSchoolId !== $school->id) {
            abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
        }
        
        // Set as active school if not set
        if (!$activeSchoolId) {
            session(['active_school_id' => $school->id]);
        }
    }

    /**
     * Helper to enforce active school for entry points.
     */
    private function enforceActiveSchool(School $school)
    {
        $user = auth()->user();
        
        // Supervisors can always access
        if ($user->isSupervisor()) {
            return;
        }
        
        // School admins can only access their schools
        if (!$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        
        // School admins MUST have an active school set
        $activeSchoolId = session('active_school_id');
        if (!$activeSchoolId) {
            abort(403, 'Prašome pasirinkti mokyklą iš meniu.');
        }
        
        // School admins can only access their ACTIVE school
        if ($activeSchoolId !== $school->id) {
            abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
        }
    }

    /**
     * Check if user can manage this school's login keys (before session is set).
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
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
    }

    /**
     * Check if user can manage this school's login keys (after session is set).
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
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
        }
        
        // School admins can only work with their ACTIVE school
        $activeSchoolId = session('active_school_id');
        if (!$activeSchoolId || $activeSchoolId !== $school->id) {
            abort(403, 'Galite redaguoti tik aktyvią mokyklą. Pirmiausia pasirinkite ją iš meniu.');
        }
    }
}

