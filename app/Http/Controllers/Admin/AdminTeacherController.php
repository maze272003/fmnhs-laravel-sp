<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\TeacherManagementService;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    public function __construct(private readonly TeacherManagementService $teacherManagement)
    {
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $viewArchived = $request->has('archived');

        return view('admin.manage_teacher', $this->teacherManagement->getAdminTeacherData($search, $viewArchived));
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

        $this->teacherManagement->create($validated);

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

        $this->teacherManagement->update(
            $teacher,
            $validated,
            $request->filled('advisory_section') ? (int) $request->advisory_section : null
        );

        return back()->with('success', 'Faculty profile and advisory updated successfully!');
    }

    public function archive(Teacher $teacher)
    {
        $this->teacherManagement->archive($teacher);
        return back()->with('success', 'Faculty member moved to archive.');
    }

    public function restore($id)
    {
        $this->teacherManagement->restore((int) $id);
        return back()->with('success', 'Faculty access restored successfully!');
    }
}
