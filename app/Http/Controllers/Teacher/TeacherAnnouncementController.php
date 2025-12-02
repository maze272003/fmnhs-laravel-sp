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
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(5);
        return view('teacher.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        // 1. Validate including image
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048'
        ]);

        $teacher = Auth::guard('teacher')->user();
        $imagePath = null;

        // 2. Image Upload Logic (Copied from Admin)
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // DOUBLE PATH: Local vs Production Logic
            if (app()->environment('local')) {
                // LOCAL — use storage/app/public (symlink)
                $imagePath = $file->storeAs('announcements', $filename, 'public');
            } else {
                // PRODUCTION — direct to public/uploads
                $destinationPath = public_path('uploads/announcements');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);

                // match format with storage path
                $imagePath = 'announcements/' . $filename;
            }
        }

        // 3. Create Announcement
        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath, // Save the path
            'author_name' => 'Teacher ' . $teacher->last_name,
            'role' => 'teacher' 
        ]);

        return back()->with('success', 'Announcement posted!');
    }
}