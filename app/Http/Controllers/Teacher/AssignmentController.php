<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\Schedule; // To get assigned classes
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index()
    {
        $teacherId = Auth::guard('teacher')->id();
        
        // 1. Get classes assigned to teacher (for the dropdown)
        $classes = Schedule::where('teacher_id', $teacherId)
            ->with('subject')
            ->get()
            ->unique(function ($item) { return $item->subject_id . '-' . $item->section; });

        // 2. Get existing assignments created by this teacher
        $assignments = Assignment::where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.assignment', compact('classes', 'assignments'));
    }
    public function show($id)
    {
        // Kunin ang assignment, kasama ang submissions at info ng student
        $assignment = Assignment::with(['submissions.student', 'subject'])
            ->findOrFail($id);

        return view('teacher.show.show', compact('assignment'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_info' => 'required', // "subject_id|section"
            'title' => 'required',
            'deadline' => 'required|date',
            'attachment' => 'nullable|file|max:10240' // 10MB Max
        ]);

        // Split the value "1|Rizal"
        [$subjectId, $section] = explode('|', $request->class_info);
        
        $filename = null;
        if ($request->hasFile('attachment')) {
            $filename = time() . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move(public_path('uploads/assignments'), $filename);
        }

        Assignment::create([
            'teacher_id' => Auth::guard('teacher')->id(),
            'subject_id' => $subjectId,
            'section' => $section,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'file_path' => $filename
        ]);

        return back()->with('success', 'Assignment created successfully!');
    }
}