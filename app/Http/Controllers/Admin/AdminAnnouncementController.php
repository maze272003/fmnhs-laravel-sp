<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            // STANDARD LOCAL WAY:
            // Ise-save ito sa: storage/app/public/announcements
            // Ang value ng $imagePath ay magiging: "announcements/filename.jpg"
            $imagePath = $request->file('image')->store('announcements', 'public');
        }

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath, // "announcements/image.jpg"
            'author_name' => 'Admin System',
            'role' => 'admin'
        ]);

        return back()->with('success', 'Announcement posted!');
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}