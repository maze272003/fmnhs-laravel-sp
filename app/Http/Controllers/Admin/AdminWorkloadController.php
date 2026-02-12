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
        $workloads = $this->workloadService->getOverview();

        return view('admin.workload.index', compact('workloads'));
    }

    /**
     * Teacher workload detail.
     */
    public function teacherDetail(Teacher $teacher): View
    {
        $detail = $this->workloadService->getTeacherDetail($teacher);

        return view('admin.workload.teacher-detail', compact('teacher', 'detail'));
    }

    /**
     * Export workload report.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $validated = $request->validate([
            'format' => ['sometimes', 'string', 'in:csv,xlsx,pdf'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $path = $this->workloadService->exportReport($validated);

        return response()->download($path);
    }
}
