<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Section;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalSubjects = Subject::count();
        $totalSections = Section::count();

        // Students per grade level
        $studentsPerGrade = Student::join('sections', 'students.section_id', '=', 'sections.id')
            ->select('sections.grade_level', DB::raw('count(*) as total'))
            ->groupBy('sections.grade_level')
            ->orderBy('sections.grade_level')
            ->get();

        // Teachers per department
        $teachersPerDept = Teacher::select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();

        // KPI: Enrollment by school year (trend)
        // FIXED: Joined with school_year_configs to get the year string
        $enrollmentByYear = Student::join('school_year_configs', 'students.school_year_id', '=', 'school_year_configs.id')
            ->select('school_year_configs.school_year', DB::raw('count(*) as total'))
            ->groupBy('school_year_configs.school_year')
            ->orderBy('school_year_configs.school_year')
            ->get();

        // KPI: Promotion vs dropout/archived rates
        $promotedCount = Student::where('enrollment_status', 'Promoted')->count();
        $alumniCount = Student::where('is_alumni', true)->count();
        $archivedCount = Student::onlyTrashed()->count();
        $droppedCount = Student::where('enrollment_status', 'Dropped')->count();

        // KPI: Section capacity (students per section)
        $sectionCapacity = Section::withCount('students')
            ->orderBy('grade_level')
            ->get();

        // KPI: Teacher load distribution
        $teacherLoad = Teacher::select('teachers.id', 'teachers.first_name', 'teachers.last_name')
            ->get()
            ->map(function ($teacher) {
                $scheduleCount = Schedule::where('teacher_id', $teacher->id)
                    ->get()
                    ->unique(fn($s) => $s->subject_id . '-' . $s->section_id)
                    ->count();
                $teacher->schedule_count = $scheduleCount;
                return $teacher;
            });

        return view('admin.dashboard', compact(
            'totalStudents', 
            'totalTeachers', 
            'totalSubjects',
            'totalSections',
            'studentsPerGrade',
            'teachersPerDept',
            'enrollmentByYear',
            'promotedCount',
            'alumniCount',
            'archivedCount',
            'droppedCount',
            'sectionCapacity',
            'teacherLoad'
        ));
    }
}