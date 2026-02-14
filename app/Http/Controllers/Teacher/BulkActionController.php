<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Section;
use App\Services\BulkActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BulkActionController extends Controller
{
    public function __construct(
        private readonly BulkActionService $bulkActionService,
    ) {}

    /**
     * Show grade entry form.
     */
    public function gradeEntry(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        return view('teacher.bulk.grade-entry', compact('teacherId'));
    }

    /**
     * Process bulk grade entry.
     */
    public function processGradeEntry(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $this->bulkActionService->bulkGradeEntry($validated['grades']);

            return redirect()
                ->back()
                ->with('success', 'Grades saved successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to save grades: '.$e->getMessage());
        }
    }

    /**
     * Show email composer form.
     */
    public function emailComposer(): View
    {
        return view('teacher.bulk.email-composer');
    }

    /**
     * Send bulk emails.
     */
    public function sendBulkEmail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_ids' => ['required', 'array'],
            'recipient_ids.*' => ['integer'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        try {
            $this->bulkActionService->bulkEmail(
                $validated['recipient_ids'],
                $validated['subject'],
                $validated['body']
            );

            return redirect()
                ->back()
                ->with('success', 'Emails sent successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to send emails: '.$e->getMessage());
        }
    }

    /**
     * Show import/export page.
     */
    public function importExport(): View
    {
        return view('teacher.bulk.import-export');
    }

    /**
     * Process data import.
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
            'type' => ['required', 'string', 'in:grades,attendance,students'],
        ]);

        try {
            $result = $this->bulkActionService->importFromCSV(
                $request->file('file'),
                $request->input('type')
            );

            return redirect()
                ->back()
                ->with('success', "Import completed: {$result['imported']} records processed.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /**
     * Process data export.
     */
    public function processExport(Request $request): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:grades,attendance,students'],
            'section_id' => ['nullable', 'exists:sections,id'],
        ]);

        $filters = [];
        if (! empty($validated['section_id'])) {
            $filters['section_id'] = $validated['section_id'];
        }

        try {
            $path = $this->bulkActionService->exportToCSV($validated['type'], $filters);

            return response()->download($path);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Export failed: '.$e->getMessage());
        }
    }

    /**
     * Bulk grade entry.
     */
    public function bulkGrades(Request $request): RedirectResponse
    {
        return $this->processGradeEntry($request);
    }

    /**
     * Bulk attendance marking.
     */
    public function bulkAttendance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*.student_id' => ['required', 'exists:students,id'],
            'attendance.*.status' => ['required', 'string', 'in:present,absent,late,excused'],
            'attendance.*.date' => ['required', 'date'],
        ]);

        try {
            $this->bulkActionService->bulkAttendanceImport($validated['attendance']);

            return redirect()
                ->back()
                ->with('success', 'Attendance recorded successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to record attendance: '.$e->getMessage());
        }
    }

    /**
     * Duplicate assignments to other sections.
     */
    public function duplicateAssignments(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'assignment_id' => ['required', 'exists:assignments,id'],
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['exists:sections,id'],
        ]);

        try {
            $assignment = Assignment::findOrFail($validated['assignment_id']);
            $sections = Section::whereIn('id', $validated['section_ids'])->get();

            $this->bulkActionService->bulkAssignmentDuplicate($assignment, $sections);

            return redirect()
                ->back()
                ->with('success', 'Assignments duplicated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to duplicate assignments: '.$e->getMessage());
        }
    }
}
