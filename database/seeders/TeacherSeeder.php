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
        // 1. CREATE SPECIFIC TEACHER (Juan Dela Cruz)
        $teacher = Teacher::updateOrCreate(
            ['email' => 'teacher@gmail.com'],
            [
                'employee_id' => 'T-2025-001',
                'first_name'  => 'Juan',
                'last_name'   => 'Dela Cruz',
                'password'    => Hash::make('password'),
                'department'  => 'Science',
            ]
        );

        // 2. ASSIGN ADVISORY FROM DB (Dynamic)
        // Kukuha tayo ng isang random section na wala pang teacher_id (advisor)
        $availableSection = Section::whereNull('teacher_id')->inRandomOrder()->first();

        if ($availableSection) {
            $availableSection->update([
                'teacher_id' => $teacher->id
            ]);
        }

        // 3. RANDOM TEACHERS (Generate using Factory)
        $randomTeachers = Teacher::factory(20)->create();

        // (Optional) Kung gusto mo pati yung random teachers ay magkaroon ng advisory
        foreach ($randomTeachers as $rTeacher) {
            $section = Section::whereNull('teacher_id')->inRandomOrder()->first();
            if ($section) {
                $section->update(['teacher_id' => $rTeacher->id]);
            }
        }
    }
}