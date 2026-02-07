<?php

namespace App\Services;

use App\Mail\AnnouncementMail;
use App\Models\Announcement;
use App\Models\Student;
use App\Repositories\Contracts\AnnouncementRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementManagementService
{
    public function __construct(private readonly AnnouncementRepositoryInterface $announcements)
    {
    }

    public function paginate(int $perPage = 5)
    {
        return $this->announcements->paginateLatest($perPage);
    }

    public function latest(int $limit = 5)
    {
        return $this->announcements->latest($limit);
    }

    public function createForAdmin(array $validated, ?string $authorName = null): Announcement
    {
        $imagePath = $this->storeMedia($validated['image'] ?? null, $validated['title']);

        $announcement = $this->announcements->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'author_name' => $authorName ?? 'Admin',
            'role' => 'admin',
            'target_audience' => $validated['target_audience'],
        ]);

        $this->broadcastToStudents($announcement);

        return $announcement;
    }

    public function createForTeacher(array $validated, string $authorName): Announcement
    {
        $imagePath = $this->storeMedia($validated['image'] ?? null, $validated['title']);

        return $this->announcements->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'author_name' => $authorName,
            'role' => 'teacher',
            'target_audience' => $validated['target_audience'],
        ]);
    }

    public function deleteById(int $id): void
    {
        $announcement = $this->announcements->findOrFail($id);

        if ($announcement->image && Storage::disk('s3')->exists($announcement->image)) {
            Storage::disk('s3')->delete($announcement->image);
        }

        $this->announcements->delete($announcement);
    }

    private function storeMedia(?UploadedFile $file, string $title): ?string
    {
        if (!$file) {
            return null;
        }

        $filename = Str::slug($title) . '-' . time() . '.' . $file->getClientOriginalExtension();
        return Storage::disk('s3')->putFileAs('announcements', $file, $filename, 'public');
    }

    private function broadcastToStudents(Announcement $announcement): void
    {
        Student::whereNotNull('email')->chunk(50, function ($students) use ($announcement) {
            foreach ($students as $student) {
                try {
                    Mail::to($student->email)->queue(new AnnouncementMail($announcement));
                } catch (\Throwable $exception) {
                    Log::error('Mail queue failed for announcement broadcast', [
                        'email' => $student->email,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        });
    }
}
