<?php

namespace App\Repositories\Contracts;

use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SubjectRepositoryInterface
{
    public function paginateForAdmin(bool $archived, int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Subject;

    public function update(Subject $subject, array $data): Subject;

    public function archive(Subject $subject): void;

    public function restoreById(int $id): Subject;
}
