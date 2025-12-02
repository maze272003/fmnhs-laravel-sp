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
        // 1. UPDATE: Added video mimes & increased max size to 20MB
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // DOUBLE PATH: Local vs Production
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

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
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