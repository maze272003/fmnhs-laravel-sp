<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TeacherAnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository
    ) {}

    public function index()
    {
        $announcements = $this->announcementRepository->orderBy('created_at', 'desc')->paginate(5);
        return view('teacher.announcement', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480' 
        ]);

        $teacher = Auth::guard('teacher')->user();
        $mediaPath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->putFileAs('announcements', $file, $filename);
            $mediaPath = $path;
        }

        $this->announcementRepository->create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $mediaPath, 
            'author_name' => 'Teacher ' . $teacher->last_name,
            'role' => 'teacher' 
        ]);

        if ($request->expectsJson()) {
            session()->flash('success', 'Announcement posted successfully!');
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Announcement posted successfully to the board!');
    }

    public function destroy($id)
    {
        $announcement = $this->announcementRepository->findOrFail($id);

        if ($announcement->image) {
            if (Storage::disk('s3')->exists($announcement->image)) {
                Storage::disk('s3')->delete($announcement->image);
            }
        }

        $this->announcementRepository->delete($id);

        return back()->with('success', 'Announcement and its media have been deleted!');
    }
}
