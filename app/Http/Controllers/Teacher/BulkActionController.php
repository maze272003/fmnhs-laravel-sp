<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
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
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'grades' => ['required', 'array'],
            'grades.*.student_id' => ['required', 'exists:students,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $this->bulkActionService->processGradeEntry($validated);

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
            'recipient_type' => ['required', 'string', 'in:students,parents,section'],
            'recipient_ids' => ['required', 'array'],
            'recipient_ids.*' => ['integer'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        try {
            $this->bulkActionService->sendBulkEmail($validated);

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
            $result = $this->bulkActionService->processImport(
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
            'format' => ['sometimes', 'string', 'in:csv,xlsx'],
        ]);

        try {
            $path = $this->bulkActionService->processExport($validated);

            return response()->download($path);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Export failed: '.$e->getMessage());
        }
    }
}
