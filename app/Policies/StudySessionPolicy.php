<?php

namespace App\Policies;

use App\Models\StudySession;
use Illuminate\Support\Facades\Auth;

class StudySessionPolicy
{
    /**
     * Determine if the user can view the study session.
     *
     * Only the student who owns it can view.
     */
    public function view(mixed $user, StudySession $session): bool
    {
        return Auth::guard('student')->check()
            && $session->student_id === $user->getKey();
    }

    /**
     * Determine if the user can update the study session.
     *
     * Only the student who owns it can update.
     */
    public function update(mixed $user, StudySession $session): bool
    {
        return Auth::guard('student')->check()
            && $session->student_id === $user->getKey();
    }

    /**
     * Determine if the user can delete the study session.
     *
     * Only the student who owns it can delete.
     */
    public function delete(mixed $user, StudySession $session): bool
    {
        return Auth::guard('student')->check()
            && $session->student_id === $user->getKey();
    }
}
