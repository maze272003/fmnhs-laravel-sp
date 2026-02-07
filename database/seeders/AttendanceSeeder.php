<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Grade;
use App\Models\SchoolYearConfig; // Import this
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $students = Student::all();
        $statuses = ['present', 'present', 'present', 'late', 'absent'];

        foreach ($students as $student) {
            // FIX: Query 'school_year_id' instead of 'school_year'
            $schoolYearIds = Grade::where('student_id', $student->id)
                ->distinct()
                ->pluck('school_year_id');

            foreach ($schoolYearIds as $syId) {
                // Get the actual year string (e.g., "2025-2026") from the config table
                $config = SchoolYearConfig::find($syId);
                
                if (!$config) continue; // Safety check

                $schoolYearString = $config->school_year; // "2025-2026"
                $startYear = (int)explode('-', $schoolYearString)[0];

                // Generate attendance for 1st Semester (June - Dec)
                for ($month = 6; $month <= 12; $month++) {
                    $this->seedAttendanceForMonth($student, $startYear, $month, $syId, $statuses);
                }
                
                // Generate attendance for 2nd Semester (Jan - May)
                for ($month = 1; $month <= 5; $month++) {
                    $this->seedAttendanceForMonth($student, $startYear + 1, $month, $syId, $statuses);
                }
            }
        }
    }

    private function seedAttendanceForMonth($student, $year, $month, $schoolYearId, $statuses)
    {
        // Safety check for valid dates
        if (!checkdate($month, 1, $year)) return;

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);

            if ($date->isWeekend()) continue;
            if ($date->gt(Carbon::now())) continue;

            // FIX: Filter grades by school_year_id
            $studentSubjects = Grade::where('student_id', $student->id)
                ->where('school_year_id', $schoolYearId)
                ->distinct()
                ->pluck('subject_id')
                ->toArray();

            if (empty($studentSubjects)) continue;

            // Pick random subjects for attendance
            $subjectIds = array_slice($studentSubjects, 0, rand(3, min(5, count($studentSubjects))));

            foreach ($subjectIds as $subjectId) {
                // 40% chance to skip recording attendance (simulate real life gaps)
                if (rand(1, 100) > 40) continue;

                Attendance::firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,
                        'date'       => $date->format('Y-m-d'),
                    ],
                    [
                        'teacher_id' => 1, // Default teacher ID (ensure ID 1 exists)
                        'section_id' => $student->section_id,
                        'status'     => collect($statuses)->random(),
                    ]
                );
            }
        }
    }
}