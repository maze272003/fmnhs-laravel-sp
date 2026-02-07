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
        $gradeLevel = $request->input('grade_level');

        $query = Subject::whereHas('grades', function($q) use ($studentId, $schoolYear, $gradeLevel) {
            $q->where('student_id', $studentId);
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
            if ($gradeLevel) {
                $q->whereHas('student', function($sq) use ($gradeLevel) {
                    $sq->whereHas('promotionHistories', function($ph) use ($gradeLevel) {
                        $ph->where('from_grade_level', $gradeLevel)
                           ->orWhere('to_grade_level', $gradeLevel);
                    });
                });
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

        // Get available grade levels from promotion history
        $gradeLevels = PromotionHistory::where('student_id', $studentId)
            ->select('from_grade_level')
            ->distinct()
            ->pluck('from_grade_level')
            ->merge(
                PromotionHistory::where('student_id', $studentId)
                    ->select('to_grade_level')
                    ->distinct()
                    ->pluck('to_grade_level')
            )
            ->unique()
            ->sort()
            ->values();

        return view('student.grades', compact('subjects', 'schoolYears', 'schoolYear', 'gradeLevels', 'gradeLevel'));
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