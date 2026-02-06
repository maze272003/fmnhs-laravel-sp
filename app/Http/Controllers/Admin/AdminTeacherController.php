<?php

namespace App\Http\Controllers\Admin;

use App\Models\Teacher;
use App\Models\Section;
use App\Models\Subject; // <--- ADDED: Import the Subject model
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTeacherController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $viewArchived = $request->has('archived');

        // 1. Fetch Teachers
        $teachers = Teacher::with('advisorySection')
            ->when($viewArchived, function ($query) {
                return $query->onlyTrashed();
            })
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('employee_id', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        // 2. Fetch Sections (for Advisory dropdown)
        $sections = Section::with('advisor')
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        // 3. Fetch Subjects (for Department dropdown) <--- ADDED THIS
        // We get all subjects to populate the department list dynamically
        $subjects = Subject::all();

        return view('admin.manage_teacher', compact('teachers', 'viewArchived', 'sections', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:teachers,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'department' => 'required|string',
        ]);

        $validated['password'] = Hash::make('password');

        Teacher::create($validated);

        return back()->with('success', 'New faculty member added successfully!');
    }

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'department' => 'required|string',
            'advisory_section' => 'nullable|exists:sections,id',
        ]);

        // Update Basic Info
        $teacher->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'department' => $validated['department'],
        ]);

        // Handle Advisory Transfer
        Section::where('teacher_id', $teacher->id)->update(['teacher_id' => null]);

        if ($request->filled('advisory_section')) {
            $section = Section::find($request->advisory_section);
            $section->teacher_id = $teacher->id;
            $section->save();
        }

        return back()->with('success', 'Faculty profile and advisory updated successfully!');
    }

    public function archive(Teacher $teacher)
    {
        if($teacher->advisorySection) {
            $teacher->advisorySection->update(['teacher_id' => null]);
        }
        $teacher->delete(); 
        return back()->with('success', 'Faculty member moved to archive.');
    }

    public function restore($id)
    {
        $teacher = Teacher::onlyTrashed()->findOrFail($id);
        $teacher->restore(); 
        return back()->with('success', 'Faculty access restored successfully!');
    }
}