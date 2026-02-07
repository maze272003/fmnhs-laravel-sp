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
        $sections = Section::all();
        $teachers = Teacher::all();
        $subjects = Subject::all();
        $times = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '13:00:00', '14:00:00', '15:00:00'];
        $days = ['Monday - Friday', 'Monday - Wednesday - Friday', 'Tuesday - Thursday'];

        foreach ($sections as $section) {
            $gradeLevel = $section->grade_level;
            $strand = $section->strand;

            $sectionSubjects = $subjects->filter(function ($subject) use ($gradeLevel, $strand) {
                $code = $subject->code;

                if ($gradeLevel >= 11) {
                    if (strpos($code, 'SHS-') !== 0) return false;

                    if ($strand === 'STEM' && strpos($code, 'SHS-GBIO') !== false) return true;
                    if ($strand === 'ABM' && strpos($code, 'SHS-FABM') !== false) return true;
                    if ($strand === 'HUMSS' && strpos($code, 'SHS-DIASS') !== false) return true;
                    if ($strand === 'TVL' && strpos($code, 'SHS-TVL') !== false) return true;
                    if (strpos($code, 'SHS-CORE') !== false || strpos($code, 'SHS-APPL') !== false) return true;

                    return in_array($strand, ['STEM', 'ABM', 'HUMSS', 'TVL']);
                } else {
                    $jhsLevel = strpos($code, '-' . $gradeLevel);
                    if ($jhsLevel !== false) return true;
                    return strpos($code, 'MAPEH') !== false || strpos($code, 'TLE') !== false;
                }
            })->take(rand(5, 8));

            foreach ($sectionSubjects as $index => $subject) {
                if (!isset($times[$index % count($times)])) continue;

                $teacher = $teachers->random();

                Schedule::firstOrCreate([
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'day' => $days[$index % count($days)],
                    'start_time' => $times[$index % count($times)],
                ], [
                    'teacher_id' => $teacher->id,
                    'end_time' => date('H:i:s', strtotime($times[$index % count($times)]) + 3600),
                    'room' => 'Room ' . (100 + $index + ($section->grade_level * 10)),
                ]);
            }
        }
    }
}
