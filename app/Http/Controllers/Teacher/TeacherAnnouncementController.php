<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <--- Import Storage
use Illuminate\Support\Str;              // <--- Import Str for filenames

class TeacherAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(5);
        return view('teacher.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|string|in:all,students,teachers',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480' 
        ], [
            'title.required' => 'The announcement title is required.',
            'content.required' => 'The announcement content is required.',
            'target_audience.required' => 'Please select a target audience.',
            'target_audience.in' => 'Invalid target audience selected.',
            'image.mimes' => 'Only JPEG, PNG, GIF, MP4, MOV, and AVI files are allowed.',
            'image.max' => 'The media file must not exceed 40MB.',
        ]);

    $teacher = Auth::guard('teacher')->user();
    $mediaPath = null;

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = Storage::disk('s3')->putFileAs('announcements', $file, $filename);
        $mediaPath = $path;
    }

    Announcement::create([
        'title' => $request->title,
        'content' => $request->content,
        'image' => $mediaPath, 
        'author_name' => 'Teacher ' . $teacher->last_name,
        'role' => 'teacher',
        'target_audience' => $request->target_audience,
    ]);

    // Check if request is AJAX/Axios
    if ($request->expectsJson()) {
        session()->flash('success', 'Announcement posted successfully!');
        return response()->json(['success' => true]);
    }

    return back()->with('success', 'Announcement posted successfully to the board!');
}

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        // 4. S3 CLEANUP: Burahin ang file sa S3 bucket bago i-delete ang record
        if ($announcement->image) {
            if (Storage::disk('s3')->exists($announcement->image)) {
                Storage::disk('s3')->delete($announcement->image);
            }
        }

        $announcement->delete();

        return back()->with('success', 'Announcement and its media have been deleted!');
    }
}