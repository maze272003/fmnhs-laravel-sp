<?php

namespace App\Contracts\Services;

interface DashboardServiceInterface
{
    public function getTeacherDashboard(int $teacherId): array;
    public function getStudentDashboard(int $studentId): array;
    public function getAdminDashboard(): array;
}
