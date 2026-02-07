<?php

namespace App\Repositories\Eloquent;

use App\Models\Schedule;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\Contracts\AdminDashboardRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardRepository implements AdminDashboardRepositoryInterface
{
    public function getTotals(): array
    {
        return [
            'totalStudents' => Student::count(),
            'totalTeachers' => Teacher::count(),
            'totalSubjects' => Subject::count(),
            'totalSections' => Section::count(),
        ];
    }

    public function getStudentsPerGrade(): Collection
    {
        return Student::join('sections', 'students.section_id', '=', 'sections.id')
            ->select('sections.grade_level', DB::raw('count(*) as total'))
            ->groupBy('sections.grade_level')
            ->orderBy('sections.grade_level')
            ->get();
    }

    public function getTeachersPerDepartment(): Collection
    {
        return Teacher::select('department', DB::raw('count(*) as total'))
            ->groupBy('department')
            ->get();
    }

    public function getEnrollmentByYear(): Collection
    {
        return Student::join('school_year_configs', 'students.school_year_id', '=', 'school_year_configs.id')
            ->select('school_year_configs.school_year', DB::raw('count(*) as total'))
            ->groupBy('school_year_configs.school_year')
            ->orderBy('school_year_configs.school_year')
            ->get();
    }

    public function getStatusCounts(): array
    {
        return [
            'promotedCount' => Student::where('enrollment_status', 'Promoted')->count(),
            'alumniCount' => Student::where('is_alumni', true)->count(),
            'archivedCount' => Student::onlyTrashed()->count(),
            'droppedCount' => Student::where('enrollment_status', 'Dropped')->count(),
        ];
    }

    public function getSectionCapacity(): Collection
    {
        return Section::withCount('students')
            ->orderBy('grade_level')
            ->get();
    }

    public function getTeacherLoad(): Collection
    {
        return Teacher::select('teachers.id', 'teachers.first_name', 'teachers.last_name')
            ->get()
            ->map(function ($teacher) {
                $teacher->schedule_count = Schedule::where('teacher_id', $teacher->id)
                    ->get()
                    ->unique(fn ($s) => $s->subject_id . '-' . $s->section_id)
                    ->count();

                return $teacher;
            });
    }
}
