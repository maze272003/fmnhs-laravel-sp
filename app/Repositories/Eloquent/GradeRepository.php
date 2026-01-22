<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Collection;

class GradeRepository extends BaseRepository implements GradeRepositoryInterface
{
    public function __construct(Grade $model)
    {
        parent::__construct($model);
    }

    public function findByStudentAndSubject(int $studentId, int $subjectId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->with('subject')
            ->get();
    }

    public function findByStudentAndQuarter(int $studentId, int $quarter): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->where('quarter', $quarter)
            ->with('subject', 'student')
            ->get();
    }

    public function getGradesForClass(int $subjectId, int $sectionId): Collection
    {
        return $this->model->where('subject_id', $subjectId)
            ->with(['student' => function($query) {
                $query->where('section_id', request('section_id'))->orderBy('last_name');
            }])
            ->orderBy('student.last_name')
            ->get();
    }

    public function updateOrCreateGrade(array $data): Grade
    {
        return $this->model->updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'],
                'quarter' => $data['quarter'],
            ],
            [
                'teacher_id' => $data['teacher_id'],
                'grade_value' => $data['grade_value'],
            ]
        );
    }

    public function getAverage(int $studentId, int $subjectId): float
    {
        return (float) $this->model->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->avg('grade_value') ?? 0;
    }
}
