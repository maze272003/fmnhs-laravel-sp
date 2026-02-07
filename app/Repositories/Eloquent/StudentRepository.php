<?php

namespace App\Repositories\Eloquent;

use App\Models\SchoolYearConfig;
use App\Models\Section;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StudentRepository implements StudentRepositoryInterface
{
    public function getSectionsGroupedWithActiveCounts(): Collection
    {
        return Section::withCount([
            'students' => function ($query) {
                $query->where('is_alumni', false)->whereNull('deleted_at');
            },
        ])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get()
            ->groupBy('grade_level');
    }

    public function getAllSectionsOrdered(): Collection
    {
        return Section::orderBy('grade_level')->orderBy('name')->get();
    }

    public function getSchoolYearsDesc(): Collection
    {
        return SchoolYearConfig::orderBy('school_year', 'desc')->get();
    }

    public function paginateActiveForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Student::with(['section', 'schoolYearConfig'])
            ->where('is_alumni', false);

        if (!empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('lrn', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filters['school_year_id'])) {
            $query->where('school_year_id', $filters['school_year_id']);
        }

        return $query->orderBy('last_name')->paginate($perPage)->withQueryString();
    }

    public function findSection(?int $sectionId)
    {
        if (!$sectionId) {
            return null;
        }

        return Section::find($sectionId);
    }

    public function findWithRecordOrFail(int $id): Student
    {
        return Student::with(['grades.subject', 'promotionHistory', 'schoolYearConfig'])
            ->withTrashed()
            ->findOrFail($id);
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }

    public function findOrFail(int $id): Student
    {
        return Student::findOrFail($id);
    }

    public function update(Student $student, array $data): Student
    {
        $student->update($data);
        return $student->fresh();
    }

    public function findManyByIds(array $ids): Collection
    {
        return Student::with(['section', 'schoolYearConfig'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');
    }

    public function archivedPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Student::onlyTrashed()
            ->with('section')
            ->orderBy('deleted_at', 'desc')
            ->paginate($perPage);
    }

    public function findArchivedOrFail(int $id): Student
    {
        return Student::onlyTrashed()->findOrFail($id);
    }
}

