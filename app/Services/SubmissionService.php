<?php

namespace App\Services;

use App\Contracts\Repositories\SubmissionRepositoryInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Services\SubmissionServiceInterface;
use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SubmissionService extends BaseService implements SubmissionServiceInterface
{
    public function __construct(
        private SubmissionRepositoryInterface $submissionRepository,
        private AssignmentRepositoryInterface $assignmentRepository,
        private StudentRepositoryInterface $studentRepository
    ) {}

    public function submitAssignment(int $assignmentId, int $studentId, ?string $filePath = null, ?string $content = null): array
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $this->validateDeadline($assignment->deadline);

            $existingSubmission = $this->submissionRepository->findByStudentAndAssignment($studentId, $assignmentId);

            $submissionData = [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submitted_at' => Carbon::now()->toDateTimeString(),
            ];

            if ($filePath) {
                $submissionData['file_path'] = $filePath;
            }

            if ($content) {
                $submissionData['content'] = $content;
            }

            if ($existingSubmission) {
                if ($existingSubmission->file_path && $filePath) {
                    Storage::disk('s3')->delete($existingSubmission->file_path);
                }
                $submission = $this->submissionRepository->update($existingSubmission->id, $submissionData);
            } else {
                $submission = $this->submissionRepository->create($submissionData);
            }

            $this->submissionRepository->markAsSubmitted($submission->id);

            $this->logInfo("Assignment submitted", [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
            ]);

            return $submission->load('assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'submitAssignment');
        }
    }

    public function getSubmissions(int $assignmentId, int $teacherId): array
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            if ($assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to view submissions');
            }

            $submissions = $this->submissionRepository->getByAssignment($assignmentId);

            return $submissions->load('student')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getSubmissions');
        }
    }

    public function getStudentSubmissions(int $studentId): array
    {
        try {
            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $submissions = $this->submissionRepository->getByStudent($studentId);

            return $submissions->load('assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getStudentSubmissions');
        }
    }

    public function gradeSubmission(int $submissionId, float $grade, ?string $feedback = null, int $teacherId): array
    {
        try {
            $submission = $this->submissionRepository->find($submissionId);
            if (!$submission) {
                throw ServiceException::validationFailed('Submission not found');
            }

            $assignment = $this->assignmentRepository->find($submission->assignment_id);
            if (!$assignment || $assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to grade this submission');
            }

            $this->validateRange($grade, 0, 100, 'Grade');

            $updateData = ['grade' => $grade];

            if ($feedback !== null) {
                $updateData['feedback'] = $feedback;
            }

            $submission = $this->submissionRepository->update($submissionId, $updateData);

            $this->logInfo("Submission graded", [
                'submission_id' => $submissionId,
                'grade' => $grade,
            ]);

            return $submission->load('student', 'assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'gradeSubmission');
        }
    }

    public function getSubmissionDetails(int $submissionId, int $userId): array
    {
        try {
            $submission = $this->submissionRepository->find($submissionId);
            if (!$submission) {
                throw ServiceException::validationFailed('Submission not found');
            }

            $submission->load('student', 'assignment', 'assignment->subject');

            return $submission->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getSubmissionDetails');
        }
    }

    private function validateDeadline(string $deadline): void
    {
        $parsed = date_create_from_format('Y-m-d H:i:s', $deadline);
        if (!$parsed) {
            return;
        }

        $deadlineDate = $parsed->getTimestamp();
        $now = Carbon::now()->timestamp;

        if ($deadlineDate < $now) {
            $this->logWarning("Late submission detected", ['deadline' => $deadline]);
        }
    }
}

            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $this->validateDeadline($assignment->deadline);

            $existingSubmission = $this->submissionRepository->findByStudentAndAssignment($studentId, $assignmentId);

            $submissionData = [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
                'submitted_at' => now(),
            ];

            if ($filePath) {
                $submissionData['file_path'] = $filePath;
            }

            if ($content) {
                $submissionData['content'] = $content;
            }

            if ($existingSubmission) {
                if ($existingSubmission->file_path && $filePath) {
                    Storage::disk('s3')->delete($existingSubmission->file_path);
                }
                $submission = $this->submissionRepository->update($existingSubmission->id, $submissionData);
            } else {
                $submission = $this->submissionRepository->create($submissionData);
            }

            $this->submissionRepository->markAsSubmitted($submission->id);

            $this->logInfo("Assignment submitted", [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
            ]);

            return $submission->load('assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'submitAssignment');
        }
    }

    public function getSubmissions(int $assignmentId, int $teacherId): array
    {
        try {
            $assignment = $this->assignmentRepository->find($assignmentId);
            if (!$assignment) {
                throw ServiceException::validationFailed('Assignment not found');
            }

            if ($assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to view submissions');
            }

            $submissions = $this->submissionRepository->getByAssignment($assignmentId);

            return $submissions->load('student')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getSubmissions');
        }
    }

    public function getStudentSubmissions(int $studentId): array
    {
        try {
            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $submissions = $this->submissionRepository->getByStudent($studentId);

            return $submissions->load('assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getStudentSubmissions');
        }
    }

    public function gradeSubmission(int $submissionId, float $grade, ?string $feedback = null, int $teacherId): array
    {
        try {
            $submission = $this->submissionRepository->find($submissionId);
            if (!$submission) {
                throw ServiceException::validationFailed('Submission not found');
            }

            $assignment = $this->assignmentRepository->find($submission->assignment_id);
            if (!$assignment || $assignment->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to grade this submission');
            }

            $this->validateRange($grade, 0, 100, 'Grade');

            $updateData = ['grade' => $grade];

            if ($feedback !== null) {
                $updateData['feedback'] = $feedback;
            }

            $submission = $this->submissionRepository->update($submissionId, $updateData);

            $this->logInfo("Submission graded", [
                'submission_id' => $submissionId,
                'grade' => $grade,
            ]);

            return $submission->load('student', 'assignment')->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'gradeSubmission');
        }
    }

    public function getSubmissionDetails(int $submissionId, int $userId): array
    {
        try {
            $submission = $this->submissionRepository->find($submissionId);
            if (!$submission) {
                throw ServiceException::validationFailed('Submission not found');
            }

            $submission->load('student', 'assignment', 'assignment.subject');

            return $submission->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getSubmissionDetails');
        }
    }

    private function validateDeadline(string $deadline): void
    {
        $parsed = date_create_from_format('Y-m-d H:i:s', $deadline);
        if (!$parsed) {
            return;
        }

        $deadlineDate = $parsed->getTimestamp();
        $now = now()->getTimestamp();

        if ($deadlineDate < $now) {
            $this->logWarning("Late submission detected", ['deadline' => $deadline]);
        }
    }
}
