<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Services\ScheduleManagementService;
use Illuminate\Support\Facades\Auth;

class AdminScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleRepositoryInterface $schedules,
        private readonly ScheduleManagementService $scheduleManagement
    ) {
    }

    public function index()
    {
        $subjects = $this->schedules->getSubjects();
        $teachers = $this->schedules->getTeachers();
        $sections = $this->schedules->getSections();
        $rooms = $this->schedules->getRooms();
        $schedules = $this->schedules->paginateSchedules(10);

        return view('admin.schedule', compact('subjects', 'teachers', 'sections', 'schedules', 'rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day'        => 'required|string',
            'start_time' => 'required|date_format:H:i', 
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'room'       => 'required|string'
        ]);

        $admin = Auth::guard('admin')->user();
        $this->scheduleManagement->create($request->all(), $admin);

        return back()->with('success', 'Schedule Added! Room has been marked as occupied.');
    }

    public function destroy($id)
    {
        $admin = Auth::guard('admin')->user();
        $this->scheduleManagement->delete((int) $id, $admin);

        return back()->with('success', 'Schedule removed. Room availability updated.');
    }
}
