<?php

namespace App\Repositories\Eloquent;

use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\Submission;
use App\Repositories\Contracts\AssignmentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AssignmentRepository implements AssignmentRepositoryInterface
{
    public function getTeacherClasses(int $teacherId): \Illuminate\Support\Collection
    {
        // Fetch Schedules with Subject, Section, AND School Year
        return \App\Models\Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section.schoolYear']) // <--- Added section.schoolYear
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section_id;
            });
    }

    public function getTeacherAssignments(int $teacherId, int $perPage = 10, ?string $search = null): LengthAwarePaginator
    {
        return Assignment::where('teacher_id', $teacherId)
            ->with(['subject', 'section.schoolYear', 'submissions.student'])
            
            // The 'Builder' type hint here caused the error because it wasn't imported
            ->when($search, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('subject', function ($q) use ($search) {
                          $q->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('section', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            })
            
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
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
            ->with(['submissions' => function($query) use ($studentId) {
                // Eager load only THIS student's submission
                $query->where('student_id', $studentId);
            }])
            ->orderBy('deadline', 'asc')
            ->get();
    }

    public function upsertSubmission(int $assignmentId, int $studentId, string $filePath): Submission
    {
        return Submission::updateOrCreate(
            [
                'assignment_id' => $assignmentId,
                'student_id'    => $studentId,
            ],
            [
                'file_path'    => $filePath, // This is now the S3 path
                'submitted_at' => now(),
                'status'       => 'submitted',
            ]
        );
    }
}
