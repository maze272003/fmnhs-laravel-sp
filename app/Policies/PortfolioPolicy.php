<?php

namespace App\Policies;

use App\Models\Portfolio;
use Illuminate\Support\Facades\Auth;

class PortfolioPolicy
{
    /**
     * Determine if the user can view the portfolio.
     *
     * The owner student, any teacher, or any admin can view.
     */
    public function view(mixed $user, Portfolio $portfolio): bool
    {
        if (Auth::guard('teacher')->check() || Auth::guard('admin')->check()) {
            return true;
        }

        return Auth::guard('student')->check()
            && $portfolio->student_id === $user->getKey();
    }

    /**
     * Determine if the user can update the portfolio.
     *
     * Only the owner student can update.
     */
    public function update(mixed $user, Portfolio $portfolio): bool
    {
        return Auth::guard('student')->check()
            && $portfolio->student_id === $user->getKey();
    }

    /**
     * Determine if the user can delete the portfolio.
     *
     * Only the owner student or an admin can delete.
     */
    public function delete(mixed $user, Portfolio $portfolio): bool
    {
        if (Auth::guard('admin')->check()) {
            return true;
        }

        return Auth::guard('student')->check()
            && $portfolio->student_id === $user->getKey();
    }
}
