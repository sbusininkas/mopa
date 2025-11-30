<?php

namespace App\Imports;

use App\Models\LoginKey;
use App\Models\School;
use App\Models\SchoolClass;

class StudentsImport
{
    protected $school;
    protected $class;
    protected $schoolYear;

    public function __construct(School $school, SchoolClass $class, $schoolYear = null)
    {
        $this->school = $school;
        $this->class = $class;
        $this->schoolYear = $schoolYear;
    }

    public function import($file)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;

            // Expected format: Single column with "vardas pavarde"
            $fullName = trim($row[0] ?? '');
            
            if (empty($fullName)) continue;

            // Parse "vardas pavarde" into first_name and last_name
            $nameParts = preg_split('/\s+/', $fullName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            if (empty($firstName)) continue;

            LoginKey::create([
                'school_id' => $this->school->id,
                'class_id' => $this->class->id,
                'type' => 'student',
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => null,
                'school_year' => $this->schoolYear,
                'key' => LoginKey::generateKey(),
            ]);

            $count++;
        }

        fclose($handle);
        return $count;
    }
}
