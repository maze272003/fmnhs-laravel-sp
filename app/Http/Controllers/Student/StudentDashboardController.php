<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(): View
    {
        // Fetch student with section and advisor relationship
        $student = Auth::guard('student')->user()->load('section.advisor');

        // Fetch latest announcements
        $announcements = Announcement::latest()->take(5)->get();

        // We can pass the advisor directly or access it via $student in the view
        $advisor = $student->section->advisor ?? null;

        return view('student.dashboard', compact('announcements', 'advisor'));
    }
}