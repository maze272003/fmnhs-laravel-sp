<?php
namespace App\Http\Controllers\Admin;

use App\Models\Teacher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $viewArchived = $request->has('archived');

    $teachers = Teacher::with('advisorySection')
        // 1. Filter para sa Archive vs Active
        ->when($viewArchived, function ($query) {
            return $query->onlyTrashed();
        })
        // 2. Filter para sa Search (Employee ID, First Name, o Last Name)
        ->when($search, function ($query, $search) {
            return $query->where(function($q) use ($search) {
                $q->where('employee_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        })
        ->orderBy('last_name')
        ->paginate(10)
        ->withQueryString(); // Importante para hindi mawala ang search sa pagination

    return view('admin.manage_teacher', compact('teachers', 'viewArchived'));
}

    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'department' => 'required|string',
        ]);

        $teacher->update($validated);
        return back()->with('success', 'Faculty profile updated successfully!');
    }

    public function archive(Teacher $teacher)
    {
        $teacher->delete(); // Soft delete
        return back()->with('success', 'Faculty member moved to archive.');
    }

    public function restore($id)
    {
        $teacher = Teacher::onlyTrashed()->findOrFail($id);
        $teacher->restore(); //
        return back()->with('success', 'Faculty access restored successfully!');
    }
}