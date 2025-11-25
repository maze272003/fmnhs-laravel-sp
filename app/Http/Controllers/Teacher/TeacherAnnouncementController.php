<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class TeacherAnnouncementController extends Controller
{
    public function index()
    {
        // Teacher sees only their own posts? Or all? Let's show all for now.
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(5);
        return view('teacher.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'content' => 'required']);

        $teacher = Auth::guard('teacher')->user();

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'author_name' => 'Teacher ' . $teacher->last_name,
            'role' => 'teacher' // Used for styling (e.g. Green badge)
        ]);

        return back()->with('success', 'Announcement posted!');
    }
}