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
            ['name' => 'Mabini', 'grade_level' => 12, 'strand' => 'HUMSS'],
            ['name' => 'Luna', 'grade_level' => 12, 'strand' => 'TVL'],
            
            ['name' => 'Del Pilar', 'grade_level' => 11, 'strand' => 'STEM'],
            ['name' => 'Jacinto', 'grade_level' => 11, 'strand' => 'ABM'],
            ['name' => 'Silang', 'grade_level' => 11, 'strand' => 'HUMSS'],
            ['name' => 'Lakandula', 'grade_level' => 11, 'strand' => 'TVL'],
            
            ['name' => 'Agoncillo', 'grade_level' => 10, 'strand' => null],
            ['name' => 'Baron', 'grade_level' => 10, 'strand' => null],
            
            ['name' => 'Aguinaldo', 'grade_level' => 9, 'strand' => null],
            ['name' => 'Burgos', 'grade_level' => 9, 'strand' => null],
            
            ['name' => 'Castro', 'grade_level' => 8, 'strand' => null],
            ['name' => 'Diaz', 'grade_level' => 8, 'strand' => null],
            
            ['name' => 'Escudero', 'grade_level' => 7, 'strand' => null],
            ['name' => 'Flores', 'grade_level' => 7, 'strand' => null],
        ];

        $teachers = Teacher::all();

        foreach ($sections as $index => $data) {
            Section::firstOrCreate(
                ['name' => $data['name']],
                [
                    'name' => $data['name'],
                    'grade_level' => $data['grade_level'],
                    'strand' => $data['strand'],
                    'teacher_id' => isset($teachers[$index]) ? $teachers[$index]->id : null,
                ]
            );
        }
    }
}
