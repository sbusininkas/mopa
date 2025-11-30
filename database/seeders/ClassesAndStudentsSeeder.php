<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LoginKey;
use App\Models\Subject;
use App\Models\Room;
use App\Models\Timetable;
use App\Models\TimetableGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClassesAndStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Kauno Gimnazija school
        $school = School::where('name', 'Kauno Gimnazija')->first();
        
        if (!$school) {
            $this->command->error('Kauno Gimnazija not found. Please create the school first.');
            return;
        }

        $this->command->info("Creating classes and students for school: {$school->name}");

        // Lithuanian first names and last names
        $maleFirstNames = [
            'Jonas', 'Petras', 'Antanas', 'Mindaugas', 'Vytautas', 'Darius', 
            'Mantas', 'Tomas', 'Lukas', 'Matas', 'Dovydas', 'Rokas', 'Andrius',
            'Justas', 'Paulius', 'Erikas', 'Robertas', 'Dominykas', 'Edvinas', 'Ignas'
        ];
        
        $femaleFirstNames = [
            'Ieva', 'Greta', 'Austė', 'Gabija', 'Emilija', 'Julija', 'Urtė',
            'Kamile', 'Liepa', 'Miglė', 'Rugilė', 'Simona', 'Viktorija',
            'Laura', 'Rasa', 'Agnė', 'Ugnė', 'Eglė', 'Vaida', 'Indrė'
        ];
        
        $lastNames = [
            'Kazlauskas', 'Petrauskas', 'Jankauskas', 'Stankevicius', 'Vasiliauskas',
            'Žukauskas', 'Butkus', 'Paulauskas', 'Urbonas', 'Kavaliauskas',
            'Ramanauskas', 'Baranauskas', 'Jankevicius', 'Mazeika', 'Gudaitis',
            'Navickas', 'Stankus', 'Laurinavicius', 'Grigas', 'Adomaitis',
            'Bagdonas', 'Bielskis', 'Cernius', 'Daujotas', 'Einikis'
        ];

        // Teacher names
        $teacherData = [
            ['first' => 'Rasa', 'last' => 'Petraitienė'],
            ['first' => 'Virginija', 'last' => 'Kazlauskienė'],
            ['first' => 'Dalia', 'last' => 'Jankevičienė'],
            ['first' => 'Petras', 'last' => 'Urbonas'],
            ['first' => 'Andrius', 'last' => 'Stankevičius'],
            ['first' => 'Gintaras', 'last' => 'Vasiliauskas'],
            ['first' => 'Jolanta', 'last' => 'Butkienė'],
            ['first' => 'Irena', 'last' => 'Paulauskienė'],
        ];

        // Classes to create
        $classNames = ['5A', '5B', '6A', '6B', '7A', '7B', '8A'];
        $createdClasses = [];
        $teachers = [];

        // Create teachers first
        $this->command->info("\nCreating teachers...");
        foreach ($teacherData as $data) {
            $teacher = LoginKey::create([
                'school_id' => $school->id,
                'type' => 'teacher',
                'first_name' => $data['first'],
                'last_name' => $data['last'],
            ]);
            $teachers[] = $teacher;
            $this->command->info("  Created teacher: {$data['first']} {$data['last']}");
        }

        // Create subjects
        $this->command->info("\nCreating subjects...");
        $subjectNames = [
            'Lietuvių kalba',
            'Matematika',
            'Anglų kalba',
            'Istorija',
            'Geografija',
            'Biologija',
            'Fizika',
            'Chemija',
            'Informatika',
            'Kūno kultūra',
            'Dailė',
            'Muzika'
        ];
        
        $subjects = [];
        foreach ($subjectNames as $name) {
            $subject = Subject::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['school_id' => $school->id, 'name' => $name]
            );
            $subjects[] = $subject;
            $this->command->info("  Created/found subject: {$name}");
        }

        // Create rooms
        $this->command->info("\nCreating rooms...");
        $roomData = [
            ['number' => '101', 'name' => 'Kabinetas 101'],
            ['number' => '102', 'name' => 'Kabinetas 102'], 
            ['number' => '201', 'name' => 'Kabinetas 201'],
            ['number' => '202', 'name' => 'Kabinetas 202'],
            ['number' => '301', 'name' => 'Informatikos klasė'],
            ['number' => '302', 'name' => 'Chemijos laboratorija'],
            ['number' => '303', 'name' => 'Fizikos laboratorija'],
            ['number' => 'S1', 'name' => 'Sporto salė'],
            ['number' => '401', 'name' => 'Muzikos klasė'],
            ['number' => '402', 'name' => 'Dailės klasė']
        ];
        
        $rooms = [];
        foreach ($roomData as $data) {
            $room = Room::firstOrCreate(
                ['school_id' => $school->id, 'number' => $data['number']],
                [
                    'school_id' => $school->id, 
                    'number' => $data['number'],
                    'name' => $data['name']
                ]
            );
            $rooms[] = $room;
            $this->command->info("  Created/found room: {$data['name']}");
        }

        // Create classes and students
        $this->command->info("\nCreating classes and students...");
        foreach ($classNames as $className) {
            $class = SchoolClass::create([
                'school_id' => $school->id,
                'name' => $className,
            ]);
            
            $createdClasses[] = $class;
            $this->command->info("Created class: {$className}");

            // Create 15-20 students for each class
            $studentCount = rand(15, 20);
            for ($i = 1; $i <= $studentCount; $i++) {
                $isMale = rand(0, 1) === 1;
                $firstName = $isMale 
                    ? $maleFirstNames[array_rand($maleFirstNames)]
                    : $femaleFirstNames[array_rand($femaleFirstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                
                LoginKey::create([
                    'school_id' => $school->id,
                    'class_id' => $class->id,
                    'type' => 'student',
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
            }
            
            $this->command->info("  Added {$studentCount} students to class {$className}");
        }

        // Get first timetable
        $timetable = Timetable::where('school_id', $school->id)->first();
        
        if ($timetable) {
            $this->command->info("\nCreating timetable groups...");
            
            // Clear existing groups
            $timetable->groups()->delete();
            
            // Subject-Teacher mapping (assign teachers to subjects)
            $subjectTeacherMap = [
                'Lietuvių kalba' => 0,      // Rasa Petraitienė
                'Matematika' => 1,           // Virginija Kazlauskienė
                'Anglų kalba' => 2,          // Dalia Jankevičienė
                'Istorija' => 3,             // Petras Urbonas
                'Geografija' => 3,           // Petras Urbonas
                'Biologija' => 4,            // Andrius Stankevičius
                'Fizika' => 5,               // Gintaras Vasiliauskas
                'Chemija' => 5,              // Gintaras Vasiliauskas
                'Informatika' => 6,          // Jolanta Butkienė
                'Kūno kultūra' => 7,         // Irena Paulauskienė
                'Dailė' => 0,                // Rasa Petraitienė
                'Muzika' => 2,               // Dalia Jankevičienė
            ];

            // Create groups for each class
            foreach ($createdClasses as $class) {
                $students = LoginKey::where('class_id', $class->id)
                    ->where('type', 'student')
                    ->get();

                // Each class gets: Lithuanian (5), Math (4), English (3), History (2), Geography (2), 
                // Biology (2), Physics (2), Chemistry (1), IT (2), PE (3), Art (1), Music (1)
                $classSubjects = [
                    'Lietuvių kalba' => 5,
                    'Matematika' => 4,
                    'Anglų kalba' => 3,
                    'Istorija' => 2,
                    'Geografija' => 2,
                    'Biologija' => 2,
                    'Fizika' => 2,
                    'Chemija' => 1,
                    'Informatika' => 2,
                    'Kūno kultūra' => 3,
                    'Dailė' => 1,
                    'Muzika' => 1,
                ];

                foreach ($classSubjects as $subjectName => $lessonsPerWeek) {
                    $subject = collect($subjects)->firstWhere('name', $subjectName);
                    $teacherIndex = $subjectTeacherMap[$subjectName];
                    $teacher = $teachers[$teacherIndex];
                    
                    // Assign appropriate room
                    $roomIndex = 0;
                    if ($subjectName === 'Informatika') $roomIndex = 4;
                    elseif ($subjectName === 'Chemija') $roomIndex = 5;
                    elseif ($subjectName === 'Fizika') $roomIndex = 6;
                    elseif ($subjectName === 'Kūno kultūra') $roomIndex = 7;
                    elseif ($subjectName === 'Muzika') $roomIndex = 8;
                    elseif ($subjectName === 'Dailė') $roomIndex = 9;
                    else $roomIndex = rand(0, 3);
                    
                    $room = $rooms[$roomIndex];

                    $group = TimetableGroup::create([
                        'timetable_id' => $timetable->id,
                        'name' => $class->name . '_' . strtolower(str_replace(' ', '_', $subjectName)),
                        'subject_id' => $subject->id,
                        'teacher_login_key_id' => $teacher->id,
                        'room_id' => $room->id,
                        'lessons_per_week' => $lessonsPerWeek,
                        'is_priority' => in_array($subjectName, ['Lietuvių kalba', 'Matematika']),
                    ]);

                    // Attach all students from this class
                    $group->students()->attach($students->pluck('id'));
                    
                    $this->command->info("  Created group: {$group->name} ({$lessonsPerWeek} lessons/week)");
                }
            }

            $totalGroups = TimetableGroup::where('timetable_id', $timetable->id)->count();
            $this->command->info("\n✓ Created {$totalGroups} timetable groups");
        }

        $totalClasses = count($createdClasses);
        $totalStudents = LoginKey::where('school_id', $school->id)
            ->where('type', 'student')
            ->count();
        
        $this->command->info("\n✓ Successfully created:");
        $this->command->info("  - {$totalClasses} classes");
        $this->command->info("  - {$totalStudents} students");
        $this->command->info("  - " . count($teachers) . " teachers");
        $this->command->info("  - " . count($subjects) . " subjects");
        $this->command->info("  - " . count($rooms) . " rooms");
    }
}
