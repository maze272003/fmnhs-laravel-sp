<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Grade;
use App\Models\Schedule;
use App\Models\Announcement;
use App\Models\Section;
use App\Models\Attendance;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use App\Helpers\SchoolYearHelper;
use Illuminate\Support\Facades\DB;


class TeacherController extends Controller
{
    /**
     * Dashboard view with dynamic counters and analytics
     */
    public function dashboard(Request $request): View
    {
        $teacher = Auth::guard('teacher')->user();
        $schoolYear = $request->input('school_year', SchoolYearHelper::current());

        // Count unique sections handled via Schedule
        $totalClasses = Schedule::where('teacher_id', $teacher->id)
            ->get()
            ->unique(function($q) { return $q->subject_id . '-' . $q->section_id; })
            ->count();
        
        // Count unique students across all handled sections
        $sectionIds = Schedule::where('teacher_id', $teacher->id)->pluck('section_id')->unique();
        $totalStudents = Student::whereIn('section_id', $sectionIds)->count();
        
        // Fetch Advisory Class
        $advisory = Section::where('teacher_id', $teacher->id)->first();
        
        $recentAnnouncements = Announcement::latest()->take(3)->get();

        // Analytics: Attendance trends
        $attendanceTrends = Attendance::where('teacher_id', $teacher->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // Analytics: Grade distribution
        $gradeDistribution = Grade::where('teacher_id', $teacher->id)
            ->where('school_year', $schoolYear)
            ->select(
                DB::raw("CASE 
                    WHEN grade_value >= 90 THEN 'Outstanding (90-100)'
                    WHEN grade_value >= 85 THEN 'Very Satisfactory (85-89)'
                    WHEN grade_value >= 80 THEN 'Satisfactory (80-84)'
                    WHEN grade_value >= 75 THEN 'Fairly Satisfactory (75-79)'
                    ELSE 'Did Not Meet (Below 75)'
                END as grade_range"),
                DB::raw('count(*) as total')
            )
            ->groupBy('grade_range')
            ->get();

        // Available school years for filter
        $schoolYears = Grade::where('teacher_id', $teacher->id)
            ->select('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        return view('teacher.dashboard', [
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'advisoryClass' => $advisory ? "Grade {$advisory->grade_level} - {$advisory->name}" : 'None',
            'recentAnnouncements' => $recentAnnouncements,
            'teacher' => $teacher,
            'attendanceTrends' => $attendanceTrends,
            'gradeDistribution' => $gradeDistribution,
            'schoolYears' => $schoolYears,
            'selectedSchoolYear' => $schoolYear,
        ]);
    }

    /**
     * myClasses uses Schedule as the source of truth
     */
    public function myClasses(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $classes = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->groupBy(function($sched) {
                return $sched->subject_id . '-' . $sched->section_id;
            })
            ->map(function($group) use ($teacherId) {
                $first = $group->first();
                $studentCount = Student::where('section_id', $first->section_id)->count();
                $avgGrade = Grade::where('teacher_id', $teacherId)
                    ->where('subject_id', $first->subject_id)
                    ->whereHas('student', fn($q) => $q->where('section_id', $first->section_id))
                    ->avg('grade_value');

                return [
                    'subject' => $first->subject,
                    'section' => $first->section,
                    'student_count' => $studentCount,
                    'average_grade' => $avgGrade ?? 0,
                ];
            });

        return view('teacher.classes', compact('classes'));
    }

    /**
     * Show students filtered by section
     */
    public function myStudents(Request $request): View
    {
        $sections = Section::with('teacher')->orderBy('grade_level')->get();
        
        $selectedSectionId = $request->section_id;
        $selectedSection = null;
        $students = collect();
        $currentTeacherId = Auth::guard('teacher')->id();

        if ($selectedSectionId) {
            $selectedSection = Section::with('teacher')->find($selectedSectionId);
            $students = Student::where('section_id', $selectedSectionId)
                        ->orderBy('last_name')
                        ->get();
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection', 'currentTeacherId'));
    }

    public function gradingSheet(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
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

        $subject = Subject::findOrFail($request->subject_id);
        $section = Section::findOrFail($request->section_id);
        $schoolYear = $request->input('school_year', SchoolYearHelper::current());

        $students = Student::where('section_id', $section->id)
                    ->with(['grades' => function($q) use ($subject, $schoolYear) {
                        $q->where('subject_id', $subject->id)
                          ->where('school_year', $schoolYear);
                    }])
                    ->orderBy('last_name')
                    ->get();

        // Check if grades are locked
        $gradesLocked = Grade::where('subject_id', $subject->id)
            ->where('school_year', $schoolYear)
            ->whereHas('student', fn($q) => $q->where('section_id', $section->id))
            ->where('is_locked', true)
            ->exists();

        return view('teacher.grade', compact('students', 'subject', 'section', 'schoolYear', 'gradesLocked'));
    }

    public function storeGrades(Request $request): RedirectResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.*' => 'nullable|numeric|min:60|max:100',
            'subject_id' => 'required|exists:subjects,id',
            'school_year' => 'nullable|string|max:20',
        ]);

        $teacherId = Auth::guard('teacher')->id();
        $teacher = Auth::guard('teacher')->user();
        $schoolYear = $request->input('school_year', SchoolYearHelper::current());

        // Check if grades are locked
        $lockedGrades = Grade::where('subject_id', $request->subject_id)
            ->where('school_year', $schoolYear)
            ->where('is_locked', true)
            ->exists();

        if ($lockedGrades) {
            return redirect()->back()->withErrors(['error' => 'Cannot modify grades: grades are locked for this subject and school year. Contact admin to unlock.']);
        }

        foreach ($request->grades as $studentId => $quarters) {
            foreach ($quarters as $quarter => $value) {
                if ($value === null || $value === '') continue;

                $existing = Grade::where([
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'quarter'    => $quarter,
                    'school_year' => $schoolYear,
                ])->first();

                $oldValue = $existing ? $existing->grade_value : null;

                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $request->subject_id,
                        'quarter'    => $quarter,
                        'school_year' => $schoolYear,
                    ],
                    [
                        'teacher_id'  => $teacherId,
                        'grade_value' => $value,
                    ]
                );

                // Audit trail for grade changes
                if ($oldValue !== null && $oldValue != $value) {
                    $grade = Grade::where([
                        'student_id' => $studentId,
                        'subject_id' => $request->subject_id,
                        'quarter'    => $quarter,
                        'school_year' => $schoolYear,
                    ])->first();

                    AuditTrail::log(
                        'Grade', $grade->id, 'updated',
                        'grade_value', (string)$oldValue, (string)$value,
                        'teacher', $teacherId, $teacher->first_name . ' ' . $teacher->last_name
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Grades saved successfully!');
    }

    /**
     * Generate printable PDF grade sheet for a class.
     */
    public function printGradeSheet(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $teacher = Auth::guard('teacher')->user();
        $subject = Subject::findOrFail($request->subject_id);
        $section = Section::findOrFail($request->section_id);
        $schoolYear = $request->input('school_year', SchoolYearHelper::current());

        $students = Student::where('section_id', $section->id)
            ->with(['grades' => function ($q) use ($subject, $schoolYear) {
                $q->where('subject_id', $subject->id)
                  ->where('school_year', $schoolYear);
            }])
            ->orderBy('last_name')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.pdf-gradesheet', compact(
            'students', 'subject', 'section', 'schoolYear', 'teacher'
        ));

        return $pdf->download("GradeSheet-{$subject->code}-{$section->name}-{$schoolYear}.pdf");
    }
}