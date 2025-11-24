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
        // 1. Validation: Huwag na 'email' rule ang gamitin kasi baka LRN ang i-input
        $request->validate([
            'login_id' => ['required'], // Ito yung name sa form sa Step 1
            'password' => ['required'],
        ]);

        // 2. Kunin ang input
        $login_id = $request->input('login_id');

        // 3. LOGIC: Check kung Email format ba ang input?
        // Kung valid email format -> set field to 'email'
        // Kung hindi -> set field to 'lrn'
        $fieldType = filter_var($login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'lrn';

        // 4. Buuin ang credentials array
        $credentials = [
            $fieldType => $login_id, // Magiging ['email' => '...'] OR ['lrn' => '...']
            'password' => $request->input('password')
        ];

        // 5. Attempt Login
        if (Auth::guard('student')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('student.dashboard');
        }

        // 6. Error handling
        return back()->withErrors([
            'login_id' => 'The provided credentials do not match our records.',
        ])->onlyInput('login_id');
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