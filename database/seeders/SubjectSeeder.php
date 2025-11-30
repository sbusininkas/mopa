<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSubjects = [
            'Lietuvių kalba',
            'Matematika',
            'Anglų kalba',
            'Rusų kalba',
            'Biologija',
            'Fizika',
            'Chemija',
            'Istorija',
            'Geografija',
            'Informacinės technologijos',
            'Dailė',
            'Muzika',
            'Technologijos',
            'Etika',
            'Tikybos',
            'Kūno kultūra',
        ];

        foreach ($defaultSubjects as $name) {
            Subject::firstOrCreate([
                'name' => $name,
                'school_id' => null,
                'is_default' => true,
            ]);
        }
    }
}
