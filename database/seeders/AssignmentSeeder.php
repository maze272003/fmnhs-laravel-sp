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
        $schedules = Schedule::with(['subject', 'section', 'teacher'])->get();
        $schoolYears = ['2018-2019', '2019-2020', '2020-2021', '2021-2022', '2022-2023', '2023-2024', '2024-2025', '2025-2026'];

        foreach ($schoolYears as $syIndex => $schoolYear) {
            $startYear = (int)explode('-', $schoolYear)[0];

            foreach ($schedules as $schedule) {
                $numAssignments = rand(2, 4);

                for ($i = 1; $i <= $numAssignments; $i++) {
                    $month = rand(6, 12);
                    $isPastSchoolYear = $syIndex < count($schoolYears) - 1;

                    if ($isPastSchoolYear) {
                        $assignmentDate = Carbon::create($startYear, $month, rand(1, 28), rand(8, 16), 0);
                        $deadline = $assignmentDate->copy()->addDays(rand(3, 14));
                    } else {
                        $month = rand(6, min(12, (int)date('n')));
                        $assignmentDate = Carbon::create($startYear, $month, rand(1, 28), rand(8, 16), 0);
                        $deadline = $assignmentDate->copy()->addDays(rand(3, 14));
                    }

                    Assignment::firstOrCreate([
                        'teacher_id' => $schedule->teacher_id,
                        'subject_id' => $schedule->subject_id,
                        'section_id' => $schedule->section_id,
                        'title' => $this->generateTitle($schedule, $i, $schoolYear),
                        'deadline' => $deadline,
                    ], [
                        'description' => $this->generateDescription($schedule->subject->name),
                        'file_path' => null,
                    ]);
                }
            }
        }
    }

    private function generateTitle($schedule, $index, $schoolYear)
    {
        $titles = [
            'Activity', 'Quiz', 'Project', 'Assignment', 'Homework', 'Exam',
        ];
        $titleType = $titles[array_rand($titles)];
        return $titleType . ' ' . $index . ' - ' . $schedule->subject->name . ' (' . $schoolYear . ')';
    }

    private function generateDescription($subjectName)
    {
        $descriptions = [
            'Please read the attached module and answer the essay questions at the end.',
            'Submit your handwritten solutions as a scanned PDF file.',
            'Complete the exercises on pages 10-15 of your textbook.',
            'Research about the topic and prepare a 5-minute presentation.',
            'Answer the multiple choice questions provided in the worksheet.',
            'Create a creative output related to the topic discussed in class.',
        ];
        return $descriptions[array_rand($descriptions)] . ' Subject: ' . $subjectName;
    }
}
