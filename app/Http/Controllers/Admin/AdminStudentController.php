<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Section;
use App\Models\PromotionHistory;
use App\Models\AuditTrail;
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
        $validated['enrollment_status'] = 'Enrolled';

        $student = Student::create($validated);

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $student->id, 'created',
            null, null, $student->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

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

        // Prevent editing alumni records
        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records are read-only and cannot be modified.']);
        }

        $oldData = $student->toArray();

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

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $student->id, 'updated',
            null, $oldData, $student->fresh()->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', 'Student record updated!');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // Prevent archiving alumni
        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records cannot be archived.']);
        }

        $student->update(['enrollment_status' => 'Archived']);
        $student->delete();

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $id, 'archived',
            'enrollment_status', 'Enrolled', 'Archived',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', 'Student has been archived successfully. Records are preserved for reference.');
    }

    public function archived()
    {
        $students = Student::onlyTrashed()->with('section')->orderBy('deleted_at', 'desc')->paginate(10);
        return view('admin.archived_students', compact('students'));
    }

    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        $student->restore();
        $student->update(['enrollment_status' => 'Enrolled']);

        // Audit trail
        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $id, 'restored',
            'enrollment_status', 'Archived', 'Enrolled',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', 'Student has been restored to active enrollment.');
    }

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
        $alumniCount = 0;

        foreach ($request->student_ids as $studentId) {
            $student = Student::findOrFail($studentId);

            // Prevent downgrading alumni
            if ($student->is_alumni) {
                continue;
            }

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

            // Grade 12 → Alumni automatically
            $isGraduating = $fromSection->grade_level == 12;

            $updateData = [
                'section_id' => $toSection->id,
                'school_year' => $request->to_school_year,
                'enrollment_type' => 'Regular',
                'enrollment_status' => $isGraduating ? 'Alumni' : 'Promoted',
                'is_alumni' => $isGraduating,
            ];

            $student->update($updateData);

            // Audit trail
            AuditTrail::log(
                'Student', $student->id,
                $isGraduating ? 'graduated' : 'promoted',
                'grade_level',
                $fromSection->grade_level,
                $isGraduating ? 'Alumni' : $toSection->grade_level,
                'admin', $admin->id ?? null, $admin->name ?? 'Admin'
            );

            if ($isGraduating) {
                $alumniCount++;
            }
            $promoted++;
        }

        $message = "{$promoted} student(s) promoted successfully to Grade {$toSection->grade_level} for SY {$request->to_school_year}.";
        if ($alumniCount > 0) {
            $message .= " {$alumniCount} student(s) marked as Alumni (Grade 12 graduates).";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Update enrollment status to Dropped.
     */
    public function dropStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records cannot be modified.']);
        }

        if ($student->enrollment_status === 'Dropped') {
            return redirect()->back()->withErrors(['error' => 'Student is already dropped.']);
        }

        $oldStatus = $student->enrollment_status;
        $student->update(['enrollment_status' => 'Dropped']);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $student->id, 'updated',
            'enrollment_status', $oldStatus, 'Dropped',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', "Student {$student->first_name} {$student->last_name} has been marked as Dropped.");
    }

    /**
     * Update enrollment status to Transferred.
     */
    public function transferStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records cannot be modified.']);
        }

        if ($student->enrollment_status === 'Transferred') {
            return redirect()->back()->withErrors(['error' => 'Student is already marked as transferred.']);
        }

        $oldStatus = $student->enrollment_status;
        $student->update(['enrollment_status' => 'Transferred']);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $student->id, 'updated',
            'enrollment_status', $oldStatus, 'Transferred',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', "Student {$student->first_name} {$student->last_name} has been marked as Transferred.");
    }

    /**
     * Re-enroll a dropped or transferred student.
     */
    public function reenrollStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records cannot be re-enrolled.']);
        }

        if (!in_array($student->enrollment_status, ['Dropped', 'Transferred'])) {
            return redirect()->back()->withErrors(['error' => 'Only dropped or transferred students can be re-enrolled.']);
        }

        $oldStatus = $student->enrollment_status;
        $student->update(['enrollment_status' => 'Enrolled']);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Student', $student->id, 'updated',
            'enrollment_status', $oldStatus, 'Enrolled',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', "Student {$student->first_name} {$student->last_name} has been re-enrolled.");
    }
}
