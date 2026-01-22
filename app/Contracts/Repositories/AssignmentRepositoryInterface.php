<?php

namespace App\Contracts\Repositories;

use App\Models\Assignment;
use Illuminate\Database\Eloquent\Collection;

interface AssignmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getByStudent(int $studentId): Collection;

    public function getByTeacher(int $teacherId): Collection;

    public function getBySubjectAndSection(int $subjectId, int $sectionId): Collection;

    public function getActiveAssignments(int $studentId): Collection;

    public function search(string $query): Collection;

    public function getBySectionWithSubmissions(int $sectionId, int $studentId): Collection;
}
