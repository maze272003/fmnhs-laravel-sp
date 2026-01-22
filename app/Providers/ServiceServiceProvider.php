<?php

namespace App\Providers;

use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\GradeServiceInterface;
use App\Contracts\Services\AttendanceServiceInterface;
use App\Contracts\Services\AssignmentServiceInterface;
use App\Contracts\Services\SubmissionServiceInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Contracts\Services\DashboardServiceInterface;

use App\Services\AuthService;
use App\Services\GradeService;
use App\Services\AttendanceService;
use App\Services\AssignmentService;
use App\Services\SubmissionService;
use App\Services\NotificationService;
use App\Services\ReportService;
use App\Services\DashboardService;

use Illuminate\Support\ServiceProvider;

class ServiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(GradeServiceInterface::class, GradeService::class);
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(AssignmentServiceInterface::class, AssignmentService::class);
        $this->app->bind(SubmissionServiceInterface::class, SubmissionService::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
        $this->app->bind(ReportServiceInterface::class, ReportService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    public function boot(): void
    {
        //
    }
}
