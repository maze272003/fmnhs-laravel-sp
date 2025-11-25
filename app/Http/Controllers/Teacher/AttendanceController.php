<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // 1. List Assigned Classes (Reuse the Select View logic)
    public function index()
    {
        $teacherId = Auth::guard('teacher')->id();
        
        // Reuse the "My Classes" logic
        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with('subject')
            ->get()
            ->unique(function ($item) { return $item->subject_id . '-' . $item->section; });

        return view('teacher.attendance', compact('assignedClasses'));
    }

    // 2. Show Attendance Sheet for specific Date & Class
    public function show(Request $request)
    {
        $request->validate([
            'subject_id' => 'required', 
            'section' => 'required', 
            'date' => 'required|date'
        ]);

        $subjectId = $request->subject_id;
        $section = $request->section;
        $date = $request->date;

        // Get Students
        $students = Student::where('section', $section)->orderBy('last_name')->get();

        // Get Existing Attendance for this date (to pre-fill the form)
        $attendances = Attendance::where('subject_id', $subjectId)
            ->where('section', $section)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id'); // Key by student ID for easy lookup

        return view('teacher.show.attendance', compact('students', 'subjectId', 'section', 'date', 'attendances'));
    }

    // 3. Save Attendance
    public function store(Request $request)
    {
        $teacherId = Auth::guard('teacher')->id();
        $date = $request->date;
        $subjectId = $request->subject_id;
        $section = $request->section;

        foreach ($request->status as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'date' => $date,
                ],
                [
                    'teacher_id' => $teacherId,
                    'section' => $section,
                    'status' => $status
                ]
            );
        }

        return back()->with('success', 'Attendance recorded successfully!');
    }
}