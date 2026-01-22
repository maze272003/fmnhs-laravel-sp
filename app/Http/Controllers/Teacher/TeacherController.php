<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Contracts\Services\DashboardServiceInterface;
use App\Contracts\Services\GradeServiceInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function __construct(
        private DashboardServiceInterface $dashboardService,
        private GradeServiceInterface $gradeService,
        private StudentRepositoryInterface $studentRepository,
        private SubjectRepositoryInterface $subjectRepository,
        private GradeRepositoryInterface $gradeRepository,
        private ScheduleRepositoryInterface $scheduleRepository,
        private SectionRepositoryInterface $sectionRepository,
        private AnnouncementRepositoryInterface $announcementRepository
    ) {}

    public function dashboard(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        $data = $this->dashboardService->getTeacherDashboard($teacherId);

        return view('teacher.dashboard', [
            'totalClasses' => $data['statistics']['active_assignments'],
            'totalStudents' => $data['statistics']['total_students'],
            'advisoryClass' => $data['advisory_class'] 
                ? "Grade {$data['advisory_class']['grade_level']} - {$data['advisory_class']['name']}" 
                : 'None',
            'recentAnnouncements' => $data['recent_announcements'],
            'teacher' => $data['teacher'],
        ]);
    }

    public function myClasses(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $classes = $this->gradeRepository
            ->where('teacher_id', $teacherId)
            ->with(['subject', 'student.section']) 
            ->all()
            ->groupBy(function($data) {
                return $data->subject_id . '-' . $data->student->section_id;
            })
            ->map(function($group) {
                return [
                    'subject' => $group->first()->subject,
                    'section' => $group->first()->student->section,
                    'student_count' => $group->unique('student_id')->count(),
                    'average_grade' => $group->avg('grade_value'),
                ];
            });

        return view('teacher.classes', compact('classes'));
    }

    public function myStudents(Request $request): View
    {
        $sections = $this->sectionRepository->with('teacher')->all();
        
        $selectedSectionId = $request->section_id;
        $selectedSection = null;
        $students = collect();
        $currentTeacherId = Auth::guard('teacher')->id();

        if ($selectedSectionId) {
            $selectedSection = $this->sectionRepository->with('teacher')->find($selectedSectionId);
            $students = $this->studentRepository->where('section_id', $selectedSectionId)->all();
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection', 'currentTeacherId'));
    }

    public function gradingSheet(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $assignedClasses = $this->scheduleRepository
            ->where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->all()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section_id;
            });

        return view('teacher.select', compact('assignedClasses'));
    }

    public function showClass(Request $request): View
    {
        $request->validate([
            'subject_id' => 'required',
            'section_id' => 'required'
        ]);

        $subject = $this->subjectRepository->findOrFail($request->subject_id);
        $section = $this->sectionRepository->findOrFail($request->section_id);

        $students = $this->studentRepository
            ->where('section_id', $section->id)
            ->with(['grades' => function($q) use ($subject) {
                $q->where('subject_id', $subject->id);
            }])
            ->orderBy('last_name')
            ->all();

        return view('teacher.grade', compact('students', 'subject', 'section'));
    }

    public function storeGrades(Request $request): RedirectResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $teacherId = Auth::guard('teacher')->id();

        $this->gradeService->recordGrades($teacherId, $request->subject_id, $request->grades);

        return redirect()->back()->with('success', 'Grades archived successfully!');
    }
}
