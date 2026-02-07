<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherAttendanceService;
use App\Models\Schedule; // Import Schedule model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(private readonly TeacherAttendanceService $teacherAttendance)
    {
    }

    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        
        // UPDATED: Fetch classes manually here to ensure 'section.schoolYear' is loaded
        $assignedClasses = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section.schoolYear']) // Eager load School Year
            ->get()
            ->unique(function ($item) {
                return $item->subject_id . '-' . $item->section_id;
            });

        return view('teacher.attendance', compact('assignedClasses'));
    }

    // ... (Keep your show and store methods as they are) ...
    public function show(Request $request): View
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
        ]);

        return view('teacher.show.attendance', $this->teacherAttendance->getAttendanceSheet(
            (int) $validated['subject_id'],
            (int) $validated['section_id'],
            $validated['date']
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $teacherId = Auth::guard('teacher')->id();
        $this->teacherAttendance->saveAttendance(
            $teacherId,
            (int) $request->subject_id,
            (int) $request->section_id,
            (string) $request->date,
            (array) $request->status
        );

        return back()->with('success', 'Attendance record updated!');
    }
}