<?php

namespace App\Services;

use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Http\UploadedFile;

class AssignmentWorkflowService
{
    public function __construct(private readonly AssignmentRepositoryInterface $assignments)
    {
    }

    public function getTeacherAssignmentPageData(int $teacherId): array
    {
        return [
            'classes' => $this->assignments->getTeacherClasses($teacherId),
            'assignments' => $this->assignments->getTeacherAssignments($teacherId),
        ];
    }

    public function getAssignmentDetail(int $assignmentId)
    {
        return $this->assignments->findWithSubmissionsOrFail($assignmentId);
    }

    public function createTeacherAssignment(
        int $teacherId,
        int $subjectId,
        int $sectionId,
        array $data,
        ?UploadedFile $attachment
    ): void {
        $filename = $this->storeAttachment($attachment, 'uploads/assignments');

        $this->assignments->create([
            'teacher_id' => $teacherId,
            'subject_id' => $subjectId,
            'section_id' => $sectionId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline' => $data['deadline'],
            'file_path' => $filename,
        ]);
    }

    public function getStudentAssignments(int $sectionId, int $studentId)
    {
        return $this->assignments->getStudentAssignmentsWithOwnSubmission($sectionId, $studentId);
    }

    public function submitStudentWork(int $assignmentId, int $studentId, UploadedFile $attachment): void
    {
        $filename = $this->storeAttachment($attachment, 'uploads/submissions', 'stud' . $studentId);
        $this->assignments->upsertSubmission($assignmentId, $studentId, $filename ?? '');
    }

    private function storeAttachment(?UploadedFile $file, string $directory, ?string $prefix = null): ?string
    {
        if (!$file) {
            return null;
        }

        $prefixPart = $prefix ? $prefix . '_' : '';
        $filename = time() . '_' . $prefixPart . $file->getClientOriginalName();
        $file->move(public_path($directory), $filename);

        return $filename;
    }
}
