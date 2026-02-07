<?php
namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Grade;
use App\Models\PromotionHistory;
use App\Models\SchoolYearConfig;
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
        $schoolYearId = $request->integer('school_year_id');
        $gradeLevel = $request->input('grade_level');

        $query = Subject::whereHas('grades', function($q) use ($studentId, $schoolYearId, $gradeLevel) {
            $q->where('student_id', $studentId);
            if ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            }
            if ($gradeLevel) {
                $q->whereHas('student', function($sq) use ($gradeLevel) {
                    $sq->whereHas('promotionHistories', function($ph) use ($gradeLevel) {
                        $ph->where(function($inner) use ($gradeLevel) {
                            $inner->where('from_grade_level', $gradeLevel)
                                  ->orWhere('to_grade_level', $gradeLevel);
                        });
                    });
                });
            }
        })->with(['grades' => function($q) use ($studentId, $schoolYearId) {
            $q->where('student_id', $studentId);
            if ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            }
        }]);

        $subjects = $query->get();

        // Get available school years for filter
        $schoolYearIds = Grade::where('student_id', $studentId)
            ->select('school_year_id')
            ->distinct()
            ->pluck('school_year_id')
            ->filter();

        $schoolYears = SchoolYearConfig::whereIn('id', $schoolYearIds)
            ->orderBy('school_year', 'desc')
            ->get(['id', 'school_year']);

        $schoolYearLabel = SchoolYearConfig::find($schoolYearId)?->school_year;

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

        return view('student.grades', compact(
            'subjects',
            'schoolYears',
            'schoolYearId',
            'schoolYearLabel',
            'gradeLevels',
            'gradeLevel'
        ));
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
        $schoolYearId = $request->integer('school_year_id');
        $schoolYearLabel = SchoolYearConfig::find($schoolYearId)?->school_year;

        $subjects = Subject::whereHas('grades', function($q) use ($student, $schoolYearId) {
            $q->where('student_id', $student->id);
            if ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            }
        })->with(['grades' => function($q) use ($student, $schoolYearId) {
            $q->where('student_id', $student->id);
            if ($schoolYearId) {
                $q->where('school_year_id', $schoolYearId);
            }
        }])->get();

        $pdf = Pdf::loadView('student.pdf-grades', compact(
            'subjects',
            'student',
            'schoolYearId',
            'schoolYearLabel'
        ));

        $suffix = $schoolYearLabel ? "-SY{$schoolYearLabel}" : '';
        return $pdf->download("ReportCard-{$student->last_name}{$suffix}.pdf");
    }
}
