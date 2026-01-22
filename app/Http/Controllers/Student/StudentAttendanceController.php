<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Services\AttendanceServiceInterface;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function __construct(
        private AttendanceServiceInterface $attendanceService
    ) {}

    public function index(): View
    {
        $studentId = Auth::guard('student')->id();

        $data = $this->attendanceService->getStudentAttendance($studentId);

        return view('student.attendance', [
            'summary' => $data['summary'],
            'history' => $data['history']
        ]);
    }
}
