<?php

namespace App\Services;

use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Support\Exceptions\ServiceException;

class DashboardService extends BaseService implements DashboardServiceInterface
{
    public function __construct(
        protected TeacherRepositoryInterface $teacherRepository,
        protected StudentRepositoryInterface $studentRepository,
        protected GradeRepositoryInterface $gradeRepository,
        protected AttendanceRepositoryInterface $attendanceRepository,
        protected AnnouncementRepositoryInterface $announcementRepository,
        protected AssignmentRepositoryInterface $assignmentRepository
    ) {}

    public function getTeacherDashboard(int $teacherId): array
    {
        try {
            $teacher = $this->teacherRepository->find($teacherId);

            if (!$teacher) {
                throw ServiceException::modelNotFound('Teacher not found');
            }

            $advisoryClass = $teacher->advisorySection;

            $assignments = $this->assignmentRepository
                ->where('teacher_id', $teacherId)
                ->orderBy('deadline', 'desc')
                ->limit(10)
                ->all();

            $recentGrades = $this->gradeRepository
                ->with(['student'])
                ->where('teacher_id', $teacherId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->all();

            $announcements = $this->announcementRepository
                ->where('role', 'teacher')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->all();

            return [
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->first_name . ' ' . $teacher->last_name,
                    'email' => $teacher->email,
                ],
                'advisory_class' => $advisoryClass ? [
                    'id' => $advisoryClass->id,
                    'name' => $advisoryClass->name,
                    'grade_level' => $advisoryClass->grade_level,
                    'student_count' => $this->studentRepository->where('section_id', $advisoryClass->id)->all()->count(),
                ] : null,
                'statistics' => [
                    'total_students' => $this->gradeRepository
                        ->where('teacher_id', $teacherId)
                        ->distinct('student_id')
                        ->all()
                        ->count(),
                    'active_assignments' => $assignments->count(),
                    'total_assignments' => $this->assignmentRepository
                        ->where('teacher_id', $teacherId)
                        ->all()
                        ->count(),
                ],
                'recent_assignments' => $assignments->toArray(),
                'recent_grades' => $recentGrades->toArray(),
                'recent_announcements' => $announcements->toArray(),
            ];
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Teacher dashboard retrieval failed');
            throw ServiceException::operationFailed('Failed to retrieve teacher dashboard');
        }
    }

    public function getStudentDashboard(int $studentId): array
    {
        try {
            $student = $this->studentRepository
                ->with(['section'])
                ->find($studentId);

            if (!$student) {
                throw ServiceException::modelNotFound('Student not found');
            }

            $assignments = $this->assignmentRepository->getActiveAssignments($studentId);

            $grades = $this->gradeRepository
                ->with(['subject'])
                ->where('student_id', $studentId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->all();

            $recentAttendance = $this->attendanceRepository
                ->with(['subject'])
                ->where('student_id', $studentId)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->all();

            $announcements = $this->announcementRepository
                ->where('role', 'student')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->all();

            $allGrades = $this->gradeRepository
                ->where('student_id', $studentId)
                ->all();

            $overallAverage = $allGrades->count() > 0
                ? round($allGrades->sum('grade_value') / $allGrades->count(), 2)
                : 0;

            return [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'email' => $student->email,
                    'section' => $student->section ? $student->section->toArray() : null,
                ],
                'statistics' => [
                    'overall_average' => $overallAverage,
                    'pending_assignments' => $assignments->count(),
                    'total_assignments' => $assignments->count(),
                    'attendance_rate' => $this->calculateAttendanceRate($studentId),
                ],
                'pending_assignments' => $assignments->toArray(),
                'recent_grades' => $grades->toArray(),
                'recent_attendance' => $recentAttendance->toArray(),
                'recent_announcements' => $announcements->toArray(),
            ];
        } catch (ServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            $this->handleException($e, 'Student dashboard retrieval failed');
            throw ServiceException::operationFailed('Failed to retrieve student dashboard');
        }
    }

    public function getAdminDashboard(): array
    {
        try {
            $totalStudents = $this->studentRepository->all()->count();
            $totalTeachers = $this->teacherRepository->all()->count();
            $totalGrades = $this->gradeRepository->all()->count();
            $totalAssignments = $this->assignmentRepository->all()->count();

            $recentStudents = $this->studentRepository
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->all();

            $recentAnnouncements = $this->announcementRepository
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->all();

            return [
                'statistics' => [
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_grades' => $totalGrades,
                    'total_assignments' => $totalAssignments,
                ],
                'recent_students' => $recentStudents->toArray(),
                'recent_announcements' => $recentAnnouncements->toArray(),
            ];
        } catch (Exception $e) {
            $this->handleException($e, 'Admin dashboard retrieval failed');
            throw ServiceException::operationFailed('Failed to retrieve admin dashboard');
        }
    }

    protected function calculateAttendanceRate(int $studentId): float
    {
        try {
            $attendanceRecords = $this->attendanceRepository
                ->where('student_id', $studentId)
                ->all();

            if ($attendanceRecords->isEmpty()) {
                return 0.0;
            }

            $presentCount = $attendanceRecords
                ->whereIn('status', ['Present', 'Late', 'Excused'])
                ->count();

            return round(($presentCount / $attendanceRecords->count()) * 100, 2);
        } catch (Exception $e) {
            $this->handleException($e, 'Attendance rate calculation failed');
            return 0.0;
        }
    }
}
