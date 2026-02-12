<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ConferenceParticipant;
use App\Models\Student;
use App\Models\VideoConference;
use Illuminate\Support\Facades\DB;

class AutoAttendanceService
{
    public function __construct(
        private readonly GamificationService $gamificationService
    ) {}

    /**
     * Mark attendance automatically when a student joins a conference.
     */
    public function markAttendanceOnJoin(VideoConference $conference, Student $student): ?Attendance
    {
        if ($this->isAlreadyMarked($conference, $student)) {
            return null;
        }

        $attendance = Attendance::create([
            'student_id' => $student->id,
            'subject_id' => $conference->section?->schedules()->first()?->subject_id,
            'teacher_id' => $conference->teacher_id,
            'section_id' => $conference->section_id,
            'date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $this->gamificationService->awardAttendancePoints($student, $attendance->id);

        return $attendance;
    }

    /**
     * Check if attendance is already marked for this conference today.
     */
    public function isAlreadyMarked(VideoConference $conference, Student $student): bool
    {
        return Attendance::where('student_id', $student->id)
            ->where('section_id', $conference->section_id)
            ->where('date', now()->toDateString())
            ->exists();
    }

    /**
     * Get auto-attendance statistics for a conference.
     */
    public function getAutoAttendanceStats(VideoConference $conference): array
    {
        $totalStudents = $conference->section?->students()->count() ?? 0;

        $presentCount = Attendance::where('section_id', $conference->section_id)
            ->where('date', now()->toDateString())
            ->where('status', 'present')
            ->count();

        $participants = ConferenceParticipant::where('conference_id', $conference->id)
            ->where('actor_type', 'student')
            ->get();

        $averageDuration = $participants->avg('duration_seconds') ?? 0;

        return [
            'total_students' => $totalStudents,
            'present' => $presentCount,
            'absent' => max(0, $totalStudents - $presentCount),
            'attendance_rate' => $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 2) : 0,
            'unique_participants' => $participants->unique('actor_id')->count(),
            'average_duration_seconds' => round($averageDuration),
        ];
    }
}
