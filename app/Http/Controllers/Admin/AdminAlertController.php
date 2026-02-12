<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterventionAlert;
use App\Services\AtRiskDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAlertController extends Controller
{
    public function __construct(
        private readonly AtRiskDetectionService $atRiskService,
    ) {}

    /**
     * List intervention alerts.
     */
    public function index(): View
    {
        $alerts = InterventionAlert::with('student')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.alerts.index', compact('alerts'));
    }

    /**
     * Show alert detail.
     */
    public function show(InterventionAlert $alert): View
    {
        $alert->load('student');

        return view('admin.alerts.show', compact('alert'));
    }

    /**
     * Resolve an alert.
     */
    public function resolve(Request $request, InterventionAlert $alert): RedirectResponse
    {
        $resolver = Auth::guard('admin')->user();

        $this->atRiskService->resolveAlert($alert, $resolver);

        return redirect()
            ->route('admin.alerts.show', $alert)
            ->with('success', 'Alert resolved successfully.');
    }

    /**
     * Alert statistics page.
     */
    public function settings(): View
    {
        $statistics = $this->atRiskService->getAlertStatistics();

        return view('admin.alerts.settings', compact('statistics'));
    }
}
