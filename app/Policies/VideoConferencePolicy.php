<?php

namespace App\Policies;

use App\Models\VideoConference;
use Illuminate\Support\Facades\Auth;

class VideoConferencePolicy
{
    /**
     * Determine if the user can view the video conference.
     *
     * The host teacher or any participant can view.
     */
    public function view(mixed $user, VideoConference $conference): bool
    {
        if ($conference->teacher_id === $user->getKey() && $user instanceof \App\Models\Teacher) {
            return true;
        }

        return $conference->participants()
            ->where('actor_type', get_class($user))
            ->where('actor_id', $user->getKey())
            ->exists();
    }

    /**
     * Determine if the user can create a video conference.
     *
     * Only teachers can create.
     */
    public function create(mixed $user): bool
    {
        return Auth::guard('teacher')->check();
    }

    /**
     * Determine if the user can update the video conference.
     *
     * Only the host teacher can update.
     */
    public function update(mixed $user, VideoConference $conference): bool
    {
        return Auth::guard('teacher')->check()
            && $conference->teacher_id === $user->getKey();
    }

    /**
     * Determine if the user can delete the video conference.
     *
     * Only the host teacher can delete.
     */
    public function delete(mixed $user, VideoConference $conference): bool
    {
        return Auth::guard('teacher')->check()
            && $conference->teacher_id === $user->getKey();
    }

    /**
     * Determine if the user can end the video conference.
     *
     * Only the host teacher can end.
     */
    public function end(mixed $user, VideoConference $conference): bool
    {
        return Auth::guard('teacher')->check()
            && $conference->teacher_id === $user->getKey();
    }
}
