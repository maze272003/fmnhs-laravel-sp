<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ClassroomAttendanceRepositoryInterface
{
    public function getAssignedClassesForTeacher(int $teacherId): Collection;

    public function findSectionOrFail(int $sectionId);

    public function getStudentsBySection(int $sectionId): Collection;

    public function getAttendanceByClassDate(int $subjectId, int $sectionId, string $date): Collection;

    public function upsertAttendance(
        int $studentId,
        int $subjectId,
        string $date,
        array $values
    ): void;

    public function getStudentStatusSummary(int $studentId, ?string $dateFrom, ?string $dateTo): Collection;

    public function paginateStudentHistory(
        int $studentId,
        ?string $dateFrom,
        ?string $dateTo,
        int $perPage = 10
    ): LengthAwarePaginator;
}
