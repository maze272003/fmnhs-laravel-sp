<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Services\WorkloadTrackingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminWorkloadController extends Controller
{
    public function __construct(
        private readonly WorkloadTrackingService $workloadService,
    ) {}

    /**
     * Workload overview dashboard.
     */
    public function index(): View
    {
        $distribution = $this->workloadService->getWorkloadDistribution();
        $overloaded = $this->workloadService->identifyOverloadedTeachers();

        return view('admin.workload.index', compact('distribution', 'overloaded'));
    }

    /**
     * Teacher workload detail.
     */
    public function teacherDetail(Teacher $teacher): View
    {
        $metrics = $this->workloadService->getWorkloadMetrics($teacher);

        return view('admin.workload.teacher-detail', compact('teacher', 'metrics'));
    }

    /**
     * Export workload report.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $teacher = Teacher::findOrFail($validated['teacher_id']);
        $path = $this->workloadService->generateWorkloadReport($teacher);

        return response()->download($path);
    }
}
