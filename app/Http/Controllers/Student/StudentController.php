<?php
namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Schedule;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function grades(): View
    {
        $studentId = Auth::guard('student')->id();

        $subjects = Subject::whereHas('grades', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->with(['grades' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->get();

        return view('student.grades', compact('subjects'));
    }

    public function schedule(): View
    {
        $student = Auth::guard('student')->user();
        
        // Refactored: Filter schedules by section_id relationship
        $schedules = Schedule::where('section_id', $student->section_id)
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get();

        return view('student.schedule', compact('schedules'));
    }

    public function downloadGrades()
    {
        $student = Auth::guard('student')->user();
        // Ensure section relationship is loaded for PDF info
        $student->load('section');

        $subjects = Subject::whereHas('grades', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })->with(['grades' => function($query) use ($student) {
            $query->where('student_id', $student->id);
        }])->get();

        $pdf = Pdf::loadView('student.pdf-grades', compact('subjects', 'student'));

        return $pdf->download("ReportCard-{$student->last_name}.pdf");
    }
}