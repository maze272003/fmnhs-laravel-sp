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
            $file = $request->file('image');
            
            // 1. Gumawa ng unique filename
            $filename = time() . '_' . $file->getClientOriginalName();

            // 2. ITO ANG SOLUSYON:
            // Imbes na sa storage folder, ilipat natin direkta sa public folder.
            // Ito ay gagana sa Local at sa Hostinger nang walang extrang setup.
            $destinationPath = public_path('uploads/announcements');
            
            // Siguraduhin na may folder, kung wala, create it
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move the file
            $file->move($destinationPath, $filename);

            // 3. Save format: "announcements/filename.jpg"
            // Ginaya natin ang format ng 'store' method para compatible sa view logic natin kanina
            $imagePath = 'announcements/' . $filename;
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