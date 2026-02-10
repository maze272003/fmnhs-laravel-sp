<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConferenceAccessController;
use App\Http\Controllers\ConferencePlaybackController;
use App\Http\Controllers\Api\ConferenceApiController;
use App\Http\Controllers\Api\ConferenceRecordingController;
use App\Http\Controllers\Api\ConferenceNotificationController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\VideoConferenceController;
use App\Http\Controllers\TeacherAuthController;
use App\Models\Announcement;
use Illuminate\Support\Facades\Route;

// Default Route
Route::get('/', function () {
    $announcements = Announcement::orderBy('created_at', 'desc')->take(3)->get();

    return view('welcome', compact('announcements'));
});

// Authentication Routes
Route::get('/student/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/student/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
Route::post('/teacher/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Public Meeting Join Page (student credentials required)
Route::get('/conference/join/{conference}', [ConferenceAccessController::class, 'showJoinForm'])->name('conference.join.form');
Route::post('/conference/join/{conference}', [ConferenceAccessController::class, 'joinWithCredentials'])->name('conference.join.attempt');

// Shared Room Route (teacher or student session)
Route::middleware(['auth:teacher,student'])->group(function () {
    Route::get('/conference/{conference}/room', [ConferenceAccessController::class, 'room'])->name('conference.room');

    // Conference API — Chat, Participants, Events
    Route::post('/conference/{conference}/messages', [ConferenceApiController::class, 'storeMessage'])->name('conference.messages.store');
    Route::post('/conference/{conference}/files', [ConferenceApiController::class, 'uploadFile'])->name('conference.files.upload');
    Route::get('/conference/{conference}/messages', [ConferenceApiController::class, 'getMessages'])->name('conference.messages.index');
    Route::get('/conference/{conference}/participants', [ConferenceApiController::class, 'getParticipants'])->name('conference.participants.index');
    Route::post('/conference/{conference}/events', [ConferenceApiController::class, 'logEvent'])->name('conference.events.store');
    Route::post('/conference/{conference}/join-log', [ConferenceApiController::class, 'recordJoin'])->name('conference.join.log');
    Route::post('/conference/{conference}/leave-log', [ConferenceApiController::class, 'recordLeave'])->name('conference.leave.log');
    Route::get('/conference/{conference}/summary', [ConferenceApiController::class, 'getSummary'])->name('conference.summary');
    Route::get('/conference/{conference}/timeline', [ConferenceApiController::class, 'getTimeline'])->name('conference.timeline');

    // Conference Recordings
    Route::get('/conference/{conference}/recordings', [ConferenceRecordingController::class, 'index'])->name('conference.recordings.index');
    Route::post('/conference/{conference}/recordings', [ConferenceRecordingController::class, 'store'])->name('conference.recordings.store');
    Route::get('/conference/{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'show'])->name('conference.recordings.show');
    Route::put('/conference/{conference}/recordings/{recording}/chapters', [ConferenceRecordingController::class, 'updateChapters'])->name('conference.recordings.chapters');
    Route::get('/conference/{conference}/recordings/{recording}/transcript', [ConferenceRecordingController::class, 'transcript'])->name('conference.recording.transcript');
    Route::delete('/conference/{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'destroy'])->name('conference.recordings.destroy');

    // Conference Playback
    Route::get('/conference/{conference}/playback/{recording}', [ConferencePlaybackController::class, 'show'])->name('conference.playback');

    // Conference Notifications
    Route::get('/conference/notifications', [ConferenceNotificationController::class, 'index'])->name('conference.notifications.index');
    Route::post('/conference/notifications/read', [ConferenceNotificationController::class, 'markRead'])->name('conference.notifications.read');
});

// Student Protected Routes
Route::middleware(['auth:student'])->group(function () {
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/student/profile', [App\Http\Controllers\Student\StudentProfileController::class, 'index'])->name('student.profile');
    Route::post('/student/profile', [App\Http\Controllers\Student\StudentProfileController::class, 'update'])->name('student.profile.update');
    Route::delete('/student/profile/avatar', [App\Http\Controllers\Student\StudentProfileController::class, 'removeAvatar'])->name('student.profile.removeAvatar');

    Route::get('/student/grades', [StudentController::class, 'grades'])->name('student.grades');
    Route::get('/student/grades/pdf', [App\Http\Controllers\Student\StudentController::class, 'downloadGrades'])->name('student.grades.pdf');
    Route::get('/student/schedule', [StudentController::class, 'schedule'])->name('student.schedule');
    Route::get('/student/enrollment-history', [StudentController::class, 'enrollmentHistory'])->name('student.enrollment.history');

    Route::get('/student/assignments', [App\Http\Controllers\Student\StudentAssignmentController::class, 'index'])->name('student.assignments.index');
    Route::post('/student/assignments/submit', [App\Http\Controllers\Student\StudentAssignmentController::class, 'submit'])->name('student.assignments.submit');
    Route::get('/student/attendance', [App\Http\Controllers\Student\StudentAttendanceController::class, 'index'])->name('student.attendance.index');
});

// Teacher Protected Routes
Route::middleware(['auth:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
    Route::get('/teacher/my-classes', [TeacherController::class, 'myClasses'])->name('teacher.classes.index');
    Route::get('/teacher/grading', [TeacherController::class, 'gradingSheet'])->name('teacher.grading.index');
    Route::get('/teacher/grading/show', [TeacherController::class, 'showClass'])->name('teacher.grading.show');
    Route::post('/teacher/grading/save', [TeacherController::class, 'storeGrades'])->name('teacher.grades.store');
    Route::get('/teacher/grading/print', [TeacherController::class, 'printGradeSheet'])->name('teacher.grades.print');
    Route::get('/teacher/students', [TeacherController::class, 'myStudents'])->name('teacher.students.index');

    Route::get('/teacher/announcements', [App\Http\Controllers\Teacher\TeacherAnnouncementController::class, 'index'])->name('teacher.announcements.index');
    Route::post('/teacher/announcements', [App\Http\Controllers\Teacher\TeacherAnnouncementController::class, 'store'])->name('teacher.announcements.store');
    Route::delete('/teacher/announcements/{id}', [App\Http\Controllers\Teacher\TeacherAnnouncementController::class, 'destroy'])->name('teacher.announcements.destroy');

    Route::get('/teacher/assignments', [App\Http\Controllers\Teacher\AssignmentController::class, 'index'])->name('teacher.assignments.index');
    Route::post('/teacher/assignments', [App\Http\Controllers\Teacher\AssignmentController::class, 'store'])->name('teacher.assignments.store');
    Route::get('/teacher/assignments/{id}', [App\Http\Controllers\Teacher\AssignmentController::class, 'show'])->name('teacher.assignments.show');

    Route::get('/teacher/attendance', [App\Http\Controllers\Teacher\AttendanceController::class, 'index'])->name('teacher.attendance.index');
    Route::get('/teacher/attendance/sheet', [App\Http\Controllers\Teacher\AttendanceController::class, 'show'])->name('teacher.attendance.show');
    Route::post('/teacher/attendance', [App\Http\Controllers\Teacher\AttendanceController::class, 'store'])->name('teacher.attendance.store');

    Route::get('/teacher/conferences', [VideoConferenceController::class, 'index'])->name('teacher.conferences.index');
    Route::post('/teacher/conferences', [VideoConferenceController::class, 'store'])->name('teacher.conferences.store');
    Route::get('/teacher/conferences/{conference}', [VideoConferenceController::class, 'show'])->name('teacher.conferences.show');
    Route::post('/teacher/conferences/{conference}/end', [VideoConferenceController::class, 'end'])->name('teacher.conferences.end');
});

// Admin Protected Dashboard
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // STUDENT MANAGEMENT ROUTES
    Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
    Route::get('/admin/students/archived', [AdminStudentController::class, 'archived'])->name('admin.students.archived');
    Route::post('/admin/students', [AdminStudentController::class, 'store'])->name('admin.students.store');
    Route::post('/admin/students/promote', [AdminStudentController::class, 'promote'])->name('admin.students.promote');
    Route::put('/admin/students/{id}', [AdminStudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{id}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');

    // Status Changes
    Route::post('/admin/students/{id}/restore', [AdminStudentController::class, 'restore'])->name('admin.students.restore');
    Route::post('/admin/students/{id}/drop', [AdminStudentController::class, 'dropStudent'])->name('admin.students.drop');
    Route::post('/admin/students/{id}/transfer', [AdminStudentController::class, 'transferStudent'])->name('admin.students.transfer');
    Route::post('/admin/students/{id}/reenroll', [AdminStudentController::class, 'reenrollStudent'])->name('admin.students.reenroll');

    Route::get('admin/students/{id}/print', [App\Http\Controllers\Admin\AdminStudentController::class, 'printRecord'])
        ->name('admin.students.print');

    // FIX: Renamed 'students.show' to 'admin.students.show' to match the View
    Route::get('/admin/students/{id}/record', [AdminStudentController::class, 'show'])->name('admin.students.show');

    // TEACHER MANAGEMENT
    Route::get('/admin/teachers', [AdminTeacherController::class, 'index'])->name('admin.teachers.index');
    Route::put('/admin/teachers/{teacher}', [AdminTeacherController::class, 'update'])->name('admin.teachers.update');
    Route::post('/admin/teachers/{teacher}/archive', [AdminTeacherController::class, 'archive'])->name('admin.teachers.archive');
    Route::post('/admin/teachers/{id}/restore', [AdminTeacherController::class, 'restore'])->name('admin.teachers.restore');
    Route::post('/admin/teachers', [AdminTeacherController::class, 'store'])->name('admin.teachers.store');

    // SUBJECT MANAGEMENT
    Route::get('/admin/subjects', [AdminSubjectController::class, 'index'])->name('admin.subjects.index');
    Route::post('/admin/subjects', [AdminSubjectController::class, 'store'])->name('admin.subjects.store');
    Route::put('/admin/subjects/{id}', [AdminSubjectController::class, 'update'])->name('admin.subjects.update');
    Route::delete('/admin/subjects/{id}', [AdminSubjectController::class, 'destroy'])->name('admin.subjects.destroy');
    Route::post('/admin/subjects/{subject}/archive', [AdminSubjectController::class, 'archive'])->name('admin.subjects.archive');
    Route::post('/admin/subjects/{id}/restore', [AdminSubjectController::class, 'restore'])->name('admin.subjects.restore');
    Route::delete('/admin/subjects/{id}/force-delete', [AdminSubjectController::class, 'forceDelete'])->name('admin.subjects.force-delete');

    // ANNOUNCEMENTS
    Route::get('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::post('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::delete('/admin/announcements/{announcement}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

    // SCHEDULES
    Route::get('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::post('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::delete('/admin/schedules/{id}', [App\Http\Controllers\Admin\AdminScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // ATTENDANCE & GRADES
    Route::get('/admin/attendance', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::post('/admin/grades/lock', [App\Http\Controllers\Admin\AdminGradeController::class, 'lockGrades'])->name('admin.grades.lock');
    Route::post('/admin/grades/unlock', [App\Http\Controllers\Admin\AdminGradeController::class, 'unlockGrades'])->name('admin.grades.unlock');

    // UTILITIES (Audit, School Year, Rooms)
    Route::get('/admin/audit-trail', [App\Http\Controllers\Admin\AdminAuditTrailController::class, 'index'])->name('admin.audit-trail.index');

    Route::get('/admin/school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'index'])->name('admin.school-years.index');
    Route::post('/admin/school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'store'])->name('admin.school-years.store');
    Route::post('/admin/school-years/{id}/activate', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'activate'])->name('admin.school-years.activate');
    Route::post('/admin/school-years/{id}/close', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'close'])->name('admin.school-years.close');

    Route::get('/admin/rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'index'])->name('admin.rooms.index');
    Route::post('/admin/rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'store'])->name('admin.rooms.store');
    Route::put('/admin/rooms/{id}', [App\Http\Controllers\Admin\AdminRoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/admin/rooms/{id}', [App\Http\Controllers\Admin\AdminRoomController::class, 'destroy'])->name('admin.rooms.destroy');
});

require __DIR__.'/db.php';
