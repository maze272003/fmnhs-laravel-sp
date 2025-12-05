<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentAccountCreated;


class AdminStudentController extends Controller
{
    // READ (List)
   public function index(Request $request)
{
    $query = Student::query();

    if ($request->filled('search')) {
        $search = $request->input('search');

        // I-group natin ang WHERE clause para hindi magulo ang logic
        $query->where(function($q) use ($search) {
            $q->where('first_name', 'LIKE', "%{$search}%")
              ->orWhere('last_name', 'LIKE', "%{$search}%")
              ->orWhere('lrn', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%"); // <--- ETO ANG UPDATE
        });
    }

    $students = $query->orderBy('last_name')->paginate(10);

    return view('admin.manage_student', compact('students'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12',
            'email' => 'required|email|unique:students,email',
            'grade_level' => 'required|integer|min:7|max:12',
            'section' => 'required|string|max:50',
            // LOGIC: Required if grade is 11 or 12, otherwise nullable
            'strand' => 'nullable|required_if:grade_level,11,12|string|max:50', 
        ]);

        $rawPassword = $request->lrn; // Get the raw LRN to send in email
        $validated['password'] = Hash::make($rawPassword); 

        // Ensure strand is null if grade is 7-10 (clean up data)
        if ($request->grade_level < 11) {
            $validated['strand'] = null;
        }

        $student = Student::create($validated);

        // SEND EMAIL
        try {
            Mail::to($student->email)->send(new StudentAccountCreated($student, $rawPassword));
        } catch (\Exception $e) {
            // Optional: Log error if email fails, but don't stop the process
            // \Log::error('Mail sending failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Student added and email sent successfully!');
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,'.$student->id, 
            'grade_level' => 'required|integer|min:7|max:12',
            'section' => 'required|string|max:50',
            // Same logic for update
            'strand' => 'nullable|required_if:grade_level,11,12|string|max:50',
            'new_password' => 'nullable|min:6'
        ]);

        if ($request->grade_level < 11) {
            $validated['strand'] = null;
        }

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