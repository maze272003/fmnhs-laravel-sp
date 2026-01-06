<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentAssignmentController extends Controller
{
    public function index(): View
    {
        $student = Auth::guard('student')->user();

        // Refactored: Filter by section_id instead of section string
        $assignments = Assignment::where('section_id', $student->section_id)
            ->with(['subject', 'submissions' => function($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('deadline', 'asc')
            ->get();

        return view('student.assignment', compact('assignments'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'file' => 'required|file|max:10240' // 10MB
        ]);

        $student = Auth::guard('student')->user();
        $file = $request->file('file');
        
        $filename = time() . '_' . $student->id . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/submissions'), $filename);

        Submission::create([
            'assignment_id' => $request->assignment_id,
            'student_id'    => $student->id,
            'file_path'     => $filename,
            'remarks'       => 'Turned in'
        ]);

        return back()->with('success', 'Work submitted successfully!');
    }
}