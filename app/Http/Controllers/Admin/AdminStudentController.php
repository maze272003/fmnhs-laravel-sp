<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Services\StudentLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminStudentController extends Controller
{
    public function __construct(
        private readonly StudentRepositoryInterface $students,
        private readonly StudentLifecycleService $studentLifecycle
    ) {
    }

    /**
     * Display the list of students with Sidebar Logic.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['section_id', 'search', 'school_year_id']);

        $sectionsList = $this->students->getSectionsGroupedWithActiveCounts();
        $students = $this->students->paginateActiveForAdmin($filters, 15);
        $activeSection = $this->students->findSection($request->integer('section_id'));
        $allSections = $this->students->getAllSectionsOrdered();
        $schoolYears = $this->students->getSchoolYearsDesc();
        $activeSchoolYear = $schoolYears->firstWhere('is_active', true)?->school_year;

        return view('admin.manage_student', compact(
            'students',
            'sectionsList',
            'activeSection',
            'allSections',
            'schoolYears',
            'activeSchoolYear'
        ));
    }

    /**
     * View Immutable Student Record (Permanent Record).
     */
    public function show($id)
    {
        $student = $this->students->findWithRecordOrFail((int) $id);

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

        $admin = Auth::guard('admin')->user();
        $this->studentLifecycle->create($validated, $admin);

        return redirect()->back()->with('success', 'Student registered successfully!');
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, $id)
{
    // 1. Find the student first
    $student = $this->students->findOrFail((int) $id);

    // 2. Validate - Notice the unique email rule ignores the current user's ID
    $validated = $request->validate([
        'first_name'      => 'required|string|max:255',
        'last_name'       => 'required|string|max:255',
        'email'           => 'required|email|unique:students,email,' . $student->id,
        'section_id'      => 'required|exists:sections,id',
        'enrollment_type' => 'required|in:Regular,Transferee',
        'new_password'    => 'nullable|min:6', // Optional password update
        'school_year_id'  => 'required|exists:school_year_configs,id',
    ]);

    $admin = Auth::guard('admin')->user();
    
    // 3. The Service handles the password hashing and Audit Logging
    $this->studentLifecycle->update($student, $validated, $admin);

    return redirect()->route('admin.students.index')->with('success', 'Student record updated!');
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

        $admin = Auth::guard('admin')->user();
        $result = $this->studentLifecycle->promote(
            $request->input('student_ids'),
            (int) $request->input('to_school_year_id'),
            $request->filled('to_section_id') ? (int) $request->input('to_section_id') : null,
            $admin
        );

        $message = "Processed successfully.";
        if ($result['promoted_count'] > 0) {
            $message = "{$result['promoted_count']} students promoted to next grade level.";
        }
        if ($result['graduated_count'] > 0) {
            $message = "{$result['graduated_count']} students graduated and moved to Alumni Archives.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Archive a student (Soft Delete).
     */
    public function destroy($id)
    {
        $student = $this->students->findOrFail((int) $id);
        $admin = Auth::guard('admin')->user();
        $this->studentLifecycle->archive($student, $admin);

        return redirect()->back()->with('success', 'Student archived successfully.');
    }

    /**
     * View archived/alumni students.
     */
    public function archived()
    {
        $students = $this->students->archivedPaginated(15);
        return view('admin.archived_students', compact('students'));
    }

    /**
     * Restore student.
     */
    public function restore($id)
    {
        $student = $this->students->findArchivedOrFail((int) $id);
        $admin = Auth::guard('admin')->user();
        $this->studentLifecycle->restore($student, $admin);

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
        $student = $this->students->findOrFail((int) $id);
        $admin = Auth::guard('admin')->user();
        $this->studentLifecycle->changeStatus($student, $newStatus, $admin);
    }

    public function printRecord(Request $request, $id)
{
    // 1. Fetch Student
    $student = $this->students->findWithRecordOrFail((int) $id);

    // 2. Determine School Year to Print
    // CHECK: Did the user click a specific "Print" button for a past year?
    if ($request->has('sy_id')) {
        $targetSyId = $request->integer('sy_id');
    } else {
        // DEFAULT: Latest School Year with grades
        $targetSyId = $student->grades->max('school_year_id');
    }

    $schoolYear = \App\Models\SchoolYearConfig::find($targetSyId);

    if (!$schoolYear) {
        return redirect()->back()->with('error', 'No academic records found to print.');
    }

    // 3. Filter Grades for that SPECIFIC School Year
    $subjects = \App\Models\Subject::whereHas('grades', function($q) use ($student, $targetSyId) {
        $q->where('student_id', $student->id)
          ->where('school_year_id', $targetSyId);
    })->with(['grades' => function($q) use ($student, $targetSyId) {
        $q->where('student_id', $student->id)
          ->where('school_year_id', $targetSyId);
    }])->get();

    // 4. Return View
    return view('admin.print_student_card', compact('student', 'subjects', 'schoolYear'));
}
}
