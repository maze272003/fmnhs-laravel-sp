<?php

namespace App\Services;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Services\ReportServiceInterface;
use App\Support\Exceptions\ServiceException;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportService extends BaseService implements ReportServiceInterface
{
    public function __construct(
        protected GradeRepositoryInterface $gradeRepository,
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected StudentRepositoryInterface $studentRepository
    ) {}

    public function generateReportCard(int $studentId, string $schoolYear): string
    {
        try {
            $student = $this->studentRepository
                ->with(['section', 'grades.subject', 'grades.teacher'])
                ->find($studentId);

            if (!$student) {
                throw ServiceException::modelNotFound('Student not found');
            }

            $grades = $student->grades;
            $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];

            $reportData = [
                'student' => $student,
                'school_year' => $schoolYear,
                'quarters' => [],
                'overall_average' => 0,
            ];

            $totalGrades = 0;
            $gradeCount = 0;

            foreach ($quarters as $quarter) {
                $quarterGrades = $grades->where('quarter', $quarter);
                $quarterAverage = $quarterGrades->count() > 0
                    ? round($quarterGrades->sum('grade_value') / $quarterGrades->count(), 2)
                    : 0;

                $reportData['quarters'][$quarter] = [
                    'grades' => $quarterGrades->values(),
                    'average' => $quarterAverage,
                ];

                $totalGrades += array_sum($quarterGrades->pluck('grade_value')->toArray());
                $gradeCount += $quarterGrades->count();
            }

            if ($gradeCount > 0) {
                $reportData['overall_average'] = round($totalGrades / $gradeCount, 2);
            }

            $pdf = PDF::loadView('reports.report-card', $reportData);

            $this->logInfo('Report card generated', [
                'student_id' => $studentId,
                'school_year' => $schoolYear,
            ]);

            return $pdf->output();
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Report card generation failed');
            throw ServiceException::operationFailed('Failed to generate report card');
        }
    }

    public function generateAttendanceReport(int $sectionId, ?string $startDate = null, ?string $endDate = null): string
    {
        try {
            $attendance = $this->attendanceRepository
                ->with(['student', 'subject'])
                ->where('section_id', $sectionId);

            if ($startDate) {
                $attendance->where('date', '>=', $startDate);
            }

            if ($endDate) {
                $attendance->where('date', '<=', $endDate);
            }

            $attendanceRecords = $attendance->orderBy('date')->all();

            $summary = [
                'section_id' => $sectionId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'records' => $attendanceRecords,
                'summary' => [
                    'total_records' => $attendanceRecords->count(),
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                    'excused' => 0,
                ],
            ];

            foreach ($attendanceRecords as $record) {
                $status = $record->status;
                if (isset($summary['summary'][$status])) {
                    $summary['summary'][$status]++;
                }
            }

            $pdf = PDF::loadView('reports.attendance', $summary);

            $this->logInfo('Attendance report generated', [
                'section_id' => $sectionId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            return $pdf->output();
        } catch (Exception $e) {
            $this->handleException($e, 'Attendance report generation failed');
            throw ServiceException::operationFailed('Failed to generate attendance report');
        }
    }

    public function generateGradeReport(int $sectionId, int $subjectId, string $quarter): string
    {
        try {
            $grades = $this->gradeRepository
                ->with(['student.section', 'teacher'])
                ->whereHas('student', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                })
                ->where('subject_id', $subjectId)
                ->where('quarter', $quarter)
                ->orderBy('student_id')
                ->all();

            $reportData = [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'quarter' => $quarter,
                'grades' => $grades,
                'statistics' => [
                    'total_students' => $grades->count(),
                    'average' => $grades->count() > 0 ? round($grades->sum('grade_value') / $grades->count(), 2) : 0,
                    'highest' => $grades->max('grade_value') ?? 0,
                    'lowest' => $grades->min('grade_value') ?? 0,
                ],
            ];

            $pdf = PDF::loadView('reports.grade', $reportData);

            $this->logInfo('Grade report generated', [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'quarter' => $quarter,
            ]);

            return $pdf->output();
        } catch (Exception $e) {
            $this->handleException($e, 'Grade report generation failed');
            throw ServiceException::operationFailed('Failed to generate grade report');
        }
    }

    public function getClassSummary(int $sectionId, string $quarter): array
    {
        try {
            $grades = $this->gradeRepository
                ->with(['student', 'subject'])
                ->whereHas('student', function ($query) use ($sectionId) {
                    $query->where('section_id', $sectionId);
                })
                ->where('quarter', $quarter)
                ->all();

            $summary = [
                'section_id' => $sectionId,
                'quarter' => $quarter,
                'total_grades' => $grades->count(),
                'subjects' => [],
            ];

            foreach ($grades as $grade) {
                $subjectId = $grade->subject_id;
                if (!isset($summary['subjects'][$subjectId])) {
                    $summary['subjects'][$subjectId] = [
                        'subject_name' => $grade->subject->name,
                        'grades' => [],
                        'average' => 0,
                        'highest' => 0,
                        'lowest' => 0,
                    ];
                }

                $summary['subjects'][$subjectId]['grades'][] = $grade->grade_value;
            }

            foreach ($summary['subjects'] as &$subject) {
                if (count($subject['grades']) > 0) {
                    $subject['average'] = round(array_sum($subject['grades']) / count($subject['grades']), 2);
                    $subject['highest'] = max($subject['grades']);
                    $subject['lowest'] = min($subject['grades']);
                }
            }

            return $summary;
        } catch (Exception $e) {
            $this->handleException($e, 'Class summary generation failed');
            throw ServiceException::operationFailed('Failed to generate class summary');
        }
    }

    public function getStudentPerformance(int $studentId, string $schoolYear): array
    {
        try {
            $student = $this->studentRepository
                ->with(['section', 'grades.subject'])
                ->find($studentId);

            if (!$student) {
                throw ServiceException::modelNotFound('Student not found');
            }

            $grades = $student->grades;
            $quarters = ['Q1', 'Q2', 'Q3', 'Q4'];

            $performance = [
                'student' => $student->toArray(),
                'school_year' => $schoolYear,
                'quarterly_performance' => [],
                'subject_performance' => [],
                'overall_average' => 0,
            ];

            $totalGrades = 0;
            $gradeCount = 0;

            foreach ($quarters as $quarter) {
                $quarterGrades = $grades->where('quarter', $quarter);
                $performance['quarterly_performance'][$quarter] = [
                    'average' => $quarterGrades->count() > 0
                        ? round($quarterGrades->sum('grade_value') / $quarterGrades->count(), 2)
                        : 0,
                    'subject_count' => $quarterGrades->count(),
                ];

                $totalGrades += array_sum($quarterGrades->pluck('grade_value')->toArray());
                $gradeCount += $quarterGrades->count();
            }

            if ($gradeCount > 0) {
                $performance['overall_average'] = round($totalGrades / $gradeCount, 2);
            }

            foreach ($grades as $grade) {
                $subjectId = $grade->subject_id;
                if (!isset($performance['subject_performance'][$subjectId])) {
                    $performance['subject_performance'][$subjectId] = [
                        'subject_name' => $grade->subject->name,
                        'grades' => [],
                        'average' => 0,
                    ];
                }
                $performance['subject_performance'][$subjectId]['grades'][] = $grade->grade_value;
            }

            foreach ($performance['subject_performance'] as &$subject) {
                if (count($subject['grades']) > 0) {
                    $subject['average'] = round(array_sum($subject['grades']) / count($subject['grades']), 2);
                }
            }

            return $performance;
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Student performance retrieval failed');
            throw ServiceException::operationFailed('Failed to retrieve student performance');
        }
    }
}
