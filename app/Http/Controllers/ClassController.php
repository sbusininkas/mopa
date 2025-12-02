<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Show all classes for a school.
     */
    public function index(School $school)
    {
        $user = auth()->user();
        
        // First check if user is supervisor or school admin
        if (!$user->isSupervisor() && !$user->isSchoolAdmin($school->id)) {
            abort(403, 'Jūs neturite prieigos prie šios mokyklos.');
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

        $classes = $school->classes()->with('teacher')->paginate(20);
        $teachers = $school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->orderBy('first_name')->get();

        return view('admin.classes.index', compact('school', 'classes', 'teachers'));
    }

    /**
     * Show a specific class with students.
     */
    public function show(School $school, SchoolClass $class)
    {
        $this->setActiveSchoolIfNeeded($school);
        $this->authorize($school);

        $students = $class->loginKeys()->where('type', 'student')->orderBy('last_name')->orderBy('first_name')->get();
        $teachers = $school->loginKeys()->where('type', 'teacher')->orderBy('last_name')->get();

        return view('admin.classes.show', compact('school', 'class', 'students', 'teachers'));
    }

    /**
     * Show create class form.
     */
    public function create(School $school)
    {
        $this->setActiveSchoolIfNeeded($school);
        $this->authorize($school);

        $class = new SchoolClass();

        return view('admin.classes.create', compact('school', 'class'));
    }

    /**
     * Store a new class.
     */
    public function store(Request $request, School $school)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'teacher_id' => 'nullable|integer|exists:login_keys,id',
            'school_year' => 'required|string|max:9',
        ]);

        // Ensure selected teacher belongs to this school and is a teacher
        if (!empty($data['teacher_id'])) {
            $isValidTeacher = $school->loginKeys()
                ->where('type', 'teacher')
                ->where('id', $data['teacher_id'])
                ->exists();
            if (!$isValidTeacher) {
                return back()->withErrors(['teacher_id' => 'Pasirinktas klasės vadovas nepriklauso šiai mokyklai.'])->withInput();
            }
        }

        // Validate school year format against helper list
        if (!\App\Helpers\SchoolYearHelper::isValidYear($data['school_year'])) {
            return back()->withErrors(['school_year' => 'Neteisingas mokslo metų formatas.'])->withInput();
        }

        $data['school_id'] = $school->id;

        SchoolClass::create($data);

        return redirect()->route('schools.classes.index', $school)
            ->with('success', 'Klasė sukurta sėkmingai.');
    }

    /**
     * Show edit class form.
     */
    public function edit(School $school, SchoolClass $class)
    {
        $this->setActiveSchoolIfNeeded($school);
        $this->authorize($school);

        return view('admin.classes.edit', compact('school', 'class'));
    }

    /**
     * Update a class.
     */
    public function update(Request $request, School $school, SchoolClass $class)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'teacher_id' => 'nullable|integer|exists:login_keys,id',
            'school_year' => 'required|string|max:9',
        ]);

        // Ensure selected teacher belongs to this school and is a teacher
        if (!empty($data['teacher_id'])) {
            $isValidTeacher = $school->loginKeys()
                ->where('type', 'teacher')
                ->where('id', $data['teacher_id'])
                ->exists();
            if (!$isValidTeacher) {
                return back()->withErrors(['teacher_id' => 'Pasirinktas klasės vadovas nepriklauso šiai mokyklai.'])->withInput();
            }
        }

        // Validate school year format against helper list
        if (!\App\Helpers\SchoolYearHelper::isValidYear($data['school_year'])) {
            return back()->withErrors(['school_year' => 'Neteisingas mokslo metų formatas.'])->withInput();
        }

        $class->update($data);

        return redirect()->route('schools.classes.index', $school)
            ->with('success', 'Klasė atnaujinta sėkmingai.');
    }

    /**
     * Delete a class.
     */
    public function destroy(School $school, SchoolClass $class)
    {
        $this->enforceActiveSchool($school);
        $this->authorize($school);

        $class->delete();

        return redirect()->route('schools.classes.index', $school)
            ->with('success', 'Klasė ištrinta sėkmingai.');
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
     * Check if user can manage this school's classes (before session is set).
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
     * Check if user can manage this school's classes (after session is set).
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
