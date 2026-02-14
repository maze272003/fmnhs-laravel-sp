<?php

namespace App\Policies;

use App\Models\ProgressReport;
use Illuminate\Support\Facades\Auth;

class ProgressReportPolicy
{
    /**
     * Determine if the user can view the progress report.
     *
     * The teacher who created it, the student it's for, or an admin can view.
     */
    public function view(mixed $user, ProgressReport $report): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        if (Auth::guard('teacher')->check() && $report->teacher_id === $user->getKey()) {
            return true;
        }

        return Auth::guard('student')->check()
            && $report->student_id === $user->getKey();
    }

    /**
     * Determine if the user can create a progress report.
     *
     * Only teachers can create.
     */
    public function create(mixed $user): bool
    {
        return Auth::guard('teacher')->check();
    }

    /**
     * Determine if the user can send the progress report.
     *
     * Only the teacher who created it can send.
     */
    public function send(mixed $user, ProgressReport $report): bool
    {
        return Auth::guard('teacher')->check()
            && $report->teacher_id === $user->getKey();
    }
}
