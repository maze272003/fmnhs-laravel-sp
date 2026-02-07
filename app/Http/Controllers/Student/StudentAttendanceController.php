<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function __construct(private readonly StudentAttendanceService $studentAttendance)
    {
    }

    public function index(Request $request): View
    {
        $studentId = Auth::guard('student')->id();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $data = $this->studentAttendance->getAttendanceData($studentId, $dateFrom, $dateTo);
        $summary = $data['summary'];
        $history = $data['history'];

        return view('student.attendance', compact('summary', 'history', 'dateFrom', 'dateTo'));
    }
}
