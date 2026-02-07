<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AttendanceMonitoringService;

class AdminAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceMonitoringService $attendanceMonitoring)
    {
    }

    public function index(Request $request)
    {
        $data = $this->attendanceMonitoring->getAdminAttendanceData(
            $request->only(['date', 'teacher_id', 'section_id', 'status'])
        );

        return view('admin.attendancelogs', $data);
    }
}
