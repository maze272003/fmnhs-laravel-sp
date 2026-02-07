<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TeacherAnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementManagementService $announcementManagement)
    {
    }

    public function index()
    {
        $announcements = $this->announcementManagement->paginate(5);
        return view('teacher.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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

        $validated['image'] = $request->file('image');
        $teacher = Auth::guard('teacher')->user();
        $this->announcementManagement->createForTeacher(
            $validated,
            'Teacher ' . ($teacher->last_name ?? 'Faculty')
        );

        // Check if request is AJAX/Axios
        if ($request->expectsJson()) {
            session()->flash('success', 'Announcement posted successfully!');
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Announcement posted successfully to the board!');
    }

    public function destroy($id)
    {
        $this->announcementManagement->deleteById((int) $id);

        return back()->with('success', 'Announcement and its media have been deleted!');
    }
}
