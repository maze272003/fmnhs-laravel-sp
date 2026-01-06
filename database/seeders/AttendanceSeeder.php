<?php
// database/seeders/AttendanceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Schedule;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();
        $statuses = ['present', 'present', 'present', 'late', 'absent']; // Mas mataas chance ng present

        // Mag-seed para sa nakaraang 5 days (excluding weekends)
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::now()->subDays($i);
            
            if ($date->isWeekend()) continue;

            foreach ($students as $student) {
                $schedules = Schedule::where('section_id', $student->section_id)->get();

                foreach ($schedules as $sched) {
                    Attendance::create([
                        'student_id' => $student->id,
                        'subject_id' => $sched->subject_id,
                        'teacher_id' => $sched->teacher_id,
                        'section_id' => $student->section_id,
                        'date'       => $date->format('Y-m-d'),
                        'status'     => collect($statuses)->random(),
                    ]);
                }
            }
        }
    }
}