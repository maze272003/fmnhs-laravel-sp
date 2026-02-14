<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Student;
use App\Services\AnalyticsAggregationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsAggregationService $analyticsService,
    ) {}

    /**
     * Analytics dashboard.
     */
    public function index(): View
    {
        return view('admin.analytics.index');
    }

    /**
     * Student performance analytics.
     */
    public function studentPerformance(Student $student): View
    {
        $performance = $this->analyticsService->getStudentPerformance($student);

        return view('admin.analytics.student', compact('student', 'performance'));
    }

    /**
     * Class/section performance analytics.
     */
    public function classPerformance(Section $section): View
    {
        $performance = $this->analyticsService->getClassPerformance($section);

        return view('admin.analytics.class', compact('section', 'performance'));
    }

    /**
     * Export analytics report.
     */
    public function exportReport(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:student,class,overall'],
            'format' => ['sometimes', 'string', 'in:csv,xlsx,pdf'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $path = $this->analyticsService->exportReport($validated);

        return response()->download($path);
    }

    /**
     * Students analytics overview.
     */
    public function students(): View
    {
        $analytics = $this->analyticsService->getStudentsAnalytics();

        return view('admin.analytics.students', compact('analytics'));
    }

    /**
     * Teachers analytics overview.
     */
    public function teachers(): View
    {
        $analytics = $this->analyticsService->getTeachersAnalytics();

        return view('admin.analytics.teachers', compact('analytics'));
    }
}
