<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Services\AuthServiceInterface;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    public function showLoginForm()
    {
        return view('auth.student');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $this->authService->login($request->email, $request->password, 'student');
            $request->session()->regenerate();
            
            return redirect()->route('student.dashboard');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout('student');
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('student/login');
    }
}
