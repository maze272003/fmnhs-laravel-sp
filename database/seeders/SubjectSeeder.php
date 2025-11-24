<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'MATH-11',
                'name' => 'General Mathematics',
                'description' => 'Core Subject for SHS',
            ],
            [
                'code' => 'SCI-11',
                'name' => 'Earth and Life Science',
                'description' => 'Core Subject for SHS',
            ],
            [
                'code' => 'ENG-11',
                'name' => 'Oral Communication',
                'description' => 'Core Subject for SHS',
            ],
            [
                'code' => 'PROG-1',
                'name' => 'Introduction to Programming',
                'description' => 'Specialized Subject for ICT',
            ],
            [
                'code' => 'FIL-11',
                'name' => 'Komunikasyon at Pananaliksik',
                'description' => 'Core Subject for SHS',
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}