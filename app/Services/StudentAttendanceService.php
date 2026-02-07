<?php

namespace App\Services;

use App\Repositories\Contracts\ClassroomAttendanceRepositoryInterface;

class StudentAttendanceService
{
    public function __construct(private readonly ClassroomAttendanceRepositoryInterface $attendance)
    {
    }

    public function getAttendanceData(int $studentId, ?string $dateFrom, ?string $dateTo): array
    {
        return [
            'summary' => $this->attendance->getStudentStatusSummary($studentId, $dateFrom, $dateTo),
            'history' => $this->attendance->paginateStudentHistory($studentId, $dateFrom, $dateTo, 10),
        ];
    }
}
