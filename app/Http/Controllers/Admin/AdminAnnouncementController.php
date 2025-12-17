<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <--- IMPORTANT: Import Storage Facade
use Illuminate\Support\Str;
use App\Models\Student; // IMPORT THIS
use App\Mail\AnnouncementMail; // IMPORT THIS
use Illuminate\Support\Facades\Mail; // IMPORT THIS

class AdminAnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(5);
        return view('admin.announcement', compact('announcements'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            // Upload to S3
            $path = Storage::disk('s3')->putFileAs('announcements', $file, $filename);
            
            // IMPORTANT: Set visibility to public para ma-access ng students sa email
            Storage::disk('s3')->setVisibility($path, 'public'); 
            
            $imagePath = $path;
        }

        // 1. Save Announcement
        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'author_name' => 'Admin System',
            'role' => 'admin'
        ]);

        // 2. Send Email to ALL Students (Background Queue is recommended for many students)
        // Kunin lang ang mga students na may valid email
        $students = Student::whereNotNull('email')->get();

        foreach ($students as $student) {
            // Gamit ang Mail Facade
            // Kung marami kang students (e.g. 500+), mas maganda gumamit ng Queue (Mail::to()->queue())
            // Pero sa ngayon, direct send muna:
            try {
                Mail::to($student->email)->send(new AnnouncementMail($announcement));
            } catch (\Exception $e) {
                // Log error kung may fail pero wag itigil ang loop
                \Log::error("Failed sending email to " . $student->email . ": " . $e->getMessage());
            }
        }

        return back()->with('success', 'Announcement posted and emails sent!');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        // S3 CLEANUP:
        // Delete the file from the bucket if it exists to save space/cost
        if ($announcement->image) {
            Storage::disk('s3')->delete($announcement->image);
        }

        $announcement->delete();
        
        return back()->with('success', 'Announcement deleted.');
    }
}