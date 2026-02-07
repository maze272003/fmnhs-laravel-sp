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
use App\Models\SchoolYearConfig;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TeacherController extends Controller
{
    /**
     * Dashboard view with dynamic counters and analytics
     */
    public function dashboard(Request $request): View
    {
        $teacher = Auth::guard('teacher')->user();
        
        // 1. Determine School Year
        $activeSchoolYearId = SchoolYearConfig::active()?->id;
        $selectedSchoolYearId = $request->integer('school_year_id') ?: $activeSchoolYearId;

        // 2. Fetch Sections for the SELECTED School Year
        // Filter Advisory Sections
        $advisorySectionIds = Section::where('teacher_id', $teacher->id)
            ->where('school_year_id', $selectedSchoolYearId)
            ->pluck('id');

        // Filter Schedule Sections (via relationship)
        $scheduleSectionIds = Schedule::where('teacher_id', $teacher->id)
            ->whereHas('section', fn($q) => $q->where('school_year_id', $selectedSchoolYearId))
            ->pluck('section_id');
        
        // Merge to get all valid Section IDs for this Year
        $allAssignedSectionIds = $scheduleSectionIds->merge($advisorySectionIds)->unique();

        // 3. Stats Counters (Filtered by Year)
        $totalClasses = Schedule::where('teacher_id', $teacher->id)
            ->whereHas('section', fn($q) => $q->where('school_year_id', $selectedSchoolYearId))
            ->get()
            ->unique(function($q) { return $q->subject_id . '-' . $q->section_id; })
            ->count();
        
        $totalStudents = Student::whereIn('section_id', $allAssignedSectionIds)->count();
        
        // Get Advisory Class Name (if exists for this year)
        $advisory = Section::where('teacher_id', $teacher->id)
            ->where('school_year_id', $selectedSchoolYearId)
            ->first();
        
        $recentAnnouncements = Announcement::latest()->take(3)->get();

        // 4. Analytics: Attendance Trends
        // FIX: Removed 'where(school_year_id)' and used 'whereIn(section_id)' instead
        $attendanceTrends = Attendance::where('teacher_id', $teacher->id)
            ->whereIn('section_id', $allAssignedSectionIds) // Implicitly filters by Year via Section
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // 5. Analytics: Grade Distribution
        $gradeDistribution = Grade::where('teacher_id', $teacher->id)
            ->where('school_year_id', $selectedSchoolYearId)
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

        // 6. Filter Dropdown Logic
        // Fetch all school years the teacher has ever been assigned to
        $syIdsFromGrades = Grade::where('teacher_id', $teacher->id)->pluck('school_year_id');
        $syIdsFromSections = Section::where('teacher_id', $teacher->id)->pluck('school_year_id');
        
        $allSyIds = $syIdsFromGrades->merge($syIdsFromSections)->unique()->filter();

        $schoolYears = SchoolYearConfig::whereIn('id', $allSyIds)
            ->orderBy('school_year', 'desc')
            ->get(['id', 'school_year']);

        // Fallback if no history found
        if ($schoolYears->isEmpty()) {
            $schoolYears = SchoolYearConfig::orderBy('school_year', 'desc')->get(['id', 'school_year']);
        }

        $selectedSchoolYearLabel = $schoolYears->firstWhere('id', $selectedSchoolYearId)?->school_year
            ?? SchoolYearConfig::find($selectedSchoolYearId)?->school_year;

        return view('teacher.dashboard', [
            'totalClasses' => $totalClasses,
            'totalStudents' => $totalStudents,
            'advisoryClass' => $advisory ? "Grade {$advisory->grade_level} - {$advisory->name}" : 'None',
            'recentAnnouncements' => $recentAnnouncements,
            'teacher' => $teacher,
            'attendanceTrends' => $attendanceTrends,
            'gradeDistribution' => $gradeDistribution,
            'schoolYears' => $schoolYears,
            'selectedSchoolYearId' => $selectedSchoolYearId,
            'selectedSchoolYearLabel' => $selectedSchoolYearLabel,
        ]);
    }

    /**
     * myClasses uses Schedule as the source of truth
     */
    public function myClasses(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $classes = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section.schoolYear']) 
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
        $currentTeacherId = Auth::guard('teacher')->id();

        $advisorySectionIds = Section::where('teacher_id', $currentTeacherId)->pluck('id');
        $scheduleSectionIds = Schedule::where('teacher_id', $currentTeacherId)->pluck('section_id');
        $allSectionIds = $advisorySectionIds->merge($scheduleSectionIds)->unique();

        $sections = Section::with(['teacher', 'schoolYear'])
            ->whereIn('id', $allSectionIds)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
        
        $selectedSectionId = $request->section_id;
        $selectedSection = null;
        $students = collect();

        if ($selectedSectionId) {
            if ($allSectionIds->contains($selectedSectionId)) {
                $selectedSection = Section::with(['teacher', 'schoolYear'])->find($selectedSectionId);
                $students = Student::where('section_id', $selectedSectionId)->orderBy('last_name')->get();
            } else {
                abort(403, 'You are not assigned to this section.');
            }
        }

        return view('teacher.student', compact('sections', 'students', 'selectedSection', 'currentTeacherId'));
    }

    public function gradingSheet(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section.schoolYear']) 
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section_id;
            });

        return view('teacher.select', compact('assignedClasses'));
    }

    public function showClass(Request $request): View
    {
        $request->validate([ 'subject_id' => 'required', 'section_id' => 'required' ]);

        $subject = Subject::findOrFail($request->subject_id);
        $section = Section::findOrFail($request->section_id);
        $activeSchoolYearId = SchoolYearConfig::active()?->id;
        $schoolYearId = $request->integer('school_year_id') ?: $activeSchoolYearId;
        $schoolYearLabel = SchoolYearConfig::find($schoolYearId)?->school_year;

        $students = Student::where('section_id', $section->id)
            ->with(['grades' => function($q) use ($subject, $schoolYearId) {
                $q->where('subject_id', $subject->id)->where('school_year_id', $schoolYearId);
            }])
            ->orderBy('last_name')
            ->get();

        $gradesLocked = Grade::where('subject_id', $subject->id)
            ->where('school_year_id', $schoolYearId)
            ->whereHas('student', fn($q) => $q->where('section_id', $section->id))
            ->where('is_locked', true)
            ->exists();

        return view('teacher.grade', compact('students', 'subject', 'section', 'schoolYearId', 'schoolYearLabel', 'gradesLocked'));
    }

    public function storeGrades(Request $request): RedirectResponse
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.*' => 'nullable|numeric|min:60|max:100',
            'subject_id' => 'required|exists:subjects,id',
            'school_year_id' => 'nullable|exists:school_year_configs,id',
        ]);

        $teacherId = Auth::guard('teacher')->id();
        $teacher = Auth::guard('teacher')->user();
        $schoolYearId = $request->integer('school_year_id') ?: SchoolYearConfig::active()?->id;

        $lockedGrades = Grade::where('subject_id', $request->subject_id)
            ->where('school_year_id', $schoolYearId)
            ->where('is_locked', true)
            ->exists();

        if ($lockedGrades) {
            return redirect()->back()->withErrors(['error' => 'Cannot modify grades: grades are locked.']);
        }

        foreach ($request->grades as $studentId => $quarters) {
            foreach ($quarters as $quarter => $value) {
                if ($value === null || $value === '') continue;

                $existing = Grade::where([
                    'student_id' => $studentId,
                    'subject_id' => $request->subject_id,
                    'quarter'    => $quarter,
                    'school_year_id' => $schoolYearId,
                ])->first();

                $oldValue = $existing ? $existing->grade_value : null;

                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $request->subject_id,
                        'quarter'    => $quarter,
                        'school_year_id' => $schoolYearId,
                    ],
                    [
                        'teacher_id'  => $teacherId,
                        'grade_value' => $value,
                    ]
                );

                if ($oldValue !== null && $oldValue != $value) {
                    $grade = Grade::where([
                        'student_id' => $studentId,
                        'subject_id' => $request->subject_id,
                        'quarter'    => $quarter,
                        'school_year_id' => $schoolYearId,
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
        $schoolYearId = $request->integer('school_year_id') ?: SchoolYearConfig::active()?->id;
        $schoolYear = SchoolYearConfig::find($schoolYearId)?->school_year ?? 'N/A';

        $students = Student::where('section_id', $section->id)
            ->with(['grades' => function ($q) use ($subject, $schoolYearId) {
                $q->where('subject_id', $subject->id)
                  ->where('school_year_id', $schoolYearId);
            }])
            ->orderBy('last_name')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.pdf-gradesheet', compact(
            'students', 'subject', 'section', 'schoolYear', 'teacher'
        ));

        return $pdf->download("GradeSheet-{$subject->code}-{$section->name}-{$schoolYear}.pdf");
    }
}
