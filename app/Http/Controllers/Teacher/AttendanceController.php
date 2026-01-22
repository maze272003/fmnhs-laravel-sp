<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AttendanceServiceInterface;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected AttendanceServiceInterface $attendanceService;
    protected ScheduleRepositoryInterface $scheduleRepository;

    public function __construct(
        AttendanceServiceInterface $attendanceService,
        ScheduleRepositoryInterface $scheduleRepository
    ) {
        $this->attendanceService = $attendanceService;
        $this->scheduleRepository = $scheduleRepository;
    }

    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        $assignedClasses = $this->scheduleRepository->getUniqueClasses($teacherId);

        return view('teacher.attendance', compact('assignedClasses'));
    }

    public function show(Request $request): View
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date'
        ]);

        $subjectId = $validated['subject_id'];
        $sectionId = $validated['section_id'];
        $date = $validated['date'];

        $attendanceData = $this->attendanceService->getAttendanceForClass($sectionId, $subjectId, $date);

        return view('teacher.show.attendance', [
            'students' => $attendanceData['students'],
            'subjectId' => $subjectId,
            'subjectName' => $attendanceData['subject'],
            'sectionName' => $attendanceData['section'],
            'date' => $date,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'date' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'required|in:present,absent,late,excused',
        ]);

        $teacherId = Auth::guard('teacher')->id();

        $result = $this->attendanceService->markAttendance(
            $validated['section_id'],
            $validated['subject_id'],
            $validated['date'],
            $validated['status'],
            $teacherId
        );

        return back()->with('success', $result['message']);
    }
}
