<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StudentProfileController extends Controller
{
    /**
     * Display the student's profile settings.
     */
    public function index(): View
    {
        /**
         * Load natin ang section at advisor para maipakita 
         * ang kumpletong detalye sa profile page.
         */
        $student = Auth::guard('student')->user()->load('section.advisor');

        return view('student.profile', compact('student'));
    }

    /**
     * Update the student's avatar or password.
     */
    public function update(Request $request): RedirectResponse
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:15360',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ], [
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'Only JPG and PNG files are allowed.',
            'avatar.max' => 'The image must not exceed 15MB.',
        ]);

        try {
            // 1. Handle Avatar Upload
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                $filename = time() . '_' . $student->id . '.' . $file->extension();
                $path = 'avatars/' . $filename;

                Storage::disk('s3')->put($path, file_get_contents($file), 'public');

                // Cleanup old avatar
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
                    return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
                }
                $student->password = Hash::make($request->new_password);
            }

            $student->save();

            return back()->with('success', 'Your profile has been updated successfully!');

        } catch (\Exception $e) {
            \Log::error("Avatar Upload Failed: " . $e->getMessage());
            return back()->withErrors(['avatar' => 'There was a problem uploading your avatar. Please try again.']);
        }
    }

    /**
     * Remove the student's profile picture.
     */
    public function removeAvatar(): RedirectResponse
    {
        $student = Auth::guard('student')->user();

        try {
            if ($student->avatar && $student->avatar !== 'default.png') {
                $oldPath = 'avatars/' . $student->avatar;
                if (Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }

            $student->avatar = null;
            $student->save();

            return back()->with('success', 'Profile picture has been removed successfully.');
        } catch (\Exception $e) {
            \Log::error("Avatar Remove Failed: " . $e->getMessage());
            return back()->withErrors(['avatar' => 'There was a problem removing your profile picture.']);
        }
    }
}