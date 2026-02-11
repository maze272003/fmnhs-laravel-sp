<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;
use App\Models\Section;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = Teacher::updateOrCreate(
            ['email' => 'rasosjoanna@gmail.com'],
            [
                'employee_id' => 'T-2025-001',
                'first_name'  => 'Juan',
                'last_name'   => 'Dela Cruz',
                'password'    => Hash::make('password'),
                'department'  => 'Science',
            ]
        );

        $rizal = Section::where('name', 'Rizal')->first();
        if ($rizal) {
            $rizal->update(['teacher_id' => $teacher->id]);
        }

        $randomTeachers = Teacher::factory(20)->create();

        foreach ($randomTeachers as $rTeacher) {
            $section = Section::whereNull('teacher_id')
                ->where('name', '!=', 'Rizal')
                ->inRandomOrder()
                ->first();

            if ($section) {
                $section->update(['teacher_id' => $rTeacher->id]);
            }
        }
    }
}
