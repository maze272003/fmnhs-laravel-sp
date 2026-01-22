<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Services\DashboardServiceInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardServiceInterface $dashboardService,
        private StudentRepositoryInterface $studentRepository,
        private TeacherRepositoryInterface $teacherRepository
    ) {}

    public function index()
    {
        $data = $this->dashboardService->getAdminDashboard();
        
        $studentsPerGrade = $this->studentRepository->with('section')->all()
            ->groupBy(function ($student) {
                return $student->section->grade_level;
            })
            ->map(function ($students) {
                return $students->count();
            })
            ->sortKeys();
        
        $teachersPerDept = $this->teacherRepository->all()
            ->groupBy(function ($teacher) {
                return $teacher->department;
            })
            ->map(function ($teachers) {
                return $teachers->count();
            });

        return view('admin.dashboard', [
            'totalStudents' => $data['statistics']['total_students'],
            'totalTeachers' => $data['statistics']['total_teachers'],
            'totalSubjects' => $data['statistics']['total_subjects'],
            'studentsPerGrade' => $studentsPerGrade,
            'teachersPerDept' => $teachersPerDept,
            'recentStudents' => $data['recent_students'],
            'recentAnnouncements' => $data['recent_announcements'],
        ]);
    }
}