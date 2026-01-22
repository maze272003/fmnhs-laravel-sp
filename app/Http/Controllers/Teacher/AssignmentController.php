<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Contracts\Services\AssignmentServiceInterface;
use App\Contracts\Repositories\AssignmentRepositoryInterface;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function __construct(
        private AssignmentServiceInterface $assignmentService,
        private AssignmentRepositoryInterface $assignmentRepository,
        private ScheduleRepositoryInterface $scheduleRepository
    ) {}

    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        
        $classes = $this->scheduleRepository->getUniqueClasses($teacherId);
        
        $assignments = $this->assignmentRepository->getByTeacher($teacherId)->load('subject', 'section');

        return view('teacher.assignment', compact('classes', 'assignments'));
    }

    public function show($id): View
    {
        $assignment = $this->assignmentRepository->findOrFail($id);
        $assignment->load('subject', 'section', 'submissions.student');

        return view('teacher.show.show', compact('assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'class_info' => 'required',
            'title' => 'required|string|max:255',
            'deadline' => 'required|date|after:now',
            'attachment' => 'nullable|file|max:10240'
        ]);

        [$subjectId, $sectionId] = explode('|', $request->class_info);
        
        $filename = null;
        if ($request->hasFile('attachment')) {
            $filename = time() . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move(public_path('uploads/assignments'), $filename);
        }

        $assignmentData = [
            'subject_id' => $subjectId,
            'section_id' => $sectionId,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
        ];

        if ($filename) {
            $assignmentData['file_path'] = $filename;
        }

        $this->assignmentService->createAssignment($assignmentData, Auth::guard('teacher')->id());

        return back()->with('success', 'New task assigned successfully!');
    }
}
