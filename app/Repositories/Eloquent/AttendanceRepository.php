<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function findByStudentAndDate(int $studentId, string $date): ?Attendance
    {
        return $this->model->where('student_id', $studentId)
            ->where('date', $date)
            ->first();
    }

    public function getAttendanceForClass(int $subjectId, int $sectionId, string $date): Collection
    {
        return $this->model->where('subject_id', $subjectId)
            ->where('section_id', $sectionId)
            ->where('date', $date)
            ->with('student')
            ->get();
    }

    public function getStudentAttendance(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)
            ->with('subject', 'teacher')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getAttendanceSummary(int $studentId, int $subjectId): array
    {
        $attendances = $this->model->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->get();

        return [
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'excused' => $attendances->where('status', 'excused')->count(),
            'total' => $attendances->count(),
        ];
    }

    public function markAttendance(array $data): bool
    {
        $success = true;
        foreach ($data['attendances'] as $studentId => $status) {
            try {
                $this->model->updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $data['subject_id'],
                        'date' => $data['date'],
                    ],
                    [
                        'teacher_id' => $data['teacher_id'],
                        'section_id' => $data['section_id'],
                        'status' => $status,
                    ]
                );
            } catch (\Exception $e) {
                $success = false;
            }
        }
        return $success;
    }
}
