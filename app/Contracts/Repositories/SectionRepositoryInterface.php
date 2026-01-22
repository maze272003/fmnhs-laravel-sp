<?php

namespace App\Contracts\Repositories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;

interface SectionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByGradeLevel(int $gradeLevel): Collection;

    public function findByStrand(string $strand): Collection;

    public function getWithStudents(): Collection;

    public function getWithAdvisor(): Collection;

    public function search(string $query): Collection;
}
