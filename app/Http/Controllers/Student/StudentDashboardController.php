<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement; // Import Announcement Model
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        // 1. Fetch latest 5 announcements
        $announcements = Announcement::latest()->take(5)->get();

        // 2. Return the view with data
        return view('student.dashboard', compact('announcements'));
    }
}