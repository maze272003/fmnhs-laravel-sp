<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AssignmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller
{
    public function __construct(private readonly AssignmentWorkflowService $assignmentWorkflow)
    {
    }

    public function index()
    {
        $student = Auth::guard('student')->user();

        if (!$student->section_id) {
            return back()->with('error', 'You are not assigned to a section.');
        }

        $assignments = $this->assignmentWorkflow->getStudentAssignments(
            (int) $student->section_id,
            (int) $student->id
        );

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request)
    {
        // 1. Strict Validation
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            // Name must be 'file' to match your Blade form
            'file'          => 'required|file|mimes:pdf,doc,docx,jpg,png,jpeg,zip,ppt,pptx,xlsx,xls|max:25600', // Max 25MB
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Invalid file format. Allowed: PDF, Word, Excel, PowerPoint, Images, ZIP.',
            'file.max'      => 'File size too large. Maximum size is 25MB.',
        ]);

        try {
            $student = Auth::guard('student')->user();
            
            // 2. Pass to Service
            $this->assignmentWorkflow->submitStudentWork(
                (int) $request->assignment_id,
                (int) $student->id,
                $request->file('file') // Use 'file' input name
            );

            return back()->with('success', 'Assignment submitted successfully!');
        } catch (\Exception $e) {
            \Log::error("Submission Error: " . $e->getMessage());
            return back()->with('error', 'Submission failed. Please try again.');
        }
    }
}