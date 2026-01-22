<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function findByLRN(string $lrn): ?Student
    {
        return $this->model->where('lrn', $lrn)->first();
    }

    public function findByEmail(string $email): ?Student
    {
        return $this->model->where('email', $email)->first();
    }

    public function getBySection(int $sectionId): Collection
    {
        return $this->model->where('section_id', $sectionId)
            ->with('section')
            ->orderBy('last_name')
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('lrn', 'like', "%{$query}%");
        })->with('section')->orderBy('last_name')->get();
    }

    public function getGradeReport(int $studentId): Student
    {
        return $this->with(['section', 'grades.subject'])
            ->findOrFail($studentId);
    }
}
