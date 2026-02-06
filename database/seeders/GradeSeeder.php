<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Schedule;
use App\Helpers\SchoolYearHelper;

class GradeSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();

        foreach ($students as $student) {
            // Kunin ang mga subjects na naka-assign sa section ng student
            $assignedSchedules = Schedule::where('section_id', $student->section_id)->get();

            foreach ($assignedSchedules as $sched) {
                // Mag-generate ng grades para sa 4 na quarters
                for ($q = 1; $q <= 4; $q++) {
                    Grade::create([
                        'student_id'  => $student->id,
                        'subject_id'  => $sched->subject_id,
                        'teacher_id'  => $sched->teacher_id,
                        'quarter'     => $q,
                        'grade_value' => rand(75, 98),
                        'school_year' => SchoolYearHelper::current(),
                    ]);
                }
            }
        }
    }
}