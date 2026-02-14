<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ReportGenerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProgressReportController extends Controller
{
    public function __construct(
        private readonly ReportGenerationService $reportService,
    ) {}

    /**
     * List progress reports.
     */
    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $reports = ProgressReport::where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('teacher.reports.index', compact('reports'));
    }

    /**
     * Generate a new progress report.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        try {
            $student = Student::findOrFail($validated['student_id']);
            $report = $this->reportService->generateProgressReport(
                $student,
                $validated['period_start'],
                $validated['period_end']
            );

            return redirect()
                ->route('teacher.reports.preview', $report)
                ->with('success', 'Report generated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to generate report: '.$e->getMessage());
        }
    }

    /**
     * Preview a report.
     */
    public function preview(ProgressReport $report): View
    {
        return view('teacher.reports.preview', compact('report'));
    }

    /**
     * Download a report.
     */
    public function download(ProgressReport $report): BinaryFileResponse|RedirectResponse
    {
        try {
            $path = $this->reportService->generatePDF($report);

            return response()->download($path);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to download report: '.$e->getMessage());
        }
    }

    /**
     * Schedule recurring report generation.
     */
    public function schedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'frequency' => ['required', 'string', 'in:weekly,monthly,quarterly'],
        ]);

        try {
            $teacher = Teacher::findOrFail(Auth::guard('teacher')->id());
            $this->reportService->scheduleReports($teacher, $validated['frequency']);

            return redirect()
                ->route('teacher.reports.index')
                ->with('success', 'Report schedule created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to schedule report: '.$e->getMessage());
        }
    }

    /**
     * Show a progress report.
     */
    public function show(ProgressReport $report): View
    {
        return $this->preview($report);
    }

    /**
     * Send a progress report.
     */
    public function send(Request $request, ProgressReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'recipients' => ['required', 'array'],
            'recipients.*' => ['string', 'email'],
        ]);

        try {
            $this->reportService->sendReport($report, $validated['recipients']);

            return redirect()
                ->route('teacher.reports.index')
                ->with('success', 'Report sent successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to send report: '.$e->getMessage());
        }
    }
}
