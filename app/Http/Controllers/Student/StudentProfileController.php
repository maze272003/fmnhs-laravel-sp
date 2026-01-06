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
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:15360', // 15MB limit
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // 1. Handle Avatar Upload (S3/MinIO)
        if ($request->hasFile('avatar')) {
            // Cleanup: Burahin ang lumang avatar sa S3 kung hindi ito default
            if ($student->avatar && $student->avatar !== 'default.png') {
                $oldPath = 'avatars/' . $student->avatar;
                if (Storage::disk('s3')->exists($oldPath)) {
                    Storage::disk('s3')->delete($oldPath);
                }
            }

            // Generate unique filename at i-store
            $filename = time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('avatars', $filename, 's3');
            
            $student->avatar = $filename;
        }

        // 2. Handle Password Change
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Ang iyong kasalukuyang password ay mali.']);
            }
            $student->password = Hash::make($request->new_password);
        }

        $student->save();

        return back()->with('success', 'Ang iyong profile ay matagumpay na na-update!');
    }
}