<?php

use App\Models\VideoConference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conference.{conferenceId}', function ($user, int $conferenceId) {
    $conference = VideoConference::find($conferenceId);

    if (! $conference || ! $conference->is_active || $conference->ended_at !== null) {
        return false;
    }

    $teacher = Auth::guard('teacher')->user();
    if ($teacher && (int) $teacher->id === (int) $conference->teacher_id) {
        return [
            'id' => 'teacher-'.$teacher->id,
            'name' => trim($teacher->first_name.' '.$teacher->last_name),
            'role' => 'teacher',
        ];
    }

    $student = Auth::guard('student')->user();
    if ($student && $conference->canStudentJoin($student)) {
        return [
            'id' => 'student-'.$student->id,
            'name' => trim($student->first_name.' '.$student->last_name),
            'role' => 'student',
        ];
    }

    return false;
}, ['guards' => ['teacher', 'student']]);
