<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Collection;

class AssignmentRepository extends BaseRepository implements AssignmentRepositoryInterface
{
    public function __construct(Assignment $model)
    {
        parent::__construct($model);
    }

    public function getByStudent(int $studentId): Collection
    {
        return $this->model->where('section_id', function($query) use ($studentId) {
            $query->whereHas('students', function($q) use ($studentId) {
                $q->where('id', $studentId);
            });
        })
        ->with(['subject', 'submissions' => function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        }])
        ->orderBy('deadline')
        ->get();
    }

    public function getByTeacher(int $teacherId): Collection
    {
        return $this->model->where('teacher_id', $teacherId)
            ->with('subject', 'section', 'submissions')
            ->orderBy('deadline', 'desc')
            ->get();
    }

    public function getBySubjectAndSection(int $subjectId, int $sectionId): Collection
    {
        return $this->model->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->with('submissions')
            ->orderBy('deadline')
            ->get();
    }

    public function getActiveAssignments(int $studentId): Collection
    {
        return $this->model->whereHas('section', function($query) use ($studentId) {
            $query->whereHas('students', function($q) use ($studentId) {
                $q->where('id', $studentId);
            });
        })
        ->where('deadline', '>=', now())
        ->with(['subject', 'submissions' => function($q) use ($studentId) {
            $q->where('student_id', $studentId);
        }])
        ->orderBy('deadline')
        ->get();
    }

    public function search(string $query): Collection
    {
        return $this->model->where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with('subject')
            ->get();
    }
}
