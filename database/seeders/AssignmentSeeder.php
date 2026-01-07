<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Schedule;
use Carbon\Carbon;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Kunin ang lahat ng schedules para malaman kung anong classes ang may assignments
        $schedules = Schedule::with(['subject', 'section', 'teacher'])->get();

        foreach ($schedules as $schedule) {
            // Gagawa tayo ng dalawang assignment bawat class/schedule
            
            // 1. Isang Active Assignment (May 7 days deadline)
            Assignment::create([
                'teacher_id'  => $schedule->teacher_id,
                'subject_id'  => $schedule->subject_id,
                'section_id'  => $schedule->section_id,
                'title'       => 'Activity 1: Introduction to ' . $schedule->subject->name,
                'description' => 'Please read the attached module and answer the essay questions at the end.',
                'deadline'    => Carbon::now()->addDays(7),
                'file_path'   => null, // Set to null muna for seeded data
            ]);

            // 2. Isang "Past Due" Assignment (Para ma-test ang Overdue status sa UI)
            Assignment::create([
                'teacher_id'  => $schedule->teacher_id,
                'subject_id'  => $schedule->subject_id,
                'section_id'  => $schedule->section_id,
                'title'       => 'Preliminary Quiz in ' . $schedule->subject->code,
                'description' => 'Submit your handwritten solutions as a scanned PDF file.',
                'deadline'    => Carbon::now()->subDays(2), // Deadline was 2 days ago
                'file_path'   => null,
            ]);
        }
    }
}