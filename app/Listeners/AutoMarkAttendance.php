<?php

namespace App\Listeners;

use App\Events\ConferenceJoined;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\VideoConference;
use App\Services\AutoAttendanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AutoMarkAttendance
{
    protected AutoAttendanceService $attendanceService;

    public function __construct(AutoAttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function handle(ConferenceJoined $event): void
    {
        $participant = $event->participant;
        
        if (!$participant->student_id) {
            return;
        }

        $student = Student::find($participant->student_id);
        $conference = $participant->conference;
        
        if (!$student || !$conference) {
            return;
        }

        $this->attendanceService->markAttendanceOnJoin($conference, $student);
    }
}

