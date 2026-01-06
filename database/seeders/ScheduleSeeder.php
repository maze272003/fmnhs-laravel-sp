<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Section;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $section = Section::where('name', 'Rizal')->first();
        $teacher = Teacher::first();
        $subjects = Subject::take(5)->get();
        $times = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '13:00:00'];

        foreach ($subjects as $index => $subject) {
            if (!isset($times[$index])) break;

            Schedule::create([
                'section_id' => $section->id, // Use ID instead of name
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'day'        => 'Monday - Friday',
                'start_time' => $times[$index],
                'end_time'   => date('H:i:s', strtotime($times[$index]) + 3600),
                'room'       => 'Room ' . (100 + $index)
            ]);
        }
    }
}