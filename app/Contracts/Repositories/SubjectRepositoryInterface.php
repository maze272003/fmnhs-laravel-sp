<?php

namespace App\Contracts\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code): ?Subject;

    public function search(string $query): Collection;

    public function getWithGrades(): Collection;

    public function getActive(): Collection;
}
