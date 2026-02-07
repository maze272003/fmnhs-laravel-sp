<?php
// app/Http/Controllers/Student/StudentAssignmentController.php

namespace App\Http\Controllers\Student; // DAPAT STUDENT

use App\Http\Controllers\Controller;
use App\Services\AssignmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller // DAPAT STUDENTASSIGNMENTCONTROLLER
{
    public function __construct(private readonly AssignmentWorkflowService $assignmentWorkflow)
    {
    }

    public function index()
    {
        $student = Auth::guard('student')->user();

        $assignments = $this->assignmentWorkflow->getStudentAssignments(
            (int) $student->section_id,
            (int) $student->id
        );

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'attachment'    => 'required|file|max:20480', 
        ]);

        $student = Auth::guard('student')->user();
        
        $this->assignmentWorkflow->submitStudentWork(
            (int) $request->assignment_id,
            (int) $student->id,
            $request->file('attachment')
        );

        return back()->with('success', 'Output submitted successfully!');
    }
}
