<?php

namespace App\Contracts\Services;

interface SubmissionServiceInterface
{
    public function submitAssignment(int $assignmentId, int $studentId, ?string $filePath = null, ?string $content = null): array;
    public function getSubmissions(int $assignmentId, int $teacherId): array;
    public function getStudentSubmissions(int $studentId): array;
    public function gradeSubmission(int $submissionId, float $grade, ?string $feedback = null, int $teacherId): array;
    public function getSubmissionDetails(int $submissionId, int $userId): array;
}
