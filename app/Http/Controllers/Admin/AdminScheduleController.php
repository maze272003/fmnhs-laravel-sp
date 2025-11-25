<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Student;

class AdminScheduleController extends Controller
{
    public function index()
    {
        // Fetch data for the form dropdowns
        $subjects = Subject::all();
        $teachers = Teacher::all();
        // Get unique sections from Student table
        $sections = Student::select('section')->distinct()->pluck('section');

        // Get existing schedules to display in table
        $schedules = Schedule::with(['subject', 'teacher'])->orderBy('day')->orderBy('start_time')->paginate(10);

        return view('admin.schedule', compact('subjects', 'teachers', 'sections', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section' => 'required',
            'subject_id' => 'required',
            'teacher_id' => 'required',
            'day' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        // Optional: Add Logic here to check for Conflict (e.g. Teacher is busy at that time)

        Schedule::create($request->all());

        return back()->with('success', 'Class Schedule Assigned! Teacher can now grade this class.');
    }

    public function destroy($id)
    {
        Schedule::findOrFail($id)->delete();
        return back()->with('success', 'Schedule deleted.');
    }
}