<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        
        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->unique(function ($item) { 
                return $item->subject_id . '-' . $item->section_id; 
            });

        return view('teacher.attendance', compact('assignedClasses'));
    }

    public function show(Request $request): View
{
    $request->validate([
        'subject_id' => 'required|exists:subjects,id', 
        'section_id' => 'required|exists:sections,id', 
        'date'       => 'required|date'
    ]);

    $subjectId = $request->subject_id;
    $sectionId = $request->section_id;
    $date = $request->date;

    // FIX: I-fetch ang Section Model para makuha ang name at grade_level sa view
    $section = Section::findOrFail($sectionId);

    // Kunin ang students sa specific section ID
    $students = Student::where('section_id', $sectionId)->orderBy('last_name')->get();

    // Kunin ang records para sa pre-fill
    $attendances = Attendance::where('subject_id', $subjectId)
        ->where('section_id', $sectionId)
        ->where('date', $date)
        ->get()
        ->keyBy('student_id');

    // Idagdag ang 'section' sa compact list
    return view('teacher.show.attendance', compact('students', 'subjectId', 'section', 'date', 'attendances'));
}

    public function store(Request $request): RedirectResponse
    {
        $teacherId = Auth::guard('teacher')->id();

        foreach ($request->status as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'date'       => $request->date,
                ],
                [
                    'teacher_id' => $teacherId,
                    'section_id' => $request->section_id,
                    'status'     => $status
                ]
            );
        }

        return back()->with('success', 'Attendance record updated!');
    }
}