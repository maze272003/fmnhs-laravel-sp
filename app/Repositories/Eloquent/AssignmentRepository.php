<?php

namespace App\Repositories\Eloquent;

use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\Submission;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Support\Collection;

class AssignmentRepository implements AssignmentRepositoryInterface
{
    public function getTeacherClasses(int $teacherId): Collection
    {
        return Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->unique(fn ($item) => $item->subject_id . '-' . $item->section_id)
            ->values();
    }

    public function getTeacherAssignments(int $teacherId): Collection
    {
        return Assignment::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->latest()
            ->get();
    }

    public function findWithSubmissionsOrFail(int $id): Assignment
    {
        return Assignment::with(['subject', 'section', 'submissions.student'])->findOrFail($id);
    }

    public function create(array $data): Assignment
    {
        return Assignment::create($data);
    }

    public function getStudentAssignmentsWithOwnSubmission(int $sectionId, int $studentId): Collection
    {
        return Assignment::where('section_id', $sectionId)
            ->with([
                'subject',
                'submissions' => fn ($q) => $q->where('student_id', $studentId),
            ])
            ->orderBy('deadline', 'asc')
            ->get();
    }

    public function upsertSubmission(int $assignmentId, int $studentId, string $filePath): Submission
    {
        return Submission::updateOrCreate(
            [
                'assignment_id' => $assignmentId,
                'student_id' => $studentId,
            ],
            [
                'file_path' => $filePath,
                'submitted_at' => now(),
            ]
        );
    }
}
