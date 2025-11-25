<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AdminStudentController extends Controller
{
    // READ (List)
    public function index()
    {
        $students = Student::orderBy('last_name')->paginate(10);
        return view('admin.manage_student', compact('students'));
    }

    // CREATE (Store)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12', // LRN validation
            'email' => 'required|email|unique:students,email',
            'grade_level' => 'required|integer|min:7|max:12', // JHS/SHS range
            'section' => 'required|string|max:50',
        ]);

        // Default password is LRN (and hashed)
        $validated['password'] = Hash::make($request->lrn); 

        Student::create($validated);

        return redirect()->back()->with('success', 'Student added successfully!');
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // Email ignores its own current ID to allow updates without changing email
            'email' => 'required|email|unique:students,email,'.$student->id, 
            'grade_level' => 'required|integer|min:7|max:12',
            'section' => 'required|string|max:50',
            'new_password' => 'nullable|min:6' // Optional password reset
        ]);

        if ($request->filled('new_password')) {
            $validated['password'] = Hash::make($request->new_password);
        }

        $student->update($validated);

        return redirect()->back()->with('success', 'Student details updated!');
    }

    // DELETE
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return redirect()->back()->with('success', 'Student deleted successfully!');
    }
}