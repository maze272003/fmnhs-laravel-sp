<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\StudentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentProfileController extends Controller
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    /**
     * Display student's profile settings.
     */
    public function index(): View
    {
        $studentId = Auth::guard('student')->id();
        $student = $this->studentRepository
            ->with(['section.advisor'])
            ->findOrFail($studentId);

        return view('student.profile', compact('student'));
    }

    /**
     * Update student's avatar or password.
     */
    public function update(Request $request): RedirectResponse
    {
        $studentId = Auth::guard('student')->id();
        $student = $this->studentRepository->findOrFail($studentId);

        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:15360',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        try {
            // 1. Handle Avatar Upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');

                // Generate unique filename
                $filename = time() . '_' . $student->id . '.' . $file->extension();
                $path = 'avatars/' . $filename;

                // Upload sa S3 na may 'public' visibility para mabasa ng browser
                Storage::disk('s3')->put($path, file_get_contents($file), 'public');

                // Cleanup: Burahin ang lumang avatar kung hindi ito default
                if ($student->avatar && $student->avatar !== 'default.png') {
                    $oldPath = 'avatars/' . $student->avatar;
                    if (Storage::disk('s3')->exists($oldPath)) {
                        Storage::disk('s3')->delete($oldPath);
                    }
                }

                $student->avatar = $filename;
            }

            // 2. Handle Password Change
            if ($request->filled('current_password')) {
                if (!Hash::check($request->current_password, $student->password)) {
                    return back()->withErrors(['current_password' => 'Ang iyong kasalukuyang password ay mali.']);
                }
                $student->password = Hash::make($request->new_password);
            }

            $this->studentRepository->update($student->id, [
                'avatar' => $student->avatar,
                'password' => $student->password,
            ]);

            return back()->with('success', 'Ang iyong profile ay matagumpay na-update!');

        } catch (\Exception $e) {
            // Log ang error para sa debugging
            \Log::error("Avatar Upload Failed: " . $e->getMessage());
            return back()->withErrors(['avatar' => 'Nagkaroon ng problema sa pag-upload: ' . $e->getMessage()]);
        }
    }
}