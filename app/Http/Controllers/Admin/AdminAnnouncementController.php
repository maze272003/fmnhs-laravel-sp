<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnnouncementManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminAnnouncementController extends Controller
{
    public function __construct(private readonly AnnouncementManagementService $announcementManagement)
    {
    }

    /**
     * Display the Bulletin Board.
     */
    public function index(): View
    {
        $announcements = $this->announcementManagement->paginate(5);
        return view('admin.announcement', compact('announcements'));
    }

    /**
     * Store and Broadcast a new announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_audience' => 'required|string|in:all,students,teachers',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480',
        ], [
            'title.required' => 'The announcement title is required.',
            'content.required' => 'The announcement content is required.',
            'target_audience.required' => 'Please select a target audience.',
            'target_audience.in' => 'Invalid target audience selected.',
            'image.mimes' => 'Only JPEG, PNG, GIF, MP4, MOV, and AVI files are allowed.',
            'image.max' => 'The media file must not exceed 40MB.',
        ]);

        $admin = Auth::guard('admin')->user();
        $this->announcementManagement->createForAdmin($validated, $admin?->name ?? 'Admin');

        return back()->with('success', 'Announcement broadcasted successfully to all students!');
    }

    /**
     * Remove the announcement and its media.
     */
    public function destroy($announcement): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $allowedEmails = [
            'admin@school.com',
            'sangbaanstephaniemary@gmail.com',
        ];

        if (!$admin || !in_array($admin->email, $allowedEmails, true)) {
            return back()->with('error', 'You are not authorized to delete this announcement.');
        }

        $this->announcementManagement->deleteById((int) $announcement);

        return back()->with('success', 'Announcement has been retracted and media deleted.');
    }
}
