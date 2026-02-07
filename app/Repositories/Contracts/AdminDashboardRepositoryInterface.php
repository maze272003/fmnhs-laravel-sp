<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface AdminDashboardRepositoryInterface
{
    public function getTotals(): array;

    public function getStudentsPerGrade(): Collection;

    public function getTeachersPerDepartment(): Collection;

    public function getEnrollmentByYear(): Collection;

    public function getStatusCounts(): array;

    public function getSectionCapacity(): Collection;

    public function getTeacherLoad(): Collection;
}
