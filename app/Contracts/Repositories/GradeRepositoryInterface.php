<?php

namespace App\Contracts\Repositories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

interface GradeRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStudentAndSubject(int $studentId, int $subjectId): Collection;

    public function findByStudentAndQuarter(int $studentId, int $quarter): Collection;

    public function getGradesForClass(int $subjectId, int $sectionId): Collection;

    public function updateOrCreateGrade(array $data): Grade;

    public function getAverage(int $studentId, int $subjectId): float;
}
