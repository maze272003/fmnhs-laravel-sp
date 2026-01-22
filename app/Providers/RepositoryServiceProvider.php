<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Repositories\SubmissionRepositoryInterface;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;

use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\StudentRepository;
use App\Repositories\Eloquent\TeacherRepository;
use App\Repositories\Eloquent\AdminRepository;
use App\Repositories\Eloquent\SubjectRepository;
use App\Repositories\Eloquent\GradeRepository;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Eloquent\AssignmentRepository;
use App\Repositories\Eloquent\SubmissionRepository;
use App\Repositories\Eloquent\AnnouncementRepository;
use App\Repositories\Eloquent\ScheduleRepository;
use App\Repositories\Eloquent\SectionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(AssignmentRepositoryInterface::class, AssignmentRepository::class);
        $this->app->bind(SubmissionRepositoryInterface::class, SubmissionRepository::class);
        $this->app->bind(AnnouncementRepositoryInterface::class, AnnouncementRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
