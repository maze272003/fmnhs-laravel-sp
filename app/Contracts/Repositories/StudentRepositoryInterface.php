<?php

namespace App\Contracts\Repositories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByLRN(string $lrn): ?Student;

    public function findByEmail(string $email): ?Student;

    public function getBySection(int $sectionId): Collection;

    public function search(string $query): Collection;

    public function getGradeReport(int $studentId): Student;
}
