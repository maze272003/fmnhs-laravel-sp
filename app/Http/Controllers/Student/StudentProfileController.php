<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentProfileController extends Controller
{
    public function index()
    {
        return view('student.profile');
    }

    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:15360', 
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // 1. Handle Avatar Upload to MinIO S3
        if ($request->hasFile('avatar')) {
            // Delete old avatar from S3 if it exists and isn't the default
            if ($student->avatar && $student->avatar !== 'default.png') {
                // Check if file exists on S3 before trying to delete to avoid errors
                if (Storage::disk('s3')->exists('avatars/' . $student->avatar)) {
                    Storage::disk('s3')->delete('avatars/' . $student->avatar);
                }
            }

            // Generate filename
            $filename = time() . '.' . $request->avatar->extension();
            
            // Upload to S3 (MinIO)
            // This saves to the 'fmnhs' bucket inside an 'avatars' folder
            $request->avatar->storeAs('avatars', $filename, 's3');
            
            $student->avatar = $filename;
        }

        // 2. Handle Password Change (Unchanged)
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