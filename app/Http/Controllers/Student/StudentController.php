<?php
namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\PromotionHistory;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Illuminate\Http\Response;

class StudentController extends Controller
{
    public function grades(Request $request): View
    {
        $student = Auth::guard('student')->user();
        $studentId = $student->id;
        $schoolYear = $request->input('school_year');

        $query = Subject::whereHas('grades', function($q) use ($studentId, $schoolYear) {
            $q->where('student_id', $studentId);
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
        })->with(['grades' => function($q) use ($studentId, $schoolYear) {
            $q->where('student_id', $studentId);
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
        }]);

        $subjects = $query->get();

        // Get available school years for filter
        $schoolYears = Grade::where('student_id', $studentId)
            ->select('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        return view('student.grades', compact('subjects', 'schoolYears', 'schoolYear'));
    }

    public function schedule(Request $request): View
    {
        $student = Auth::guard('student')->user();
        
        $schedules = Schedule::where('section_id', $student->section_id)
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get();

        return view('student.schedule', compact('schedules'));
    }

    /**
     * View enrollment history (promotion timeline).
     */
    public function enrollmentHistory(): View
    {
        $student = Auth::guard('student')->user();
        $student->load('section');

        $history = PromotionHistory::where('student_id', $student->id)
            ->with(['fromSection', 'toSection'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.enrollment_history', compact('student', 'history'));
    }

    public function downloadGrades(Request $request)
    {
        $student = Auth::guard('student')->user();
        $student->load('section');
        $schoolYear = $request->input('school_year');

        $subjects = Subject::whereHas('grades', function($q) use ($student, $schoolYear) {
            $q->where('student_id', $student->id);
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
        })->with(['grades' => function($q) use ($student, $schoolYear) {
            $q->where('student_id', $student->id);
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
        }])->get();

        $pdf = Pdf::loadView('student.pdf-grades', compact('subjects', 'student', 'schoolYear'));

        $suffix = $schoolYear ? "-SY{$schoolYear}" : '';
        return $pdf->download("ReportCard-{$student->last_name}{$suffix}.pdf");
    }
}