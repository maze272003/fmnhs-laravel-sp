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
        // 1. UPDATE VALIDATION: Added video mimes (mp4, mov, avi) & increased max size to 20MB
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            // Note: 'image' parin ang input name natin kahit video ang laman para di na magbago ang DB column
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40000' 
        ]);

        $teacher = Auth::guard('teacher')->user();
        $mediaPath = null;

        // 2. Upload Logic (mga file handling)
        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // DOUBLE PATH: Local vs Production Logic
            if (app()->environment('local')) {
                // LOCAL
                $mediaPath = $file->storeAs('announcements', $filename, 'public');
            } else {
                // PRODUCTION (Hostinger/cPanel)
                $destinationPath = public_path('uploads/announcements');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $file->move($destinationPath, $filename);
                $mediaPath = 'announcements/' . $filename;
            }
        }

        // 3. Create Announcement
        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $mediaPath, // Saving path (image or video)
            'author_name' => 'Teacher ' . $teacher->last_name,
            'role' => 'teacher' 
        ]);

        return back()->with('success', 'Announcement posted!');
    }
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return back()->with('success', 'Announcement deleted successfully!');
    }
}