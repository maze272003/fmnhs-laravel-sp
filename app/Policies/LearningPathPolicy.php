<?php

namespace App\Policies;

use App\Models\LearningPath;
use Illuminate\Support\Facades\Auth;

class LearningPathPolicy
{
    /**
     * Determine if the user can view the learning path.
     *
     * Any authenticated user can view.
     */
    public function view(mixed $user, LearningPath $path): bool
    {
        return true;
    }

    /**
     * Determine if the user can create a learning path.
     *
     * Only teachers or admins can create.
     */
    public function create(mixed $user): bool
    {
        return Auth::guard('teacher')->check()
            || Auth::guard('admin')->check();
    }

    /**
     * Determine if the user can update the learning path.
     *
     * Only the creator or admins can update.
     */
    public function update(mixed $user, LearningPath $path): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return $path->created_by_type !== null
            && $path->created_by_type === get_class($user)
            && $path->created_by_id === $user->getKey();
    }
}
