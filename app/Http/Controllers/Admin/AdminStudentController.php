<?php
namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentAccountCreated;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        // Load the section relationship to display Section Name and Grade
        $query = Student::with('section');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('lrn', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->orderBy('last_name')->paginate(10);
        $sections = Section::all(); // Pass this for the "Add/Edit" dropdowns

        return view('admin.manage_student', compact('students', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12',
            'email' => 'required|email|unique:students,email',
            'section_id' => 'required|exists:sections,id', // Validated against sections table
        ]);

        $rawPassword = $request->lrn;
        $validated['password'] = Hash::make($rawPassword); 

        $student = Student::create($validated);

        try {
            Mail::to($student->email)->send(new StudentAccountCreated($student, $rawPassword));
        } catch (\Exception $e) {
            \Log::error('Mail failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Student registered to section successfully!');
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,'.$student->id, 
            'section_id' => 'required|exists:sections,id',
            'new_password' => 'nullable|min:6'
        ]);

        if ($request->filled('new_password')) {
            $validated['password'] = Hash::make($request->new_password);
        }

        $student->update($validated);
        return redirect()->back()->with('success', 'Student record updated!');
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Student deleted.');
    }
}