<?php

namespace App\Contracts\Services;

interface GradeServiceInterface
{
    public function recordGrade(int $studentId, int $subjectId, string $quarter, float $value, int $teacherId): array;
    public function updateGrade(int $gradeId, float $value, int $teacherId): array;
    public function getStudentGrades(int $studentId, ?string $quarter = null): array;
    public function getClassGrades(int $subjectId, int $sectionId, string $quarter): array;
    public function calculateAverage(int $studentId, ?string $quarter = null): float;
    public function generateReportCard(int $studentId, string $schoolYear): array;
}
