<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Services\AuthServiceInterface;

class TeacherAuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    public function showLoginForm()
    {
        return view('auth.teacher');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $this->authService->login($request->email, $request->password, 'teacher');
            $request->session()->regenerate();
            return redirect()->route('teacher.dashboard');
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Invalid teacher credentials.',
            ])->onlyInput('email');
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout('teacher');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('teacher/login');
    }
}
