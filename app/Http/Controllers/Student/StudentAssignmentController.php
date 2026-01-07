<?php
// app/Http/Controllers/Student/StudentAssignmentController.php

namespace App\Http\Controllers\Student; // DAPAT STUDENT

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller // DAPAT STUDENTASSIGNMENTCONTROLLER
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $assignments = Assignment::where('section_id', $student->section_id)
            ->with(['subject', 'submissions' => function($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('deadline', 'asc')
            ->get();

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'attachment'    => 'required|file|max:20480', 
        ]);

        $student = Auth::guard('student')->user();
        
        if ($request->hasFile('attachment')) {
            $filename = time() . '_stud' . $student->id . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move(public_path('uploads/submissions'), $filename);

            Submission::updateOrCreate(
                ['assignment_id' => $request->assignment_id, 'student_id' => $student->id],
                ['file_path' => $filename, 'submitted_at' => now()]
            );
        }

        return back()->with('success', 'Output submitted successfully!');
    }
}