<?php

namespace App\Repositories\Eloquent;

use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function paginateForAdmin(?string $search, bool $archived, int $perPage = 10): LengthAwarePaginator
    {
        return Teacher::with('advisorySection')
            ->when($archived, fn ($q) => $q->onlyTrashed())
            ->when($search, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('employee_id', 'like', '%' . $searchTerm . '%')
                        ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
                });
            })
            ->orderBy('last_name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getSectionsWithAdvisor(): Collection
    {
        return Section::with('advisor')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function getSubjects(): Collection
    {
        return Subject::all();
    }

    public function create(array $data): Teacher
    {
        return Teacher::create($data);
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->update($data);
        return $teacher->fresh();
    }

    public function clearAdvisoryByTeacherId(int $teacherId): void
    {
        Section::where('teacher_id', $teacherId)->update(['teacher_id' => null]);
    }

    public function assignAdvisorySection(int $teacherId, int $sectionId): void
    {
        Section::whereKey($sectionId)->update(['teacher_id' => $teacherId]);
    }

    public function archive(Teacher $teacher): void
    {
        $teacher->delete();
    }

    public function restoreById(int $id): Teacher
    {
        $teacher = Teacher::onlyTrashed()->findOrFail($id);
        $teacher->restore();
        return $teacher->fresh();
    }
}
