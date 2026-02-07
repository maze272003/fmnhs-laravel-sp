<?php

use App\Models\Student;
use App\Models\Teacher;
use App\Models\VideoConference;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conference.{conferenceId}', function ($user, int $conferenceId) {
    $conference = VideoConference::find($conferenceId);

    if (! $conference || ! $conference->is_active || $conference->ended_at !== null) {
        return false;
    }

    if ($user instanceof Teacher && (int) $user->id === (int) $conference->teacher_id) {
        return [
            'id' => 'teacher-'.$user->id,
            'name' => trim($user->first_name.' '.$user->last_name),
            'role' => 'teacher',
        ];
    }

    if ($user instanceof Student && $conference->canStudentJoin($user)) {
        return [
            'id' => 'student-'.$user->id,
            'name' => trim($user->first_name.' '.$user->last_name),
            'role' => 'student',
        ];
    }

    return false;
});
