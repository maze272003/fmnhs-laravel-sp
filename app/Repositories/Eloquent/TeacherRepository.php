<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class TeacherRepository extends BaseRepository implements TeacherRepositoryInterface
{
    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?Teacher
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByEmployeeId(string $employeeId): ?Teacher
    {
        return $this->model->where('employee_id', $employeeId)->first();
    }

    public function getAdvisoryClasses(): Collection
    {
        return $this->model->has('advisorySection')
            ->with('advisorySection')
            ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }
}
