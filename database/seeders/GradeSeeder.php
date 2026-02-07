<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\SchoolYearConfig;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::with('section')->get();
        $allSubjects = Subject::all();
        $teachers = Teacher::pluck('id')->toArray();
        $schoolYears = SchoolYearConfig::pluck('id', 'school_year'); 

        if (empty($teachers)) {
            $this->command->warn("No teachers found.");
            return;
        }

        // 1. Pre-categorize Subjects to prevent mixing
        $jhsSubjects = $allSubjects->filter(fn($s) => preg_match('/[7-9]|10/', $s->name) || preg_match('/-(7|8|9|10)$/', $s->code));
        
        // SHS Subjects are those strictly NOT containing 7, 8, 9, or 10 in their name
        $shsSubjects = $allSubjects->diff($jhsSubjects);

        $this->command->info("Seeding History: Found " . $jhsSubjects->count() . " JHS and " . $shsSubjects->count() . " SHS subjects.");

        foreach ($students as $student) {
            if (!$student->section || !$student->school_year_id) continue;

            $currentGrade = $student->section->grade_level; 
            $currentSyString = $schoolYears->search($student->school_year_id); 

            if (!$currentSyString) continue;

            $currentYearStart = (int) explode('-', $currentSyString)[0];

            // Loop Backwards from Current Grade down to 7
            for ($level = $currentGrade; $level >= 7; $level--) {

                // Calculate Past Year
                $yearsAgo = $currentGrade - $level;
                $pastYearStart = $currentYearStart - $yearsAgo;
                $pastSyString = $pastYearStart . '-' . ($pastYearStart + 1);

                if (!isset($schoolYears[$pastSyString])) continue; 
                
                $targetSyId = $schoolYears[$pastSyString];

                // 2. Select Subjects STRICTLY for this Level
                $subjectsForLevel = collect();

                if ($level <= 10) {
                    // JUNIOR HIGH: Only pick subjects with the specific number (e.g. "7")
                    $subjectsForLevel = $jhsSubjects->filter(function ($subject) use ($level) {
                        return str_contains($subject->name, (string)$level) || str_contains($subject->code, "-$level");
                    });
                } else {
                    // SENIOR HIGH (11/12): Pick from the SHS pool
                    // To avoid duplicates between G11 and G12, we can split the SHS pool or just pick random unique ones
                    $subjectsForLevel = $shsSubjects->random(min(8, $shsSubjects->count()));
                }

                // If somehow empty, skip (DO NOT pick random JHS subjects)
                if ($subjectsForLevel->isEmpty()) continue;

                // 3. Create Grades
                foreach ($subjectsForLevel as $subject) {
                    $teacherId = $teachers[array_rand($teachers)];

                    for ($quarter = 1; $quarter <= 4; $quarter++) {
                        Grade::firstOrCreate([
                            'student_id'     => $student->id,
                            'subject_id'     => $subject->id,
                            'quarter'        => $quarter,
                            'school_year_id' => $targetSyId,
                        ], [
                            'teacher_id'     => $teacherId,
                            'grade_value'    => rand(78, 96),
                            'is_locked'      => true,
                            'locked_at'      => now(),
                            'locked_by'      => 'System History Seeder',
                        ]);
                    }
                }
            }
        }
    }
}