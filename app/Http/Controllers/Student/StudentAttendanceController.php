<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class StudentAttendanceController extends Controller
{
    public function index()
    {
        $studentId = Auth::guard('student')->id();

        // 1. Summary Stats
        $summary = Attendance::where('student_id', $studentId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // 2. Detailed History
        $history = Attendance::where('student_id', $studentId)
            ->with('subject')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('student.attendance', compact('summary', 'history'));
    }
}