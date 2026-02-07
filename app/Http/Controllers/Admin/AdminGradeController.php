<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GradeLockingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGradeController extends Controller
{
    public function __construct(private readonly GradeLockingService $gradeLocking)
    {
    }

    /**
     * Lock grades for a specific subject/section/school year combination.
     */
    public function lockGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'school_year_id' => 'required|exists:school_year_configs,id',
        ]);

        $admin = Auth::guard('admin')->user();
        $count = $this->gradeLocking->lock(
            (int) $request->subject_id,
            (int) $request->section_id,
            (int) $request->school_year_id,
            $admin
        );

        return back()->with('success', $count . ' grade(s) locked successfully.');
    }

    /**
     * Unlock grades (admin approval required).
     */
    public function unlockGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'school_year_id' => 'required|exists:school_year_configs,id',
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('admin')->user();
        $count = $this->gradeLocking->unlock(
            (int) $request->subject_id,
            (int) $request->section_id,
            (int) $request->school_year_id,
            $request->reason,
            $admin
        );

        return back()->with('success', $count . ' grade(s) unlocked successfully.');
    }
}
