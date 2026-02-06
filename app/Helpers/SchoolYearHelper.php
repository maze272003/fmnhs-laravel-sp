<?php

namespace App\Helpers;

class SchoolYearHelper
{
    /**
     * Get the current school year in DepEd format.
     * School year starts in June, so June 2024 to May 2025 = "2024-2025".
     */
    public static function current(): string
    {
        $month = (int) date('n');
        $year = (int) date('Y');

        // If June or later, the school year starts this year
        // Otherwise, it started last year
        $startYear = $month >= 6 ? $year : $year - 1;

        return $startYear . '-' . ($startYear + 1);
    }

    /**
     * Get the next school year (for promotion).
     */
    public static function next(): string
    {
        $month = (int) date('n');
        $year = (int) date('Y');

        $startYear = $month >= 6 ? $year + 1 : $year;

        return $startYear . '-' . ($startYear + 1);
    }
}
