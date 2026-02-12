<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ProgressReport;
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
            'student_id' => ['nullable', 'exists:students,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'type' => ['required', 'string', 'in:individual,class,summary'],
            'period' => ['required', 'string'],
        ]);

        $validated['teacher_id'] = Auth::guard('teacher')->id();

        try {
            $report = $this->reportService->generate($validated);

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
            $path = $this->reportService->download($report);

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
            'section_id' => ['required', 'exists:sections,id'],
            'frequency' => ['required', 'string', 'in:weekly,monthly,quarterly'],
            'type' => ['required', 'string', 'in:individual,class,summary'],
        ]);

        $validated['teacher_id'] = Auth::guard('teacher')->id();

        try {
            $this->reportService->scheduleReport($validated);

            return redirect()
                ->route('teacher.reports.index')
                ->with('success', 'Report schedule created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to schedule report: '.$e->getMessage());
        }
    }
}
