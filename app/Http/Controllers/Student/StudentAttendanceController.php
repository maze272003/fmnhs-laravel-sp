<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = Auth::guard('student')->id();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // 1. Summary Stats
        $summaryQuery = Attendance::where('student_id', $studentId);
        if ($dateFrom) {
            $summaryQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $summaryQuery->whereDate('date', '<=', $dateTo);
        }
        $summary = (clone $summaryQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // 2. Detailed History with Subject Eager Loading
        $historyQuery = Attendance::where('student_id', $studentId)
            ->with('subject');
        if ($dateFrom) {
            $historyQuery->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $historyQuery->whereDate('date', '<=', $dateTo);
        }
        $history = $historyQuery->orderBy('date', 'desc')->paginate(10);

        return view('student.attendance', compact('summary', 'history', 'dateFrom', 'dateTo'));
    }
}