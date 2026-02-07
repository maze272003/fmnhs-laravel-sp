<?php

namespace App\Services;

use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssignmentWorkflowService
{
    public function __construct(private readonly AssignmentRepositoryInterface $assignments)
    {
    }

    /* |--------------------------------------------------------------------------
    | TEACHER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get data for the main teacher assignment dashboard.
     */
    public function getTeacherAssignmentPageData(int $teacherId, ?string $search = null): array
    {
        return [
            'classes'     => $this->assignments->getTeacherClasses($teacherId),
            
            // Pass the search term to repository
            'assignments' => $this->assignments->getTeacherAssignments($teacherId, 5, $search), 
            'search'      => $search, // Pass back to view to keep input filled
        ];
    }

    /**
     * Get details for a specific assignment (Teacher View).
     */
    public function getAssignmentDetail(int $assignmentId)
    {
        return $this->assignments->findWithSubmissionsOrFail($assignmentId);
    }

    /**
     * Create a new assignment (Teacher).
     */
    public function createTeacherAssignment(int $teacherId, int $subjectId, int $sectionId, array $data, ?UploadedFile $attachment): void
    {
        // Store attachment if provided
        $filePath = null;
        if ($attachment) {
            $filePath = $this->uploadFile($attachment, 'assignments', 'teacher_' . $teacherId);
        }

        $this->assignments->create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'section_id'  => $sectionId,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'deadline'    => $data['deadline'],
            'file_path'   => $filePath, 
        ]);
    }

    /* |--------------------------------------------------------------------------
    | STUDENT METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get assignments for a student's specific section.
     */
    public function getStudentAssignments(int $sectionId, int $studentId)
    {
        return $this->assignments->getStudentAssignmentsWithOwnSubmission($sectionId, $studentId);
    }

    /**
     * Submit a student's work.
     */
    public function submitStudentWork(int $assignmentId, int $studentId, UploadedFile $file): void
    {
        // Upload file to S3
        $s3Path = $this->uploadFile(
            $file, 
            "submissions/assignment_{$assignmentId}/student_{$studentId}", 
            'submission'
        );

        // Update Database
        $this->assignments->upsertSubmission($assignmentId, $studentId, $s3Path);
    }

    /* |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Reusable S3 uploader.
     */
    private function uploadFile(UploadedFile $file, string $folder, string $prefix = ''): string
    {
        $extension = $file->getClientOriginalExtension();
        $cleanName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        
        // Final filename: prefix_timestamp_clean-name.ext
        $filename = ($prefix ? $prefix . '_' : '') . time() . '_' . $cleanName . '.' . $extension;
        $path = "{$folder}/{$filename}";

        // Upload to S3 with 'public' visibility
        Storage::disk('s3')->put($path, file_get_contents($file), 'public');

        return $path;
    }
}