<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    // Show the Profile Page
    public function index()
    {
        return view('student.profile');
    }

    // Update Profile (Avatar & Password)
    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            // CHANGED: max:2048 -> max:15360 (15MB)
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:15360', 
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // 1. Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($student->avatar && $student->avatar !== 'default.png') {
                Storage::delete('public/avatars/' . $student->avatar);
            }

            // Save new avatar
            $filename = time() . '.' . $request->avatar->extension();
            $request->avatar->storeAs('avatars', $filename, 'public');
            
            $student->avatar = $filename;
        }

        // 2. Handle Password Change
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $student->password = Hash::make($request->new_password);
        }

        $student->save();

        return back()->with('success', 'Profile updated successfully!');
    }
}