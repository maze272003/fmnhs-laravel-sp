<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Section;
use App\Models\Grade;
use App\Models\Room;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AdminScheduleController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $sections = Section::all();
        
        // This ensures only rooms with is_available = 1 are shown in the dropdown
        // $rooms = Room::where('is_available', true)->get();
       $rooms = Room::orderBy('name')->get();
        $schedules = Schedule::with(['subject', 'teacher', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(10);

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

        // [Existing Validation Logic Here...]
        if (Schedule::hasTeacherConflict($request->teacher_id, $request->day, $request->start_time, $request->end_time)) {
            return back()->withInput()->withErrors(['teacher_id' => 'Time conflict: Teacher already has a class.']);
        }
        if (Schedule::hasRoomConflict($request->room, $request->day, $request->start_time, $request->end_time)) {
            return back()->withInput()->withErrors(['room' => 'Room conflict: Room already occupied.']);
        }
        if (Schedule::hasSectionConflict($request->section_id, $request->day, $request->start_time, $request->end_time)) {
            return back()->withInput()->withErrors(['section_id' => 'Time conflict: Section already has a class.']);
        }

        // 1. Create the Schedule
        $schedule = Schedule::create($request->all());

        // 2. LOCK THE ROOM (New Logic)
        // Find the room by name and set is_available to false (0)
        // This stops it from showing in the dropdown next time.
        Room::where('name', $request->room)->update(['is_available' => false]);

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Schedule', $schedule->id, 'created',
            null, null, $schedule->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return back()->with('success', 'Schedule Added! Room has been marked as occupied.');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $scheduleData = $schedule->toArray();
        $roomName = $schedule->room; // Capture room name before deleting

        // Delete associated grades
        Grade::where('teacher_id', $schedule->teacher_id)
            ->where('subject_id', $schedule->subject_id)
            ->whereHas('student', function ($q) use ($schedule) {
                $q->where('section_id', $schedule->section_id);
            })
            ->delete();

        // Delete the schedule
        $schedule->delete();

        // 3. UNLOCK THE ROOM (New Logic)
        // Check if any other schedules still use this room. 
        // If count is 0, make it available again.
        $remainingUsage = Schedule::where('room', $roomName)->count();
        
        if ($remainingUsage === 0) {
            Room::where('name', $roomName)->update(['is_available' => true]);
        }

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Schedule', $id, 'deleted',
            null, $scheduleData, null,
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return back()->with('success', 'Schedule removed. Room availability updated.');
    }
}