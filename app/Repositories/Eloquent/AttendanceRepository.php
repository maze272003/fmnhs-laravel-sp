<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Models\Section;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function getTeachersOrdered(): Collection
    {
        return Teacher::orderBy('last_name')->get();
    }

    public function getSectionsOrdered(): Collection
    {
        return Section::orderBy('grade_level')->orderBy('name')->get();
    }

    public function paginateForAdmin(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Attendance::with(['student', 'teacher', 'subject', 'section']);

        if (!empty($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (!empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage)->withQueryString();
    }
}
