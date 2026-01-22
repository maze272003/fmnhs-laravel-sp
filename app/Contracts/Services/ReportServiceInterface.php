<?php

namespace App\Contracts\Services;

interface ReportServiceInterface
{
    public function generateReportCard(int $studentId, string $schoolYear): string;
    public function generateAttendanceReport(int $sectionId, ?string $startDate = null, ?string $endDate = null): string;
    public function generateGradeReport(int $sectionId, int $subjectId, string $quarter): string;
    public function getClassSummary(int $sectionId, string $quarter): array;
    public function getStudentPerformance(int $studentId, string $schoolYear): array;
}
