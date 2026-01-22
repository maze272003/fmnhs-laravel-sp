<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\AnnouncementRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Services\NotificationServiceInterface;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminAnnouncementController extends Controller
{
    public function __construct(
        private AnnouncementRepositoryInterface $announcementRepository,
        private StudentRepositoryInterface $studentRepository,
        private NotificationServiceInterface $notificationService
    ) {}

    public function index(): View
    {
        $announcements = $this->announcementRepository->latest('created_at')->paginate(5);
        return view('admin.announcement', compact('announcements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:40480'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::slug($request->title) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $imagePath = Storage::disk('s3')->putFileAs('announcements', $file, $filename, 'public');
        }

        $announcement = $this->announcementRepository->create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $imagePath,
            'author_name' => Auth::guard('admin')->user()->name, 
            'role' => 'admin'
        ]);

        try {
            $this->notificationService->sendAnnouncementEmail($announcement->id, 'student');
        } catch (\Exception $e) {
            \Log::error('Announcement email broadcast failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Announcement broadcasted successfully to all students!');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();

        $allowedEmails = [
            'admin@school.com',
            'sangbaanstephaniemary@gmail.com',
        ];

        if (!$admin || !in_array($admin->email, $allowedEmails, true)) {
            return back()->with('error', 'You are not authorized to delete this announcement.');
        }

        if ($announcement->image && Storage::disk('s3')->exists($announcement->image)) {
            Storage::disk('s3')->delete($announcement->image);
        }

        $this->announcementRepository->delete($announcement->id);

        return back()->with('success', 'Announcement has been retracted and media deleted.');
    }
}
