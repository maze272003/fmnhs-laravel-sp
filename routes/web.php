<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeacherAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Teacher\TeacherController; 
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Models\Announcement;
// add aritsan command for php artisan migrate:fresh --seed

// make default is welcome blade
Route::get('/', function () {
    // Fetch latest 3 announcements
    $announcements = Announcement::orderBy('created_at', 'desc')->take(3)->get();
    return view('welcome', compact('announcements'));
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
    Route::get('/student/profile', [App\Http\Controllers\Student\StudentProfileController::class, 'index'])->name('student.profile');
    Route::post('/student/profile', [App\Http\Controllers\Student\StudentProfileController::class, 'update'])->name('student.profile.update');

   Route::get('/student/grades', [StudentController::class, 'grades'])->name('student.grades');
   Route::get('/student/grades/pdf', [App\Http\Controllers\Student\StudentController::class, 'downloadGrades'])->name('student.grades.pdf');
   Route::get('/student/schedule', [StudentController::class, 'schedule'])->name('student.schedule');

   Route::get('/student/assignments', [App\Http\Controllers\Student\StudentAssignmentController::class, 'index'])->name('student.assignments.index');
    Route::post('/student/assignments/submit', [App\Http\Controllers\Student\StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::get('/student/attendance', [App\Http\Controllers\Student\StudentAttendanceController::class, 'index'])->name('student.attendance.index');
});


Route::middleware(['auth:teacher'])->group(function () {
    // CHANGED: Use TeacherController@dashboard to fetch dynamic data
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    
    Route::get('/teacher/my-classes', [TeacherController::class, 'myClasses'])->name('teacher.classes.index');
    Route::get('/teacher/grading', [TeacherController::class, 'gradingSheet'])->name('teacher.grading.index');
    
    // 2. The Actual Grading Page (Using GET so we can share links easily)
    Route::get('/teacher/grading/show', [TeacherController::class, 'showClass'])->name('teacher.grading.show');
    Route::post('/teacher/grading/save', [TeacherController::class, 'storeGrades'])->name('teacher.grades.store');
    Route::get('/teacher/students', [TeacherController::class, 'myStudents'])->name('teacher.students.index');

    Route::get('/teacher/announcements', [App\Http\Controllers\Teacher\TeacherAnnouncementController::class, 'index'])->name('teacher.announcements.index');
    Route::post('/teacher/announcements', [App\Http\Controllers\Teacher\TeacherAnnouncementController::class, 'store'])->name('teacher.announcements.store');

    Route::get('/teacher/assignments', [App\Http\Controllers\Teacher\AssignmentController::class, 'index'])->name('teacher.assignments.index');
    Route::post('/teacher/assignments', [App\Http\Controllers\Teacher\AssignmentController::class, 'store'])->name('teacher.assignments.store');
    Route::get('/teacher/assignments/{id}', [App\Http\Controllers\Teacher\AssignmentController::class, 'show'])->name('teacher.assignments.show');

    Route::get('/teacher/attendance', [App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::get('/teacher/attendance/sheet', [App\Http\Controllers\Teacher\AttendanceController::class, 'show'])->name('teacher.attendance.show');
    Route::post('/teacher/attendance', [App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('teacher.attendance.store');
});



// Admin Protected Dashboard
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    
    // Manage Teachers
    Route::get('/admin/teachers', [AdminTeacherController::class, 'index'])->name('admin.teachers.index');

    Route::get('/admin/subjects', [AdminSubjectController::class, 'index'])->name('admin.subjects.index');
    Route::post('/admin/subjects', [AdminSubjectController::class, 'store'])->name('admin.subjects.store');
    Route::put('/admin/subjects/{id}', [AdminSubjectController::class, 'update'])->name('admin.subjects.update');
    Route::delete('/admin/subjects/{id}', [AdminSubjectController::class, 'destroy'])->name('admin.subjects.destroy');

    Route::get('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::post('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::delete('/admin/announcements/{id}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

    Route::get('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::post('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::delete('/admin/schedules/{id}', [App\Http\Controllers\Admin\AdminScheduleController::class, 'destroy'])->name('admin.schedules.destroy');
    Route::get('/admin/attendance', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'index'])->name('admin.attendance.index');

    Route::post('/admin/students', [AdminStudentController::class, 'store'])->name('admin.students.store');
    Route::put('/admin/students/{id}', [AdminStudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{id}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');
});

require __DIR__.'/db.php';