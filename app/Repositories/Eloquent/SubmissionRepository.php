<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SubmissionRepositoryInterface;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Collection;

class SubmissionRepository extends BaseRepository implements SubmissionRepositoryInterface
{
    public function __construct(Submission $model)
    {
        parent::__construct($model);
    }

    public function findByStudentAndAssignment(int $studentId, int $assignmentId): ?Submission
    {
        return $this->model->where('student_id', $studentId)
            ->where('assignment_id', $assignmentId)
            ->first();
    }

    public function getByAssignment(int $assignmentId): Collection
    {
        return $this->model->where('assignment_id', $assignmentId)
            ->with('student')
            ->get();
    }

    public function getByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->with('assignment.subject')
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    public function markAsSubmitted(int $submissionId): bool
    {
        $submission = $this->findOrFail($submissionId);
        $submission->submitted_at = now();
        return $submission->save();
    }
}
