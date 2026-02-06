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
        $rooms = Room::where('is_available', true)->get();

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
            'start_time'=> 'required|date_format:H:i', 
            'end_time'  => 'required|date_format:H:i|after:start_time',
            'room'      => 'required|string'
        ]);

        // Teacher schedule conflict validation
        if (Schedule::hasTeacherConflict(
            $request->teacher_id,
            $request->day,
            $request->start_time,
            $request->end_time
        )) {
            return back()->withInput()->withErrors([
                'teacher_id' => 'Schedule invalid due to time conflict: this teacher already has a class at the selected time.'
            ]);
        }

        // Room availability validation
        if (Schedule::hasRoomConflict(
            $request->room,
            $request->day,
            $request->start_time,
            $request->end_time
        )) {
            return back()->withInput()->withErrors([
                'room' => 'Schedule invalid due to room conflict: this room is already assigned to another class at the selected time.'
            ]);
        }

        // Section schedule conflict validation
        if (Schedule::hasSectionConflict(
            $request->section_id,
            $request->day,
            $request->start_time,
            $request->end_time
        )) {
            return back()->withInput()->withErrors([
                'section_id' => 'Schedule invalid due to time conflict: this section already has a class at the selected time.'
            ]);
        }

        $schedule = Schedule::create($request->all());

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Schedule', $schedule->id, 'created',
            null, null,
            $schedule->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return back()->with('success', 'Schedule Added Successfully!');
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $scheduleData = $schedule->toArray();

        Grade::where('teacher_id', $schedule->teacher_id)
            ->where('subject_id', $schedule->subject_id)
            ->whereHas('student', function ($q) use ($schedule) {
                $q->where('section_id', $schedule->section_id);
            })
            ->delete();

        $schedule->delete();

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Schedule', $id, 'deleted',
            null, $scheduleData, null,
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return back()->with('success', 'Schedule and related class assignments have been removed!');
    }
}