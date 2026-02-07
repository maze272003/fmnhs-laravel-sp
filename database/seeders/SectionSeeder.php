<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\SchoolYearConfig;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Load Teachers
        $teachers = Teacher::all();

        // 2. Load or Create School Years
        // We ensure these years exist in the database so we can get their IDs
        $sy2025 = SchoolYearConfig::firstOrCreate(
            ['school_year' => '2025-2026'], 
            ['is_active' => true, 'status' => 'active']
        );
        
        $sy2024 = SchoolYearConfig::firstOrCreate(
            ['school_year' => '2024-2025'], 
            ['is_active' => false, 'status' => 'closed']
        );

        // 3. Define Sections with Specific School Years
        $sections = [
            // GRADE 12 (Current Active Year)
            ['name' => 'Rizal',     'grade_level' => 12, 'strand' => 'STEM',  'sy_id' => $sy2025->id],
            ['name' => 'Bonifacio', 'grade_level' => 12, 'strand' => 'ABM',   'sy_id' => $sy2025->id],
            ['name' => 'Mabini',    'grade_level' => 12, 'strand' => 'HUMSS', 'sy_id' => $sy2025->id],
            ['name' => 'Luna',      'grade_level' => 12, 'strand' => 'TVL',   'sy_id' => $sy2025->id],
            
            // GRADE 11 (Current Active Year)
            ['name' => 'Del Pilar', 'grade_level' => 11, 'strand' => 'STEM',  'sy_id' => $sy2025->id],
            ['name' => 'Jacinto',   'grade_level' => 11, 'strand' => 'ABM',   'sy_id' => $sy2025->id],
            ['name' => 'Silang',    'grade_level' => 11, 'strand' => 'HUMSS', 'sy_id' => $sy2025->id],
            ['name' => 'Lakandula', 'grade_level' => 11, 'strand' => 'TVL',   'sy_id' => $sy2025->id],
            
            // GRADE 10 (Current Active Year)
            ['name' => 'Agoncillo', 'grade_level' => 10, 'strand' => null,   'sy_id' => $sy2025->id],
            ['name' => 'Baron',     'grade_level' => 10, 'strand' => null,   'sy_id' => $sy2025->id],

            // OLD SECTIONS (Previous Year Example)
            ['name' => 'Old-Rizal', 'grade_level' => 12, 'strand' => 'STEM',  'sy_id' => $sy2024->id],
            ['name' => 'Old-Bonifa', 'grade_level' => 12, 'strand' => 'ABM',  'sy_id' => $sy2024->id],
        ];

        foreach ($sections as $index => $data) {
            // Assign a teacher if available (cycling through list)
            $teacherId = isset($teachers[$index]) ? $teachers[$index]->id : $teachers->first()->id ?? null;

            Section::updateOrCreate(
                [
                    'name' => $data['name'], 
                    'school_year_id' => $data['sy_id'] // Unique key combination if needed
                ],
                [
                    'grade_level'    => $data['grade_level'],
                    'strand'         => $data['strand'],
                    'teacher_id'     => $teacherId,
                    'school_year_id' => $data['sy_id'], // Explicitly set the SY ID
                ]
            );
        }
    }
}