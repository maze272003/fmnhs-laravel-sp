<?php

namespace App\Services;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Contracts\Services\GradeServiceInterface;
use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Collection;

class GradeService extends BaseService implements GradeServiceInterface
{
    public function __construct(
        private GradeRepositoryInterface $gradeRepository,
        private StudentRepositoryInterface $studentRepository,
        private SubjectRepositoryInterface $subjectRepository
    ) {}

    public function recordGrade(int $studentId, int $subjectId, string $quarter, float $value, int $teacherId): array
    {
        try {
            $this->validateRange($value, 0, 100, 'Grade');
            $this->validateQuarter($quarter);

            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $subject = $this->subjectRepository->find($subjectId);
            if (!$subject) {
                throw ServiceException::validationFailed('Subject not found');
            }

            $grade = $this->gradeRepository->updateOrCreateGrade(
                $studentId,
                $subjectId,
                $quarter,
                $value,
                $teacherId
            );

            $this->logInfo("Grade recorded", [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'quarter' => $quarter,
                'value' => $value,
                'teacher_id' => $teacherId,
            ]);

            return $grade->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'recordGrade');
        }
    }

    public function updateGrade(int $gradeId, float $value, int $teacherId): array
    {
        try {
            $this->validateRange($value, 0, 100, 'Grade');

            $grade = $this->gradeRepository->find($gradeId);
            if (!$grade) {
                throw ServiceException::validationFailed('Grade not found');
            }

            if ($grade->teacher_id !== $teacherId) {
                throw ServiceException::authorizationFailed('Unauthorized to update this grade');
            }

            $grade = $this->gradeRepository->update($gradeId, ['grade_value' => $value]);

            $this->logInfo("Grade updated", ['grade_id' => $gradeId, 'value' => $value]);

            return $grade->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'updateGrade');
        }
    }

    public function getStudentGrades(int $studentId, ?string $quarter = null): array
    {
        try {
            if ($quarter) {
                $this->validateQuarter($quarter);
                $grades = $this->gradeRepository->findByStudentAndQuarter($studentId, $quarter);
            } else {
                $grades = $this->gradeRepository->where('student_id', $studentId)
                    ->with('subject')
                    ->all();
            }

            return $grades->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getStudentGrades');
        }
    }

    public function getClassGrades(int $subjectId, int $sectionId, string $quarter): array
    {
        try {
            $this->validateQuarter($quarter);

            $grades = $this->gradeRepository->getGradesForClass($subjectId, $sectionId, $quarter);

            return $grades->toArray();
        } catch (\Exception $e) {
            $this->handleException($e, 'getClassGrades');
        }
    }

    public function calculateAverage(int $studentId, ?string $quarter = null): float
    {
        try {
            if ($quarter) {
                $this->validateQuarter($quarter);
                $average = $this->gradeRepository->getAverage($studentId, $quarter);
            } else {
                $average = $this->gradeRepository->where('student_id', $studentId)
                    ->all()
                    ->avg('grade_value') ?? 0.0;
            }

            return round($average, 2);
        } catch (\Exception $e) {
            $this->handleException($e, 'calculateAverage');
        }
    }

    public function generateReportCard(int $studentId, string $schoolYear): array
    {
        try {
            $student = $this->studentRepository->find($studentId);
            if (!$student) {
                throw ServiceException::validationFailed('Student not found');
            }

            $gradeReport = $this->gradeRepository->getGradeReport($studentId);

            return [
                'student' => $student->toArray(),
                'grades' => $gradeReport,
                'school_year' => $schoolYear,
                'generated_at' => now()->toDateTimeString(),
            ];
        } catch (\Exception $e) {
            $this->handleException($e, 'generateReportCard');
        }
    }

    private function validateQuarter(string $quarter): void
    {
        $validQuarters = ['Q1', 'Q2', 'Q3', 'Q4'];
        if (!in_array($quarter, $validQuarters)) {
            throw ServiceException::validationFailed("Invalid quarter. Must be one of: " . implode(', ', $validQuarters));
        }
    }
}
