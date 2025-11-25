<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Logic: Get assignments where the section matches the student's section
        $assignments = Assignment::where('section', $student->section)
            ->with(['subject', 'submissions' => function($q) use ($student) {
                // Check if this specific student has submitted
                $q->where('student_id', $student->id);
            }])
            ->orderBy('deadline', 'asc')
            ->get();

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required',
            'file' => 'required|file|max:10240'
        ]);

        $filename = time() . '_' . Auth::guard('student')->id() . '_' . $request->file('file')->getClientOriginalName();
        $request->file('file')->move(public_path('uploads/submissions'), $filename);

        Submission::create([
            'assignment_id' => $request->assignment_id,
            'student_id' => Auth::guard('student')->id(),
            'file_path' => $filename,
            'remarks' => 'Turned in'
        ]);

        return back()->with('success', 'Work submitted successfully!');
    }
}