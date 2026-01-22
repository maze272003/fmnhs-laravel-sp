<?php

namespace App\Contracts\Repositories;

use App\Models\Submission;

interface SubmissionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Submission;

    public function getByAssignment(int $assignmentId): Collection;

    public function getByStudent(int $studentId): Collection;

    public function markAsSubmitted(int $submissionId): bool;
}
