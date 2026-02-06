<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGradeController extends Controller
{
    /**
     * Lock grades for a specific subject/section/school year combination.
     */
    public function lockGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'school_year' => 'required|string',
        ]);

        $admin = Auth::guard('admin')->user();

        $grades = Grade::where('subject_id', $request->subject_id)
            ->where('school_year', $request->school_year)
            ->whereHas('student', fn($q) => $q->where('section_id', $request->section_id))
            ->where('is_locked', false)
            ->get();

        foreach ($grades as $grade) {
            $grade->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $admin->name ?? 'Admin',
            ]);

            AuditTrail::log(
                'Grade', $grade->id, 'locked',
                'is_locked', 'false', 'true',
                'admin', $admin->id ?? null, $admin->name ?? 'Admin'
            );
        }

        return back()->with('success', $grades->count() . ' grade(s) locked successfully.');
    }

    /**
     * Unlock grades (admin approval required).
     */
    public function unlockGrades(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'school_year' => 'required|string',
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('admin')->user();

        $grades = Grade::where('subject_id', $request->subject_id)
            ->where('school_year', $request->school_year)
            ->whereHas('student', fn($q) => $q->where('section_id', $request->section_id))
            ->where('is_locked', true)
            ->get();

        foreach ($grades as $grade) {
            $grade->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
            ]);

            AuditTrail::log(
                'Grade', $grade->id, 'unlocked',
                'is_locked', 'true', 'false (Reason: ' . $request->reason . ')',
                'admin', $admin->id ?? null, $admin->name ?? 'Admin'
            );
        }

        return back()->with('success', $grades->count() . ' grade(s) unlocked successfully.');
    }
}
