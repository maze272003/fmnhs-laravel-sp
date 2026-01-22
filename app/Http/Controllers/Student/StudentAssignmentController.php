<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Contracts\Services\SubmissionServiceInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssignmentController extends Controller
{
    public function __construct(
        private SubmissionServiceInterface $submissionService,
        private AssignmentRepositoryInterface $assignmentRepository
    ) {}

    public function index()
    {
        $student = Auth::guard('student')->user();

        $assignments = $this->assignmentRepository->getBySectionWithSubmissions($student->section_id, $student->id);

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'attachment'    => 'required|file|max:20480', 
        ]);

        $student = Auth::guard('student')->user();
        
        $filename = null;
        if ($request->hasFile('attachment')) {
            $filename = time() . '_stud' . $student->id . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move(public_path('uploads/submissions'), $filename);
        }

        $this->submissionService->submitAssignment($request->assignment_id, $student->id, $filename);

        return back()->with('success', 'Output submitted successfully!');
    }
}
