<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

class SubjectRepository extends BaseRepository implements SubjectRepositoryInterface
{
    public function __construct(Subject $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Subject
    {
        return $this->model->where('code', $code)->first();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->get();
    }

    public function getWithGrades(): Collection
    {
        return $this->model->has('grades')
            ->with('grades.student')
            ->get();
    }

    public function getActive(): Collection
    {
        return $this->model->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }
}
