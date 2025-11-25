<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Get your specific test section
        $section = 'Rizal'; // Make sure this matches your student account's section
        $teacher = Teacher::first();
        $subjects = Subject::take(5)->get();

        // Create a schedule for each subject
        $times = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '13:00:00'];

        foreach ($subjects as $index => $subject) {
            if (!isset($times[$index])) break;

            Schedule::create([
                'section'    => $section,
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day'        => 'Monday - Friday',
                'start_time' => $times[$index],
                'end_time'   => date('H:i:s', strtotime($times[$index]) + 3600), // +1 hour
                'room'       => 'Room ' . (100 + $index)
            ]);
        }
    }
}