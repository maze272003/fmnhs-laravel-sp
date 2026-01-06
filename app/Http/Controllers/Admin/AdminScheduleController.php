<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Section;

class AdminScheduleController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        $teachers = Teacher::all();
        $sections = Section::all(); // Get all normalized sections

        $schedules = Schedule::with(['subject', 'teacher', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(10);

        return view('admin.schedule', compact('subjects', 'teachers', 'sections', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day'        => 'required|string',
            // Binago mula H:i:s tungo sa H:i
            'start_time'=> 'required|date_format:H:i', 
            'end_time'  => 'required|date_format:H:i|after:start_time',
            'room'      => 'required|string'
        ]);

        Schedule::create($request->all());
        
        return back()->with('success', 'Schedule Added Successfully!');
    }
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return back()->with('success', 'Schedule Deleted!');
    }
}