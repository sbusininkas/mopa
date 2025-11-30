<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\User;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            ['name' => 'Vilniaus Mokykla', 'address' => 'Vilnius, Gedimino pr. 1', 'phone' => '+37061234567'],
            ['name' => 'Kauno Gimnazija', 'address' => 'Kaunas, Laisvės al. 10', 'phone' => '+37069876543'],
            ['name' => 'Klaipėdos Progimnazija', 'address' => 'Klaipėda, Tiltų g. 5', 'phone' => '+37061230000'],
        ];

        foreach ($schools as $s) {
            $school = School::create($s);

            // Attach an existing admin user (search by email used earlier)
            $admin = User::where('email', 'heromu.megazas@gmail.com')->first();
            if ($admin) {
                $school->users()->attach($admin->id, ['is_admin' => true]);
            } else {
                // fallback: attach the first user
                $first = User::first();
                if ($first) {
                    $school->users()->attach($first->id, ['is_admin' => true]);
                }
            }
        }
    }
}
