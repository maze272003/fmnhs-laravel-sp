<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __construct(private readonly AnnouncementManagementService $announcementManagement)
    {
    }

    public function index(): View
    {
        // Fetch student with section and advisor relationship
        $student = Auth::guard('student')->user()->load('section.advisor');

        // Fetch latest announcements
        $announcements = $this->announcementManagement->latest(5);

        // We can pass the advisor directly or access it via $student in the view
        $advisor = $student->section->advisor ?? null;

        return view('student.dashboard', compact('announcements', 'advisor'));
    }
}
