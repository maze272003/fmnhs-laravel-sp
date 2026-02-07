<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Section;
use App\Models\PromotionHistory;
use App\Models\SchoolYearConfig;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\StudentAccountCreated;
use Illuminate\Support\Facades\DB;

class AdminStudentController extends Controller
{
    /**
     * Display the list of students with Sidebar Logic.
     */
    public function index(Request $request)
    {
        // 1. Sidebar Data: Get Sections grouped by Grade Level with active student counts
        $sectionsList = Section::withCount(['students' => function($q) {
            $q->where('is_alumni', false)->whereNull('deleted_at');
        }])
        ->orderBy('grade_level')
        ->orderBy('name')
        ->get()
        ->groupBy('grade_level');

        // 2. Main Query for Student Table
        $query = Student::with(['section', 'schoolYearConfig'])
            ->where('is_alumni', false); // Exclude Alumni from active lists

        // 3. Filter by Specific Section (When clicking sidebar items)
        $activeSection = null;
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->input('section_id'));
            $activeSection = Section::find($request->input('section_id'));
        }

        // 4. Search Logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('lrn', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // 5. School Year Filter
        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->input('school_year_id'));
        }

        $students = $query->orderBy('last_name')->paginate(15);
        
        // Data for Modals
        $allSections = Section::orderBy('grade_level')->orderBy('name')->get();
        $schoolYears = SchoolYearConfig::orderBy('school_year', 'desc')->get();

        return view('admin.manage_student', compact(
            'students', 
            'sectionsList', 
            'activeSection', 
            'allSections', 
            'schoolYears'
        ));
    }

    /**
     * View Immutable Student Record (Permanent Record).
     */
    public function show($id)
    {
        // Fetch student with all academic history, including soft deleted ones (Alumni)
        $student = Student::with(['grades.subject', 'promotionHistory', 'schoolYearConfig'])
            ->withTrashed()
            ->findOrFail($id);

        return view('admin.student_record', compact('student'));
    }

    /**
     * Store a newly created student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12',
            'email' => 'required|email|unique:students,email',
            'section_id' => 'required|exists:sections,id',
            'enrollment_type' => 'required|in:Regular,Transferee',
            'school_year_id' => 'required|exists:school_year_configs,id',
        ]);

        $rawPassword = $request->lrn;
        $validated['password'] = Hash::make($rawPassword);
        $validated['enrollment_status'] = 'Enrolled';
        $validated['is_alumni'] = false;

        $student = Student::create($validated);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log('Student', $student->id, 'created', null, null, $student->toArray(), 'admin', $admin->id ?? null);

        try {
            Mail::to($student->email)->send(new StudentAccountCreated($student, $rawPassword));
        } catch (\Exception $e) {
            \Log::error('Mail failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Student registered successfully!');
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records are read-only.']);
        }

        $oldData = $student->toArray();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'section_id' => 'required|exists:sections,id',
            'enrollment_type' => 'required|in:Regular,Transferee',
            'new_password' => 'nullable|min:6',
            'school_year_id' => 'sometimes|exists:school_year_configs,id',
        ]);

        if ($request->filled('new_password')) {
            $validated['password'] = Hash::make($request->new_password);
        }

        $student->update($validated);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log('Student', $student->id, 'updated', null, $oldData, $student->fresh()->toArray(), 'admin', $admin->id ?? null);

        return redirect()->back()->with('success', 'Student record updated!');
    }

    /**
     * Promote students (Grade Up) or Graduate them (Alumni).
     */
    public function promote(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'to_school_year_id' => 'required|exists:school_year_configs,id',
            // Nullable because graduating students don't need a section
            'to_section_id' => 'nullable|exists:sections,id', 
        ]);

        $toSchoolYearConfig = SchoolYearConfig::find($request->to_school_year_id);
        $admin = Auth::guard('admin')->user();
        
        $promotedCount = 0;
        $graduatedCount = 0;

        foreach ($request->student_ids as $studentId) {
            $student = Student::findOrFail($studentId);

            if ($student->is_alumni) continue;

            $fromSection = $student->section;
            // LOGIC: If currently Grade 12, they become Alumni
            $isGraduating = $fromSection->grade_level == 12;

            // Validation: Non-graduating students MUST have a section
            if (!$isGraduating && !$request->to_section_id) {
                return back()->withErrors(['error' => 'Regular promotion requires a destination section.']);
            }

            // 1. Archive History
            PromotionHistory::create([
                'student_id' => $student->id,
                'from_grade_level' => (string)$fromSection->grade_level,
                // If graduating, new grade is "Alumni", else it is current + 1
                'to_grade_level' => $isGraduating ? 'Alumni' : (string)($fromSection->grade_level + 1),
                
                'from_school_year' => $student->schoolYearConfig->school_year ?? 'N/A',
                'to_school_year' => $toSchoolYearConfig->school_year,
                
                'from_section_id' => $fromSection->id,
                'to_section_id' => $isGraduating ? null : $request->to_section_id,
                'promoted_by' => $admin->name ?? 'Admin',
            ]);

            // 2. Update Student Data
            $student->update([
                'school_year_id' => $toSchoolYearConfig->id,
                'enrollment_type' => 'Regular',
                'enrollment_status' => $isGraduating ? 'Alumni' : 'Promoted',
                'is_alumni' => $isGraduating,
                // Remove from section if graduated, otherwise assign new section
                'section_id' => $isGraduating ? null : $request->to_section_id,
            ]);

            // 3. Log Audit
            AuditTrail::log(
                'Student', $student->id,
                $isGraduating ? 'graduated' : 'promoted',
                'grade_level',
                $fromSection->grade_level,
                $isGraduating ? 'Alumni' : ($fromSection->grade_level + 1),
                'admin', $admin->id ?? null
            );

            if ($isGraduating) $graduatedCount++;
            else $promotedCount++;
        }

        $message = "Processed successfully.";
        if ($promotedCount > 0) $message = "$promotedCount students promoted to next grade level.";
        if ($graduatedCount > 0) $message = "$graduatedCount students graduated and moved to Alumni Archives.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Archive a student (Soft Delete).
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->is_alumni) {
            return redirect()->back()->withErrors(['error' => 'Alumni records are permanent and cannot be archived manually.']);
        }

        $student->update(['enrollment_status' => 'Archived']);
        $student->delete();

        $admin = Auth::guard('admin')->user();
        AuditTrail::log('Student', $id, 'archived', 'status', 'Enrolled', 'Archived', 'admin', $admin->id ?? null);

        return redirect()->back()->with('success', 'Student archived successfully.');
    }

    /**
     * View archived/alumni students.
     */
    public function archived()
    {
        $students = Student::onlyTrashed()->with('section')->orderBy('deleted_at', 'desc')->paginate(15);
        return view('admin.archived_students', compact('students'));
    }

    /**
     * Restore student.
     */
    public function restore($id)
    {
        $student = Student::onlyTrashed()->findOrFail($id);
        
        // If restoring an Alumni, keep them as Alumni? Or revert?
        // Usually, we revert them to 'Enrolled' if it was a mistake.
        $student->restore();
        $student->update(['enrollment_status' => 'Enrolled', 'is_alumni' => false]);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log('Student', $id, 'restored', 'status', 'Archived', 'Enrolled', 'admin', $admin->id ?? null);

        return redirect()->back()->with('success', 'Student record restored.');
    }

    // Status Management Methods
    public function dropStudent(Request $request, $id)
    {
        $this->changeStatus($id, 'Dropped');
        return redirect()->back()->with('success', 'Student marked as Dropped.');
    }

    public function transferStudent(Request $request, $id)
    {
        $this->changeStatus($id, 'Transferred');
        return redirect()->back()->with('success', 'Student marked as Transferred.');
    }

    public function reenrollStudent(Request $request, $id)
    {
        $this->changeStatus($id, 'Enrolled');
        return redirect()->back()->with('success', 'Student re-enrolled.');
    }

    /**
     * Helper for status changes to reduce duplication
     */
    private function changeStatus($id, $newStatus)
    {
        $student = Student::findOrFail($id);
        if ($student->is_alumni) abort(403, 'Cannot modify Alumni records.');
        
        $oldStatus = $student->enrollment_status;
        $student->update(['enrollment_status' => $newStatus]);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log('Student', $student->id, 'status_change', 'status', $oldStatus, $newStatus, 'admin', $admin->id ?? null);
    }
}