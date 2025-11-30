<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\School;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(School $school)
    {
        $rooms = $school->rooms()->orderBy('number')->get();
        return view('admin.rooms.index', compact('school', 'rooms'));
    }

    public function store(Request $request, School $school)
    {
        $validated = $request->validate([
            'number' => 'required|string|max:50',
            'name' => 'required|string|max:100',
        ]);

        // Check if room number already exists for this school
        $exists = $school->rooms()->where('number', $validated['number'])->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kabinetas su šiuo numeriu jau egzistuoja'
            ], 422);
        }

        $room = $school->rooms()->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'room' => $room
            ]);
        }

        return redirect()->route('schools.rooms.index', $school)
            ->with('success', 'Kabinetas sėkmingai sukurtas');
    }

    public function update(Request $request, School $school, Room $room)
    {
        // Check if room belongs to this school
        if ($room->school_id !== $school->id) {
            abort(403);
        }

        $validated = $request->validate([
            'number' => 'required|string|max:50',
            'name' => 'required|string|max:100',
        ]);

        // Check if room number already exists for this school (excluding current room)
        $exists = $school->rooms()
            ->where('number', $validated['number'])
            ->where('id', '!=', $room->id)
            ->exists();
            
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Kabinetas su šiuo numeriu jau egzistuoja'
            ], 422);
        }

        $room->update($validated);

        return response()->json([
            'success' => true,
            'room' => $room
        ]);
    }

    public function destroy(School $school, Room $room)
    {
        // Check if room belongs to this school
        if ($room->school_id !== $school->id) {
            abort(403);
        }

        $room->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('schools.rooms.index', $school)
            ->with('success', 'Kabinetas sėkmingai pašalintas');
    }

    public function bulkDelete(Request $request, School $school)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:rooms,id'
        ]);

        $deleted = 0;
        foreach ($validated['ids'] as $id) {
            $room = Room::find($id);
            if ($room && $room->school_id === $school->id) {
                $room->delete();
                $deleted++;
            }
        }

        return response()->json([
            'success' => true,
            'deleted' => $deleted
        ]);
    }

    public function import(School $school)
    {
        return view('admin.rooms.import', compact('school'));
    }

    public function importExcel(Request $request, School $school)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('file');
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getPathname());
            $spreadsheet = $reader->load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $imported = 0;
            $skipped = 0;
            $errors = [];

            foreach ($worksheet->getRowIterator(2) as $row) { // Start from row 2 (skip header)
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Skip empty rows
                if (empty($cells[0]) && empty($cells[1])) {
                    continue;
                }

                $number = trim($cells[0] ?? '');
                $name = trim($cells[1] ?? '');

                if (empty($number) || empty($name)) {
                    $errors[] = "Eilutė {$row->getRowIndex()}: trūksta numerio arba pavadinimo";
                    $skipped++;
                    continue;
                }

                // Check if room already exists
                $exists = $school->rooms()->where('number', $number)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                $school->rooms()->create([
                    'number' => $number,
                    'name' => $name,
                ]);
                $imported++;
            }

            $message = "Importuota: {$imported}";
            if ($skipped > 0) {
                $message .= ", praleista: {$skipped}";
            }

            return redirect()->route('schools.rooms.index', $school)
                ->with('success', $message)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Klaida importuojant: ' . $e->getMessage());
        }
    }
}

