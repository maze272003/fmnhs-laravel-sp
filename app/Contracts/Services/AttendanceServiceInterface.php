<?php

namespace App\Contracts\Services;

interface AttendanceServiceInterface
{
    public function markAttendance(int $sectionId, int $subjectId, string $date, array $attendanceData, int $teacherId): array;
    public function getAttendanceForClass(int $sectionId, int $subjectId, string $date): array;
    public function getStudentAttendance(int $studentId, ?string $startDate = null, ?string $endDate = null): array;
    public function getAttendanceSummary(int $sectionId, ?string $startDate = null, ?string $endDate = null): array;
    public function getAttendanceByDate(string $date, ?int $sectionId = null): array;
}
