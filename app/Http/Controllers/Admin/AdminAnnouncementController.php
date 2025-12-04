<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // <--- IMPORTANT: Import Storage Facade
use Illuminate\Support\Str;

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

            // 1. Create a clean, custom filename
            // Example: "no-classes-on-monday-173335600.jpg"
            // We use time() to make sure it's always unique even if titles are same
            $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();

            // 2. Use putFileAs to upload with YOUR specific name
            // Syntax: putFileAs(folder_name, file_object, custom_filename)
            $path = Storage::disk('s3')->putFileAs('announcements', $file, $filename);
            
            // $path now equals: "announcements/no-classes-on-monday-173335600.jpg"
            $imagePath = $path;
        }

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'author_name' => 'Admin System', // Or Auth::user()->name
            'role' => 'admin'
        ]);

        return back()->with('success', 'Announcement posted!');
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