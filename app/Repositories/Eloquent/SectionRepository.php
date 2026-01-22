<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

class SectionRepository extends BaseRepository implements SectionRepositoryInterface
{
    public function __construct(Section $model)
    {
        parent::__construct($model);
    }

    public function findByGradeLevel(int $gradeLevel): Collection
    {
        return $this->model->where('grade_level', $gradeLevel)
            ->orderBy('name')
            ->get();
    }

    public function findByStrand(string $strand): Collection
    {
        return $this->model->where('strand', $strand)
            ->orderBy('name')
            ->get();
    }

    public function getWithStudents(): Collection
    {
        return $this->model->with('students')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function getWithAdvisor(): Collection
    {
        return $this->model->with('teacher')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('name', 'like', "%{$query}%")
            ->orWhere('grade_level', 'like', "%{$query}%")
            ->orWhere('strand', 'like', "%{$query}%")
            ->with('teacher')
            ->get();
    }
}
