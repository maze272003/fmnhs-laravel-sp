<?php

namespace App\Repositories\Contracts;

use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Collection;

interface AssignmentRepositoryInterface
{
    public function getTeacherClasses(int $teacherId): Collection;

    public function getTeacherAssignments(int $teacherId): Collection;

    public function findWithSubmissionsOrFail(int $id): Assignment;

    public function create(array $data): Assignment;

    public function getStudentAssignmentsWithOwnSubmission(int $sectionId, int $studentId): Collection;

    public function upsertSubmission(int $assignmentId, int $studentId, string $filePath): Submission;
}
