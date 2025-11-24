<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class AuthController extends Controller
{
    // Show the form
    public function showLoginForm()
    {
        return view('auth.student');
    }

    // Process the login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // PALITAN ITO: Magdagdag ng guard('student')
        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout specific guard
        Auth::guard('student')->logout(); 
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('student/login'); // O kaya sa student login page
    }
}