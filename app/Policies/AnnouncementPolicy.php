<?php

namespace App\Policies;

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AnnouncementPolicy
{
    /**
     * Determine if the user can delete the announcement.
     *
     * Admins can always delete. Other users can only delete announcements they created.
     */
    public function delete(mixed $user, Announcement $announcement): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return $announcement->created_by_type !== null
            && $announcement->created_by_type === get_class($user)
            && $announcement->created_by_id === $user->getKey();
    }
}
