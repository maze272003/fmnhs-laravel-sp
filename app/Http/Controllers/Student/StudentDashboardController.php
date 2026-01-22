<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Contracts\Services\DashboardServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(
        private DashboardServiceInterface $dashboardService
    ) {}

    public function index(): View
    {
        $studentId = Auth::guard('student')->id();
        $data = $this->dashboardService->getStudentDashboard($studentId);
        $advisor = $data['student']['section']['advisor'] ?? null;

        return view('student.dashboard', [
            'student' => $data['student'],
            'advisor' => $advisor,
            'announcements' => $data['recent_announcements'],
            'statistics' => $data['statistics'],
            'pendingAssignments' => $data['pending_assignments'],
            'recentGrades' => $data['recent_grades'],
            'recentAttendance' => $data['recent_attendance'],
        ]);
    }
}
}