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

    public function searchPaginate(string $query, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->where(function($q) use ($query) {
            $q->where('employee_id', 'like', "%{$query}%")
              ->orWhere('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%");
        })->with('advisorySection')->orderBy('last_name')->paginate($perPage);
    }

    public function getArchivedPaginate(int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->onlyTrashed()->with('advisorySection')->orderBy('last_name')->paginate($perPage);
    }

    public function searchArchivedPaginate(string $query, int $perPage = 10): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model->onlyTrashed()->where(function($q) use ($query) {
            $q->where('employee_id', 'like', "%{$query}%")
              ->orWhere('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%");
        })->with('advisorySection')->orderBy('last_name')->paginate($perPage);
    }

    public function restore(int $id): bool
    {
        return $this->model->onlyTrashed()->findOrFail($id)->restore();
    }
}
