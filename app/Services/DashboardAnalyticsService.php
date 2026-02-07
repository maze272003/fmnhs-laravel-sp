<?php

namespace App\Services;

use App\Repositories\Contracts\AdminDashboardRepositoryInterface;

class DashboardAnalyticsService
{
    public function __construct(private readonly AdminDashboardRepositoryInterface $dashboard)
    {
    }

    public function getAdminDashboardData(): array
    {
        return array_merge(
            $this->dashboard->getTotals(),
            [
                'studentsPerGrade' => $this->dashboard->getStudentsPerGrade(),
                'teachersPerDept' => $this->dashboard->getTeachersPerDepartment(),
                'enrollmentByYear' => $this->dashboard->getEnrollmentByYear(),
            ],
            $this->dashboard->getStatusCounts(),
            [
                'sectionCapacity' => $this->dashboard->getSectionCapacity(),
                'teacherLoad' => $this->dashboard->getTeacherLoad(),
            ]
        );
    }
}
