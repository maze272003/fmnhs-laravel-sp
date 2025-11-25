<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    // 1. Show the "Selector" page (Pick Subject & Section)
    public function gradingSheet()
    {
        $subjects = Subject::all();
        $sections = Student::select('section')->distinct()->pluck('section');

        // BAGUHIN ITO: Ituro sa 'teacher.select'
        return view('teacher.select', compact('subjects', 'sections'));
    }
    public function myStudents(Request $request)
    {
        // 1. Kunin lahat ng Sections para sa dropdown
        $sections = Student::select('section')->distinct()->orderBy('section')->pluck('section');

        // 2. Check kung may piniling section ang teacher
        $selectedSection = $request->section;
        $students = collect(); // Empty muna default

        if ($selectedSection) {
            // Kunin ang students sa section na 'yun
            $students = Student::where('section', $selectedSection)
                        ->orderBy('last_name')
                        ->get();
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection'));
    }

    // 2. Show the actual students for grading
    public function showClass(Request $request)
    {
        $request->validate([
            'subject_id' => 'required',
            'section' => 'required'
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $section = $request->section;

        // Get students in this section
        $students = Student::where('section', $section)
                    ->orderBy('last_name')
                    ->get();

        // Pass the teacher's ID for saving later
        $teacherId = Auth::guard('teacher')->id();

        return view('teacher.grade', compact('students', 'subject', 'section', 'teacherId'));
    }
    public function storeGrades(Request $request)
    {
        // Data structure: grades[student_id][quarter] = value
        $data = $request->validate([
            'grades' => 'required|array',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacherId = Auth::guard('teacher')->id();
        $subjectId = $request->subject_id;

        foreach ($request->grades as $studentId => $quarters) {
            foreach ($quarters as $quarter => $value) {
                
                // Skip kung walang laman ang input
                if ($value === null || $value === '') continue;

                // Update or Create logic
                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'quarter'    => $quarter,
                    ],
                    [
                        'teacher_id'  => $teacherId, // Update who edited last
                        'grade_value' => $value,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Grades successfully saved!');
    }
}