<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Student;
use App\Repositories\Contracts\ClassroomAttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ClassroomAttendanceRepository implements ClassroomAttendanceRepositoryInterface
{
    public function getAssignedClassesForTeacher(int $teacherId): Collection
    {
        return Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->unique(fn ($item) => $item->subject_id . '-' . $item->section_id)
            ->values();
    }

    public function findSectionOrFail(int $sectionId)
    {
        return Section::findOrFail($sectionId);
    }

    public function getStudentsBySection(int $sectionId): Collection
    {
        return Student::where('section_id', $sectionId)->orderBy('last_name')->get();
    }

    public function getAttendanceByClassDate(int $subjectId, int $sectionId, string $date): Collection
    {
        return Attendance::where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->where('date', $date)
            ->get()
            ->keyBy('student_id');
    }

    public function upsertAttendance(
        int $studentId,
        int $subjectId,
        string $date,
        array $values
    ): void {
        Attendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'date' => $date,
            ],
            $values
        );
    }

    public function getStudentStatusSummary(int $studentId, ?string $dateFrom, ?string $dateTo): Collection
    {
        $query = Attendance::where('student_id', $studentId);

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function paginateStudentHistory(
        int $studentId,
        ?string $dateFrom,
        ?string $dateTo,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = Attendance::where('student_id', $studentId)
            ->with('subject');

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }
}
