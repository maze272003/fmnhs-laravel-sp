<?php

namespace App\Contracts\Services;

interface AssignmentServiceInterface
{
    public function createAssignment(array $data, int $teacherId): array;
    public function updateAssignment(int $assignmentId, array $data, int $teacherId): array;
    public function deleteAssignment(int $assignmentId, int $teacherId): bool;
    public function getAssignments(int $teacherId, ?int $subjectId = null, ?int $sectionId = null): array;
    public function getActiveAssignments(int $studentId): array;
    public function getAssignmentDetails(int $assignmentId, int $userId): array;
}
