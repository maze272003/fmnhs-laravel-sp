<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\AssignmentWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentWorkflowService $assignmentWorkflow)
    {
    }

    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        return view('teacher.assignment', $this->assignmentWorkflow->getTeacherAssignmentPageData($teacherId));
    }

    public function show($id)
    {
        $assignment = $this->assignmentWorkflow->getAssignmentDetail((int) $id);
        return view('teacher.show.show', compact('assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_info' => 'required', // Format: "subject_id|section_id"
            'title' => 'required|string|max:255',
            'deadline' => 'required|date|after:now',
            'attachment' => 'nullable|file|max:10240'
        ]);

        [$subjectId, $sectionId] = explode('|', $request->class_info);
        $this->assignmentWorkflow->createTeacherAssignment(
            (int) Auth::guard('teacher')->id(),
            (int) $subjectId,
            (int) $sectionId,
            $request->only(['title', 'description', 'deadline']),
            $request->file('attachment')
        );

        return back()->with('success', 'New task assigned successfully!');
    }
}
