<?php
namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // Rodo visus dalykus mokyklai
    public function index(School $school)
    {
        $subjects = $school->subjects()->orderBy('name')->get();
        return view('admin.subjects.index', compact('school', 'subjects'));
    }

    // Sukuria naują dalyką
    public function store(Request $request, School $school)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);
        $subject = $school->subjects()->create([
            'name' => $request->name,
            'is_default' => false,
        ]);
        return redirect()->route('schools.subjects.index', $school)->with('success', 'Dalykas sukurtas');
    }

    // Ištrina dalyką
    public function destroy(School $school, Subject $subject)
    {
        $deleted = false;
        if ($subject->school_id === $school->id) {
            $subject->delete();
            $deleted = true;
        }
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => $deleted]);
        }
        return redirect()->route('schools.subjects.index', $school)->with('success', 'Dalykas ištrintas');
    }

    // Atnaujina dalyką
    public function update(Request $request, School $school, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);
        if ($subject->school_id === $school->id) {
            $subject->update(['name' => $request->name]);
        }
        return response()->json(['success' => true]);
    }

    // Įtraukia numatytus dalykus, jei jų nėra
    public function addDefaults(School $school)
    {
        $defaultSubjects = Subject::where('is_default', true)->whereNull('school_id')->get();
        $existingNames = $school->subjects()->pluck('name')->toArray();
        $added = 0;
        foreach ($defaultSubjects as $default) {
            if (!in_array($default->name, $existingNames)) {
                $school->subjects()->create([
                    'name' => $default->name,
                    'is_default' => false,
                ]);
                $added++;
            }
        }
        return redirect()->route('schools.subjects.index', $school)
            ->with('success', $added ? "Įtraukta $added numatytų dalykų." : "Visi numatyti dalykai jau yra.");
    }

    // Pašalina kelis dalykus pagal ID
    public function bulkDelete(Request $request, School $school)
    {
        $ids = $request->input('ids', []);
        $deleted = 0;
        foreach ($ids as $id) {
            $subject = $school->subjects()->where('id', $id)->first();
            if ($subject) {
                $subject->delete();
                $deleted++;
            }
        }
        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}
