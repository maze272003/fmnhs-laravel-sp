<?php
namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Contracts\Services\GradeServiceInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        private GradeServiceInterface $gradeService,
        private ReportServiceInterface $reportService,
        private ScheduleRepositoryInterface $scheduleRepository,
        private SubjectRepositoryInterface $subjectRepository
    ) {}

    public function grades(): View
    {
        $studentId = Auth::guard('student')->id();

        $subjects = $this->subjectRepository->all()->load(['grades' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->filter(function($subject) use ($studentId) {
            return $subject->grades->where('student_id', $studentId)->isNotEmpty();
        });

        return view('student.grades', compact('subjects'));
    }

    public function schedule(): View
    {
        $student = Auth::guard('student')->user();
        
        $schedules = $this->scheduleRepository->getBySection($student->section_id);

        return view('student.schedule', compact('schedules'));
    }

    public function downloadGrades()
    {
        $student = Auth::guard('student')->user();
        $student->load('section');

        $subjects = $this->subjectRepository->all()->load(['grades' => function($query) use ($student) {
            $query->where('student_id', $student->id);
        }])->filter(function($subject) use ($student) {
            return $subject->grades->where('student_id', $student->id)->isNotEmpty();
        });

        $pdf = Pdf::loadView('student.pdf-grades', compact('subjects', 'student'));

        return $pdf->download("ReportCard-{$student->last_name}.pdf");
    }
}
