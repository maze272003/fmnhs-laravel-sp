<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\Teacher;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['name' => 'Rizal', 'grade_level' => 12, 'strand' => 'STEM'],
            ['name' => 'Bonifacio', 'grade_level' => 12, 'strand' => 'ABM'],
            ['name' => 'Mabini', 'grade_level' => 11, 'strand' => 'HUMSS'],
            ['name' => 'Luna', 'grade_level' => 10, 'strand' => null],
            ['name' => 'Aguinaldo', 'grade_level' => 7, 'strand' => null],
        ];

        // Get some teachers to be advisors
        $teachers = Teacher::all();

        foreach ($sections as $index => $data) {
            Section::create([
                'name' => $data['name'],
                'grade_level' => $data['grade_level'],
                'strand' => $data['strand'],
                
                // Eto ang tamang paraan para i-check kung may teacher sa index na yan
                'teacher_id' => isset($teachers[$index]) ? $teachers[$index]->id : null,
            ]);
        }
    }
}