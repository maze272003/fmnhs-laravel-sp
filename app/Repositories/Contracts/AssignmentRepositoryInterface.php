<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Assignment;
use App\Models\Submission;

interface AssignmentRepositoryInterface
{
    public function getTeacherClasses(int $teacherId): Collection;

    public function getTeacherAssignments(int $teacherId, int $perPage = 10, ?string $search = null): LengthAwarePaginator;

    public function findWithSubmissionsOrFail(int $id): Assignment;

    public function create(array $data): Assignment;

    // ERROR WAS HERE: Remove "int $teacherId" to match the Repository class
    public function getStudentAssignmentsWithOwnSubmission(int $sectionId, int $studentId): Collection;

    public function upsertSubmission(int $assignmentId, int $studentId, string $filePath): Submission;
}