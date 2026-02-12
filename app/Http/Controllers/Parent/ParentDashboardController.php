<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    /**
     * Parent dashboard.
     */
    public function index(): View
    {
        $parent = Auth::guard('parent')->user();
        $children = $parent->students ?? collect();

        return view('parent.dashboard', compact('parent', 'children'));
    }

    /**
     * View child's grades.
     */
    public function childGrades(Student $student): View
    {
        $grades = $student->grades()
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('parent.child-grades', compact('student', 'grades'));
    }

    /**
     * View child's attendance.
     */
    public function childAttendance(Student $student): View
    {
        $attendance = $student->attendances()
            ->orderBy('date', 'desc')
            ->paginate(30);

        return view('parent.child-attendance', compact('student', 'attendance'));
    }

    /**
     * View messages.
     */
    public function messages(): View
    {
        $parent = Auth::guard('parent')->user();

        return view('parent.messages', compact('parent'));
    }
}
