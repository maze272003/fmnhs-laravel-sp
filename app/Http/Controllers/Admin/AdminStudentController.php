<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Section;
use App\Models\PromotionHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\StudentAccountCreated;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
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

        if ($request->filled('school_year')) {
            $query->where('school_year', $request->input('school_year'));
        }

        $students = $query->orderBy('last_name')->paginate(10);
        $sections = Section::all();

        // Get distinct school years for filter dropdown
        $schoolYears = Student::select('school_year')->distinct()->orderBy('school_year', 'desc')->pluck('school_year');

        return view('admin.manage_student', compact('students', 'sections', 'schoolYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12',
            'email' => 'required|email|unique:students,email',
            'section_id' => 'required|exists:sections,id',
            'enrollment_type' => 'required|in:Regular,Transferee',
            'school_year' => 'required|string|max:20',
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
            'enrollment_type' => 'required|in:Regular,Transferee',
            'new_password' => 'nullable|min:6'
        ]);

        if ($request->filled('new_password')) {
            $validated['password'] = Hash::make($request->new_password);
        }

        $student->update($validated);
        return redirect()->back()->with('success', 'Student record updated!');
    }

    /**
     * Soft delete (archive) a student instead of hard delete.
     * Student data remains stored for reference and audit.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete(); // SoftDeletes — student is archived, not removed
        return redirect()->back()->with('success', 'Student has been archived successfully. Records are preserved for reference.');
    }

    /**
     * Show archived (soft-deleted) students.
     */
    public function archived()
    {
        $students = Student::onlyTrashed()->with('section')->orderBy('deleted_at', 'desc')->paginate(10);
        return view('admin.archived_students', compact('students'));
    }

    /**
     * Restore a soft-deleted student.
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        return redirect()->back()->with('success', 'Student has been restored to active enrollment.');
    }

    /**
     * Promote students to the next grade level.
     * Creates a new school-year record and preserves previous data.
     */
    public function promote(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'to_section_id' => 'required|exists:sections,id',
            'to_school_year' => 'required|string|max:20',
        ]);

        $toSection = Section::findOrFail($request->to_section_id);
        $admin = Auth::guard('admin')->user();
        $promoted = 0;

        foreach ($request->student_ids as $studentId) {
            $student = Student::findOrFail($studentId);
            $fromSection = $student->section;

            // Record promotion history
            PromotionHistory::create([
                'student_id' => $student->id,
                'from_grade_level' => $fromSection->grade_level,
                'to_grade_level' => $toSection->grade_level,
                'from_school_year' => $student->school_year,
                'to_school_year' => $request->to_school_year,
                'from_section_id' => $fromSection->id,
                'to_section_id' => $toSection->id,
                'promoted_by' => $admin->name ?? 'Admin',
            ]);

            // Update student to new section and school year
            $student->update([
                'section_id' => $toSection->id,
                'school_year' => $request->to_school_year,
                'enrollment_type' => 'Regular',
            ]);

            $promoted++;
        }

        return redirect()->back()->with('success', "{$promoted} student(s) promoted successfully to Grade {$toSection->grade_level} for SY {$request->to_school_year}.");
    }
}
