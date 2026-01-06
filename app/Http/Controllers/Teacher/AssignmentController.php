<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Schedule;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();
        
        // 1. Kunin ang unique classes (Subject + Section) mula sa Schedule
        $classes = Schedule::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->get()
            ->unique(function ($item) { 
                return $item->subject_id . '-' . $item->section_id; 
            });

        // 2. Kunin ang assignments na ginawa ng teacher na ito
        $assignments = Assignment::where('teacher_id', $teacherId)
            ->with(['subject', 'section'])
            ->latest()
            ->get();

        return view('teacher.assignment', compact('classes', 'assignments'));
    }

    public function show($id)
{
    // Gamitin ang load() para makuha ang relationships
    $assignment = Assignment::with(['subject', 'section', 'submissions.student'])
        ->findOrFail($id);

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
        
        $filename = null;
        if ($request->hasFile('attachment')) {
            $filename = time() . '_' . $request->file('attachment')->getClientOriginalName();
            $request->file('attachment')->move(public_path('uploads/assignments'), $filename);
        }

        Assignment::create([
            'teacher_id'  => Auth::guard('teacher')->id(),
            'subject_id'  => $subjectId,
            'section_id'  => $sectionId, // Normalized ID
            'title'       => $request->title,
            'description' => $request->description,
            'deadline'    => $request->deadline,
            'file_path'   => $filename
        ]);

        return back()->with('success', 'New task assigned successfully!');
    }
}