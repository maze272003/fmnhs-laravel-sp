<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Student\StudentController;
// 
// make default is welcome blade
Route::get('/', function () {
    return view('welcome');
});
// 1. The Login Form
Route::get('/student/login', [AuthController::class, 'showLoginForm'])->name('login');

// 2. The Logic to Process Login
Route::post('/student/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
Route::post('/teacher/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// 3. The Destination (Student Dashboard)
// We use the 'auth' middleware to ensure only logged-in users can see this
// Gamitin ang 'auth:student' sa halip na 'auth' lang
Route::middleware(['auth:student'])->group(function () {
    Route::get('/student/dashboard', function () {
        return view('student.dashboard'); 
    })->name('student.dashboard');

   Route::get('/student/grades', [StudentController::class, 'grades'])->name('student.grades');
});


Route::middleware(['auth:teacher'])->group(function () {
    Route::get('/teacher/dashboard', function () {
        return view('teacher.dashboard');
    })->name('teacher.dashboard');
});



// Admin Protected Dashboard
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    
    // Manage Teachers
    Route::get('/admin/teachers', [AdminTeacherController::class, 'index'])->name('admin.teachers.index');
});