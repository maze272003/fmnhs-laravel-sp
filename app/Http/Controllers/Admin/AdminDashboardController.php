<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardAnalyticsService;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly DashboardAnalyticsService $dashboardAnalytics)
    {
    }

    public function index()
    {
        return view('admin.dashboard', $this->dashboardAnalytics->getAdminDashboardData());
    }
}
