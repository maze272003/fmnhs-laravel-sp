<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentController extends Controller
{
    public function grades()
    {
        $studentId = Auth::guard('student')->id();

        // Logic: Get all Subjects that have grades for this student.
        // Also 'eager load' the specific grades for this student to avoid loading everyone else's grades.
        $subjects = Subject::whereHas('grades', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->with(['grades' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->get();

        return view('student.grades', compact('subjects'));
    }
    public function downloadGrades()
    {
        $student = Auth::guard('student')->user();
        $studentId = $student->id;

        // Fetch the same data as the grades page
        $subjects = Subject::whereHas('grades', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->with(['grades' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->get();

        // Load a specific view for PDF (we will create this next)
        $pdf = Pdf::loadView('student.pdf-grades', compact('subjects', 'student'));

        // Download the file
        return $pdf->download('ReportCard-' . $student->last_name . '.pdf');
    }
}