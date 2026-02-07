<?php

namespace App\Services;

use App\Repositories\Contracts\ClassroomAttendanceRepositoryInterface;

class TeacherAttendanceService
{
    public function __construct(private readonly ClassroomAttendanceRepositoryInterface $attendance)
    {
    }

    public function getAssignedClasses(int $teacherId)
    {
        return $this->attendance->getAssignedClassesForTeacher($teacherId);
    }

    public function getAttendanceSheet(int $subjectId, int $sectionId, string $date): array
    {
        return [
            'subjectId' => $subjectId,
            'section' => $this->attendance->findSectionOrFail($sectionId),
            'students' => $this->attendance->getStudentsBySection($sectionId),
            'date' => $date,
            'attendances' => $this->attendance->getAttendanceByClassDate($subjectId, $sectionId, $date),
        ];
    }

    public function saveAttendance(
        int $teacherId,
        int $subjectId,
        int $sectionId,
        string $date,
        array $statuses
    ): void {
        foreach ($statuses as $studentId => $status) {
            $this->attendance->upsertAttendance(
                (int) $studentId,
                $subjectId,
                $date,
                [
                    'teacher_id' => $teacherId,
                    'section_id' => $sectionId,
                    'status' => $status,
                ]
            );
        }
    }
}
