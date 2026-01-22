<?php

namespace App\Contracts\Repositories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStudentAndDate(int $studentId, string $date): ?Attendance;

    public function getAttendanceForClass(int $subjectId, int $sectionId, string $date): Collection;

    public function getStudentAttendance(int $studentId): Collection;

    public function getAttendanceSummary(int $studentId, int $subjectId): array;

    public function markAttendance(array $data): bool;
}
