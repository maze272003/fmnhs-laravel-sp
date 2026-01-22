<?php

namespace App\Services;

use App\Contracts\Services\AttendanceServiceInterface;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Support\Exceptions\ServiceException;
use Illuminate\Support\Facades\Log;

class AttendanceService implements AttendanceServiceInterface
{
    protected AttendanceRepositoryInterface $attendanceRepository;
    protected StudentRepositoryInterface $studentRepository;
    protected SectionRepositoryInterface $sectionRepository;
    protected SubjectRepositoryInterface $subjectRepository;

    public function __construct(
        AttendanceRepositoryInterface $attendanceRepository,
        StudentRepositoryInterface $studentRepository,
        SectionRepositoryInterface $sectionRepository,
        SubjectRepositoryInterface $subjectRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
        $this->studentRepository = $studentRepository;
        $this->sectionRepository = $sectionRepository;
        $this->subjectRepository = $subjectRepository;
    }

    public function markAttendance(int $sectionId, int $subjectId, string $date, array $attendanceData, int $teacherId): array
    {
        try {
            $section = $this->sectionRepository->findOrFail($sectionId);
            $subject = $this->subjectRepository->findOrFail($subjectId);

            $students = $this->studentRepository->getBySection($sectionId);

            if ($students->isEmpty()) {
                throw ServiceException::validationFailed('No students found in this section');
            }

            $results = [];
            foreach ($students as $student) {
                $status = $attendanceData[$student->id] ?? 'absent';

                $attendance = $this->attendanceRepository->markAttendance([
                    'attendances' => [$student->id => $status],
                    'subject_id' => $subjectId,
                    'date' => $date,
                    'teacher_id' => $teacherId,
                    'section_id' => $sectionId,
                ]);

                $results[$student->id] = [
                    'student' => $student->full_name ?? $student->first_name . ' ' . $student->last_name,
                    'status' => $status,
                    'success' => $attendance,
                ];
            }

            Log::info('Attendance marked successfully', [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'date' => $date,
                'teacher_id' => $teacherId,
            ]);

            return [
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'results' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to mark attendance', [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('mark attendance', $e->getMessage());
        }
    }

    public function getAttendanceForClass(int $sectionId, int $subjectId, string $date): array
    {
        try {
            $section = $this->sectionRepository->findOrFail($sectionId);
            $subject = $this->subjectRepository->findOrFail($subjectId);

            $students = $this->studentRepository->getBySection($sectionId);
            $attendances = $this->attendanceRepository->getAttendanceForClass($subjectId, $sectionId, $date);

            $attendanceByStudent = $attendances->keyBy('student_id');

            $results = [];
            foreach ($students as $student) {
                $attendance = $attendanceByStudent->get($student->id);
                $results[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'lrn' => $student->lrn,
                    'status' => $attendance ? $attendance->status : null,
                    'attendance_id' => $attendance ? $attendance->id : null,
                ];
            }

            return [
                'section' => $section->name,
                'subject' => $subject->name,
                'date' => $date,
                'students' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get attendance for class', [
                'section_id' => $sectionId,
                'subject_id' => $subjectId,
                'date' => $date,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get attendance for class', $e->getMessage());
        }
    }

    public function getStudentAttendance(int $studentId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $student = $this->studentRepository->findOrFail($studentId);

            $attendances = $this->attendanceRepository->getStudentAttendance($studentId);

            if ($startDate) {
                $attendances = $attendances->where('date', '>=', $startDate);
            }
            if ($endDate) {
                $attendances = $attendances->where('date', '<=', $endDate);
            }

            $summary = [
                'present' => $attendances->where('status', 'present')->count(),
                'absent' => $attendances->where('status', 'absent')->count(),
                'late' => $attendances->where('status', 'late')->count(),
                'excused' => $attendances->where('status', 'excused')->count(),
                'total' => $attendances->count(),
            ];

            $attendanceRate = $summary['total'] > 0
                ? round(($summary['present'] / $summary['total']) * 100, 2)
                : 0;

            return [
                'student' => $student->first_name . ' ' . $student->last_name,
                'attendances' => $attendances->map(function ($attendance) {
                    return [
                        'date' => $attendance->date,
                        'subject' => $attendance->subject->name ?? 'Unknown',
                        'status' => $attendance->status,
                    ];
                })->values(),
                'summary' => $summary,
                'attendance_rate' => $attendanceRate,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get student attendance', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get student attendance', $e->getMessage());
        }
    }

    public function getAttendanceSummary(int $sectionId, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $section = $this->sectionRepository->findOrFail($sectionId);
            $students = $this->studentRepository->getBySection($sectionId);

            $summary = [];
            foreach ($students as $student) {
                $attendances = $this->attendanceRepository->getStudentAttendance($student->id);

                if ($startDate) {
                    $attendances = $attendances->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $attendances = $attendances->where('date', '<=', $endDate);
                }

                $summary[] = [
                    'student_id' => $student->id,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'lrn' => $student->lrn,
                    'present' => $attendances->where('status', 'present')->count(),
                    'absent' => $attendances->where('status', 'absent')->count(),
                    'late' => $attendances->where('status', 'late')->count(),
                    'excused' => $attendances->where('status', 'excused')->count(),
                    'total' => $attendances->count(),
                ];
            }

            return [
                'section' => $section->name,
                'summary' => $summary,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get attendance summary', [
                'section_id' => $sectionId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get attendance summary', $e->getMessage());
        }
    }

    public function getAttendanceByDate(string $date, ?int $sectionId = null): array
    {
        try {
            $query = $this->attendanceRepository;

            $results = [];
            $attendances = $query->where('date', $date)
                ->with(['student', 'subject', 'section'])
                ->get();

            if ($sectionId) {
                $attendances = $attendances->where('section_id', $sectionId);
            }

            $groupedBySection = $attendances->groupBy('section_id');

            foreach ($groupedBySection as $sectionId => $sectionAttendances) {
                $section = $sectionAttendances->first()->section;
                $results[] = [
                    'section_id' => $sectionId,
                    'section_name' => $section->name ?? 'Unknown',
                    'attendances' => $sectionAttendances->map(function ($attendance) {
                        return [
                            'student_name' => $attendance->student->first_name . ' ' . $attendance->student->last_name,
                            'subject' => $attendance->subject->name ?? 'Unknown',
                            'status' => $attendance->status,
                        ];
                    }),
                ];
            }

            return [
                'date' => $date,
                'sections' => $results,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get attendance by date', [
                'date' => $date,
                'section_id' => $sectionId,
                'error' => $e->getMessage(),
            ]);
            throw ServiceException::operationFailed('get attendance by date', $e->getMessage());
        }
    }
}
