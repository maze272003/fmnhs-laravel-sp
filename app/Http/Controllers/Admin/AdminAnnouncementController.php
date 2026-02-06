<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Student;
use App\Mail\AnnouncementMail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminAnnouncementController extends Controller
{
    /**
     * Display the Bulletin Board.
     */
    public function index(): View
    {
        $announcements = Announcement::latest()->paginate(5);
        return view('admin.announcement', compact('announcements'));
    }

    /**
     * Store and Broadcast a new announcement.
     */
    public function store(Request $request): RedirectResponse
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

        $imagePath = null;

        // Media Handling (S3)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            
            // Upload to S3 with Public visibility
            $imagePath = Storage::disk('s3')->putFileAs('announcements', $file, $filename, 'public');
        }

        // 1. Save to Database
        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'author_name' => Auth::guard('admin')->user()->name, 
            'role' => 'admin',
            'target_audience' => $request->target_audience,
        ]);

        // 2. Broadcast via Queued Email
        // Ginagamit natin ang chunking para hindi ma-overload ang mail server
        Student::whereNotNull('email')->chunk(50, function ($students) use ($announcement) {
            foreach ($students as $student) {
                try {
                    // .queue() ang gagamitin sa halip na .send() para mabilis ang response
                    Mail::to($student->email)->queue(new AnnouncementMail($announcement));
                } catch (\Exception $e) {
                    Log::error("Mail queue failed for {$student->email}: " . $e->getMessage());
                }
            }
        });

        return back()->with('success', 'Announcement broadcasted successfully to all students!');
    }

    /**
     * Remove the announcement and its media.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        // ✅ Hardcoded allowed admin emails
        $allowedEmails = [
            'admin@school.com',
            'sangbaanstephaniemary@gmail.com',
        ];

        if (!$admin || !in_array($admin->email, $allowedEmails, true)) {
            return back()->with('error', 'You are not authorized to delete this announcement.');
        }

        // 🗑️ Delete image from S3 if exists
        if ($announcement->image && Storage::disk('s3')->exists($announcement->image)) {
            Storage::disk('s3')->delete($announcement->image);
        }

        $announcement->delete();

        return back()->with('success', 'Announcement has been retracted and media deleted.');
    }
}
