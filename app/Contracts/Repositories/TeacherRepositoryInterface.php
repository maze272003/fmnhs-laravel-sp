<?php

namespace App\Contracts\Repositories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

interface TeacherRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?Teacher;

    public function findByEmployeeId(string $employeeId): ?Teacher;

    public function getAdvisoryClasses(): Collection;

    public function search(string $query): Collection;
}
