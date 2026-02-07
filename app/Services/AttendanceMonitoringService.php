<?php

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;

class AttendanceMonitoringService
{
    public function __construct(private readonly AttendanceRepositoryInterface $attendance)
    {
    }

    public function getAdminAttendanceData(array $filters): array
    {
        return [
            'teachers' => $this->attendance->getTeachersOrdered(),
            'sections' => $this->attendance->getSectionsOrdered(),
            'records' => $this->attendance->paginateForAdmin($filters, 20),
        ];
    }
}
