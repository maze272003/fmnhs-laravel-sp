<?php

namespace App\Services;

use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Services\AssignmentServiceInterface;
use App\Support\Exceptions\ServiceException;
use Illuminate\Http\UploadedFile;

class AssignmentService extends BaseService implements AssignmentServiceInterface
{
    public function __construct(
        private AssignmentRepositoryInterface $assignmentRepository,
        private TeacherRepositoryInterface $teacherRepository,
        private SubjectRepositoryInterface $subjectRepository,
        private SectionRepositoryInterface $sectionRepository
    ) {}

    public function createAssignment(array $data, int $teacherId): array
    {
        try {
            $teacher = $this->teacherRepository->find($teacherId);
            if (!$teacher) {
                throw ServiceException::validationFailed('Teacher not found');
            }

            $this->validateRequired($data, ['subject_id', 'section_id', 'title', 'description', 'deadline']);

            $subject = $this->subjectRepository->find($data['subject_id']);
            if (!$subject) {
                throw ServiceException::validationFailed('Subject not found');
            }

            $section = $this->sectionRepository->find($data['section_id']);
            if (!$section) {
                throw ServiceException::validationFailed('Section not found');
            }

            $this->validateDeadline($data['deadline']);

            $assignmentData = array_merge($data, [
                'teacher_id' => $teacherId,
            ]);

            $assignment = $this->assignmentRepository->create($assignmentData);

            $this->logInfo("Assignment created", [
                'assignment_id' => $assignment->id,
                'teacher_id' => $teacherId,
                'title' => $data['title'],
            ]);

            return $assignment->load('subject', 'section', 'teacher')->toArray();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateAssignment(int $assignmentId, array $data, int $teacherId): array
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            if ($assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to update this assignment');
            }

            if (isset($data['deadline'])) {
                $this->validateDeadline($data['deadline']);
            }

            $assignment = $this->assignmentRepository->update($assignmentId, $data);

            $this->logInfo("Assignment updated", ['assignment_id' => $assignmentId]);

            return $assignment->load('subject', 'section')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'updateAssignment');
        }
    }

    public function deleteAssignment(int $assignmentId, int $teacherId): bool
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            if ($assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to delete this assignment');
            }

            $this->assignmentRepository->delete($assignmentId);

            $this->logInfo("Assignment deleted", ['assignment_id' => $assignmentId]);

            return true;
        } catch (\Exception $e) {
            $this->handleException($e, 'deleteAssignment');
        }
    }

    public function getAssignments(int $teacherId, ?int $subjectId = null, ?int $sectionId = null): array
    {
        try {
            $query = $this->assignmentRepository->getByTeacher($teacherId);

            if ($subjectId) {
                $query = $query->where('subject_id', $subjectId);
            }

            if ($sectionId) {
                $query = $query->where('section_id', $sectionId);
            }

            return $query->load('subject', 'section')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getAssignments');
        }
    }

    public function getActiveAssignments(int $studentId): array
    {
        try {
            $assignments = $this->assignmentRepository->getActiveAssignments($studentId);

            return $assignments->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getActiveAssignments');
        }
    }

    public function getAssignmentDetails(int $assignmentId, int $userId): array
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            $assignment->load('subject', 'section', 'teacher', 'submissions');

            return $assignment->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getAssignmentDetails');
        }
    }

    private function validateDeadline(string $deadline): void
    {
        $parsed = date_create_from_format('Y-m-d H:i:s', $deadline);
        if (!$parsed) {
            throw ServiceException::validationFailed('Invalid deadline format. Use Y-m-d H:i:s format.');
        }

        $deadlineDate = $parsed->getTimestamp();
        $now = now()->getTimestamp();

        if ($deadlineDate < $now) {
            throw ServiceException::validationFailed('Deadline must be in the future');
        }
    }
}
