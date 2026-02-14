<?php

use Illuminate\Support\Facades\Route;
use App\Models\Announcement;

// Admin
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminAlertController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminWorkloadController;
use App\Http\Controllers\Admin\AdminParentController;

// Student
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentLearningPathController;
use App\Http\Controllers\Student\StudentPortfolioController;
use App\Http\Controllers\Student\StudentStudyController;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\StudentAssignmentController;
use App\Http\Controllers\Student\StudentAttendanceController;

// Teacher
use App\Http\Controllers\TeacherAuthController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\VideoConferenceController;
use App\Http\Controllers\Teacher\LessonPlanController;
use App\Http\Controllers\Teacher\ProgressReportController;
use App\Http\Controllers\Teacher\SeatingController;
use App\Http\Controllers\Teacher\BulkActionController;
use App\Http\Controllers\Teacher\TeacherAnnouncementController;
use App\Http\Controllers\Teacher\AssignmentController;
use App\Http\Controllers\Teacher\AttendanceController;

// Parent
use App\Http\Controllers\Parent\ParentAuthController;
use App\Http\Controllers\Parent\ParentDashboardController;

// Conference - access + playback
use App\Http\Controllers\ConferenceAccessController;
use App\Http\Controllers\ConferencePlaybackController;

// Conference APIs
use App\Http\Controllers\Api\ConferenceApiController;
use App\Http\Controllers\Api\ConferenceRecordingController;
use App\Http\Controllers\Api\ConferenceNotificationController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\GamificationApiController;

// Extended API
use App\Http\Controllers\Api\WhiteboardApiController;
use App\Http\Controllers\Api\BreakoutRoomApiController;
use App\Http\Controllers\Api\ConferenceMoodApiController;
use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\CaptionApiController;
use App\Http\Controllers\Api\PresentationApiController;
use App\Http\Controllers\Api\StudyGroupApiController;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\LearningPathApiController;
use App\Http\Controllers\Api\PortfolioApiController;
use App\Http\Controllers\Api\StudySessionApiController;
use App\Http\Controllers\Api\InterventionAlertApiController;
use App\Http\Controllers\Api\ContentRecommendationApiController;
use App\Http\Controllers\Api\AIAssistantApiController;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $announcements = Announcement::orderBy('created_at', 'desc')->take(3)->get();
    return view('welcome', compact('announcements'));
})->name('home');

/*
|--------------------------------------------------------------------------
| AUTH (Student/Teacher/Admin/Parent)
|--------------------------------------------------------------------------
*/
Route::prefix('student')->name('student.')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('teacher')->name('teacher.')->group(function () {
    Route::get('login', [TeacherAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [TeacherAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('logout', [TeacherAuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
});

Route::prefix('parent')->name('parent.')->group(function () {
    Route::get('login', [ParentAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [ParentAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::post('logout', [ParentAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| CONFERENCE (PUBLIC JOIN + ROOM ACCESS)
|--------------------------------------------------------------------------
*/
Route::prefix('conference')->name('conference.')->group(function () {
    Route::get('join/{conference}', [ConferenceAccessController::class, 'showJoinForm'])->name('join.form');
    Route::post('join/{conference}', [ConferenceAccessController::class, 'joinWithCredentials'])->name('join.attempt');

    Route::post('join/{conference}/guest/validate', [ConferenceAccessController::class, 'validateGuestKey'])->name('join.guest.validate');
    Route::post('join/{conference}/guest', [ConferenceAccessController::class, 'joinAsGuest'])->name('join.guest');

    Route::get('{conference}/room', [ConferenceAccessController::class, 'room'])->name('room');
    Route::get('{conference}/status', [ConferenceAccessController::class, 'status'])->name('status');
});

/*
|--------------------------------------------------------------------------
| CONFERENCE (PROTECTED: teacher OR student)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:teacher,student'])->group(function () {

    Route::prefix('conference')->name('conference.')->group(function () {
        // Analytics endpoints (restricted)
        Route::get('{conference}/summary', [ConferenceApiController::class, 'getSummary'])->name('summary');
        Route::get('{conference}/timeline', [ConferenceApiController::class, 'getTimeline'])->name('timeline');

        // Recordings
        Route::get('{conference}/recordings', [ConferenceRecordingController::class, 'index'])->name('recordings.index');
        Route::post('{conference}/recordings', [ConferenceRecordingController::class, 'store'])->name('recordings.store');
        Route::get('{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'show'])->name('recordings.show');
        Route::put('{conference}/recordings/{recording}/chapters', [ConferenceRecordingController::class, 'updateChapters'])->name('recordings.chapters');
        Route::get('{conference}/recordings/{recording}/transcript', [ConferenceRecordingController::class, 'transcript'])->name('recording.transcript');
        Route::delete('{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'destroy'])->name('recordings.destroy');

        // Playback
        Route::get('{conference}/playback/{recording}', [ConferencePlaybackController::class, 'show'])->name('playback');

        // Notifications
        Route::get('notifications', [ConferenceNotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read', [ConferenceNotificationController::class, 'markRead'])->name('notifications.read');
    });

    /*
    |--------------------------------------------------------------------------
    | API (Shared: teacher OR student) — canonical endpoints only
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->middleware('throttle:60,1')->group(function () {

        // --- Conference Core ---
        Route::prefix('conference/{conference}')->group(function () {
            Route::post('messages', [ConferenceApiController::class, 'storeMessage']);
            Route::get('messages', [ConferenceApiController::class, 'getMessages']);

            Route::post('files', [ConferenceApiController::class, 'uploadFile']);
            Route::get('participants', [ConferenceApiController::class, 'getParticipants']);

            Route::post('events', [ConferenceApiController::class, 'logEvent']);

            // Canonical join/leave logs (remove duplicates)
            Route::post('join', [ConferenceApiController::class, 'recordJoin']);
            Route::post('leave', [ConferenceApiController::class, 'recordLeave']);

            Route::get('summary', [ConferenceApiController::class, 'getSummary']);
            Route::get('timeline', [ConferenceApiController::class, 'getTimeline']);

            // Recordings
            Route::get('recordings', [ConferenceRecordingController::class, 'index']);
            Route::post('recordings', [ConferenceRecordingController::class, 'store']);
            Route::get('recordings/{recording}', [ConferenceRecordingController::class, 'show']);
            Route::put('recordings/{recording}/chapters', [ConferenceRecordingController::class, 'updateChapters']);
            Route::get('recordings/{recording}/transcript', [ConferenceRecordingController::class, 'transcript']);
            Route::delete('recordings/{recording}', [ConferenceRecordingController::class, 'destroy']);
        });

        // Notifications
        Route::get('conference/notifications', [ConferenceNotificationController::class, 'index']);
        Route::post('conference/notifications/read', [ConferenceNotificationController::class, 'markRead']);

        // Quiz
        Route::get('conference/{conference}/quizzes', [QuizApiController::class, 'index']);
        Route::post('quizzes', [QuizApiController::class, 'store']);
        Route::get('quizzes/{quiz}', [QuizApiController::class, 'show']);
        Route::put('quizzes/{quiz}', [QuizApiController::class, 'update']);
        Route::delete('quizzes/{quiz}', [QuizApiController::class, 'destroy']);
        Route::post('quizzes/{quiz}/questions', [QuizApiController::class, 'addQuestion']);
        Route::post('quizzes/{quiz}/start', [QuizApiController::class, 'start']);
        Route::post('quizzes/{quiz}/end', [QuizApiController::class, 'end']);
        Route::post('quizzes/{quiz}/questions/{question}/respond', [QuizApiController::class, 'submitResponse']);
        Route::get('quizzes/{quiz}/leaderboard', [QuizApiController::class, 'leaderboard']);
        Route::get('quizzes/{quiz}/results', [QuizApiController::class, 'results']);
        Route::get('quizzes/{quiz}/statistics', [QuizApiController::class, 'statistics']);
        Route::get('questions/{question}/results', [QuizApiController::class, 'questionResults']);

        // Gamification
        Route::get('gamification/summary', [GamificationApiController::class, 'summary']);
        Route::get('gamification/leaderboard', [GamificationApiController::class, 'leaderboard']);
        Route::get('gamification/badges', [GamificationApiController::class, 'badges']);
        Route::get('gamification/my-badges', [GamificationApiController::class, 'studentBadges']);
        Route::get('gamification/points-history', [GamificationApiController::class, 'pointsHistory']);
    });
});

/*
|--------------------------------------------------------------------------
| STUDENT (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('profile', [StudentProfileController::class, 'index'])->name('profile');
    Route::post('profile', [StudentProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile/avatar', [StudentProfileController::class, 'removeAvatar'])->name('profile.removeAvatar');

    // SIS Core
    Route::get('grades', [StudentController::class, 'grades'])->name('grades');
    Route::get('grades/pdf', [StudentController::class, 'downloadGrades'])->name('grades.pdf');
    Route::get('schedule', [StudentController::class, 'schedule'])->name('schedule');
    Route::get('enrollment-history', [StudentController::class, 'enrollmentHistory'])->name('enrollment.history');

    // Assignments + Attendance
    Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
    Route::post('assignments/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');

    // Extended: Learning Path
    Route::get('learning-path', [StudentLearningPathController::class, 'index'])->name('learning-path.index');
    Route::get('learning-path/{id}', [StudentLearningPathController::class, 'show'])->name('learning-path.show');
    Route::post('learning-path/{id}/progress', [StudentLearningPathController::class, 'updateProgress'])->name('learning-path.progress');

    // Extended: Portfolio
    Route::get('portfolio', [StudentPortfolioController::class, 'index'])->name('portfolio.index');
    Route::post('portfolio/items', [StudentPortfolioController::class, 'storeItem'])->name('portfolio.items.store');
    Route::put('portfolio/items/{id}', [StudentPortfolioController::class, 'updateItem'])->name('portfolio.items.update');
    Route::delete('portfolio/items/{id}', [StudentPortfolioController::class, 'destroyItem'])->name('portfolio.items.destroy');
    Route::post('portfolio/reflections', [StudentPortfolioController::class, 'storeReflection'])->name('portfolio.reflections.store');

    // Extended: Study Tools
    Route::get('study', [StudentStudyController::class, 'index'])->name('study.index');
    Route::post('study/sessions', [StudentStudyController::class, 'startSession'])->name('study.sessions.start');
    Route::post('study/sessions/{id}/end', [StudentStudyController::class, 'endSession'])->name('study.sessions.end');
    Route::post('study/goals', [StudentStudyController::class, 'storeGoal'])->name('study.goals.store');
    Route::put('study/goals/{id}', [StudentStudyController::class, 'updateGoal'])->name('study.goals.update');
});

/*
|--------------------------------------------------------------------------
| TEACHER (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');

    Route::get('my-classes', [TeacherController::class, 'myClasses'])->name('classes.index');
    Route::get('students', [TeacherController::class, 'myStudents'])->name('students.index');

    // Grading
    Route::get('grading', [TeacherController::class, 'gradingSheet'])->name('grading.index');
    Route::get('grading/show', [TeacherController::class, 'showClass'])->name('grading.show');
    Route::post('grading/save', [TeacherController::class, 'storeGrades'])->name('grades.store');
    Route::get('grading/print', [TeacherController::class, 'printGradeSheet'])->name('grades.print');

    // Announcements
    Route::get('announcements', [TeacherAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements', [TeacherAnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('announcements/{id}', [TeacherAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Assignments
    Route::get('assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('assignments/{id}', [AssignmentController::class, 'show'])->name('assignments.show');

    // Attendance
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('attendance/sheet', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // Conferences
    Route::get('conferences', [VideoConferenceController::class, 'index'])->name('conferences.index');
    Route::post('conferences', [VideoConferenceController::class, 'store'])->name('conferences.store');
    Route::patch('conferences/{conference}/privacy', [VideoConferenceController::class, 'updatePrivacy'])->name('conferences.privacy');
    Route::get('conferences/{conference}', [VideoConferenceController::class, 'show'])->name('conferences.show');
    Route::post('conferences/{conference}/end', [VideoConferenceController::class, 'end'])->name('conferences.end');

    // Lesson Plans
    Route::get('lesson-plans', [LessonPlanController::class, 'index'])->name('lesson-plans.index');
    Route::post('lesson-plans', [LessonPlanController::class, 'store'])->name('lesson-plans.store');
    Route::get('lesson-plans/{id}', [LessonPlanController::class, 'show'])->name('lesson-plans.show');
    Route::put('lesson-plans/{id}', [LessonPlanController::class, 'update'])->name('lesson-plans.update');
    Route::delete('lesson-plans/{id}', [LessonPlanController::class, 'destroy'])->name('lesson-plans.destroy');

    // Progress Reports
    Route::get('progress-reports', [ProgressReportController::class, 'index'])->name('progress-reports.index');
    Route::post('progress-reports/generate', [ProgressReportController::class, 'generate'])->name('progress-reports.generate');
    Route::get('progress-reports/{id}', [ProgressReportController::class, 'show'])->name('progress-reports.show');
    Route::post('progress-reports/{id}/send', [ProgressReportController::class, 'send'])->name('progress-reports.send');

    // Seating
    Route::get('seating', [SeatingController::class, 'index'])->name('seating.index');
    Route::post('seating', [SeatingController::class, 'store'])->name('seating.store');
    Route::get('seating/{arrangement}', [SeatingController::class, 'show'])->name('seating.show');
    Route::put('seating/{id}', [SeatingController::class, 'update'])->name('seating.update');
    Route::post('seating/{id}/auto-arrange', [SeatingController::class, 'autoArrange'])->name('seating.auto-arrange');

    // Bulk
    Route::post('bulk/grades', [BulkActionController::class, 'bulkGrades'])->name('bulk.grades');
    Route::post('bulk/attendance', [BulkActionController::class, 'bulkAttendance'])->name('bulk.attendance');
    Route::post('bulk/assignments', [BulkActionController::class, 'duplicateAssignments'])->name('bulk.assignments');
    Route::post('bulk/email', [BulkActionController::class, 'sendBulkEmail'])->name('bulk.email');
});

/*
|--------------------------------------------------------------------------
| ADMIN (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Students
    Route::get('students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::get('students/archived', [AdminStudentController::class, 'archived'])->name('students.archived');
    Route::post('students', [AdminStudentController::class, 'store'])->name('students.store');
    Route::post('students/promote', [AdminStudentController::class, 'promote'])->name('students.promote');
    Route::put('students/{id}', [AdminStudentController::class, 'update'])->name('students.update');
    Route::delete('students/{id}', [AdminStudentController::class, 'destroy'])->name('students.destroy');

    Route::post('students/{id}/restore', [AdminStudentController::class, 'restore'])->name('students.restore');
    Route::post('students/{id}/drop', [AdminStudentController::class, 'dropStudent'])->name('students.drop');
    Route::post('students/{id}/transfer', [AdminStudentController::class, 'transferStudent'])->name('students.transfer');
    Route::post('students/{id}/reenroll', [AdminStudentController::class, 'reenrollStudent'])->name('students.reenroll');
    Route::get('students/{id}/print', [AdminStudentController::class, 'printRecord'])->name('students.print');
    Route::get('students/{id}/record', [AdminStudentController::class, 'show'])->name('students.show');

    // Teachers
    Route::get('teachers', [AdminTeacherController::class, 'index'])->name('teachers.index');
    Route::post('teachers', [AdminTeacherController::class, 'store'])->name('teachers.store');
    Route::put('teachers/{teacher}', [AdminTeacherController::class, 'update'])->name('teachers.update');
    Route::post('teachers/{teacher}/archive', [AdminTeacherController::class, 'archive'])->name('teachers.archive');
    Route::post('teachers/{id}/restore', [AdminTeacherController::class, 'restore'])->name('teachers.restore');

    // Subjects
    Route::get('subjects', [AdminSubjectController::class, 'index'])->name('subjects.index');
    Route::post('subjects', [AdminSubjectController::class, 'store'])->name('subjects.store');
    Route::put('subjects/{id}', [AdminSubjectController::class, 'update'])->name('subjects.update');
    Route::delete('subjects/{id}', [AdminSubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::post('subjects/{subject}/archive', [AdminSubjectController::class, 'archive'])->name('subjects.archive');
    Route::post('subjects/{id}/restore', [AdminSubjectController::class, 'restore'])->name('subjects.restore');
    Route::delete('subjects/{id}/force-delete', [AdminSubjectController::class, 'forceDelete'])->name('subjects.force-delete');

    // Announcements
    Route::get('announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('announcements/{announcement}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('announcements.destroy');

    // Schedules
    Route::get('schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::post('schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('schedules/{id}', [App\Http\Controllers\Admin\AdminScheduleController::class, 'destroy'])->name('schedules.destroy');

    // Attendance & Grades
    Route::get('attendance', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('grades/lock', [App\Http\Controllers\Admin\AdminGradeController::class, 'lockGrades'])->name('grades.lock');
    Route::post('grades/unlock', [App\Http\Controllers\Admin\AdminGradeController::class, 'unlockGrades'])->name('grades.unlock');

    // Utilities
    Route::get('audit-trail', [App\Http\Controllers\Admin\AdminAuditTrailController::class, 'index'])->name('audit-trail.index');

    Route::get('school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'index'])->name('school-years.index');
    Route::post('school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'store'])->name('school-years.store');
    Route::post('school-years/{id}/activate', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'activate'])->name('school-years.activate');
    Route::post('school-years/{id}/close', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'close'])->name('school-years.close');

    Route::get('rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'index'])->name('rooms.index');
    Route::post('rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'store'])->name('rooms.store');
    Route::put('rooms/{id}', [App\Http\Controllers\Admin\AdminRoomController::class, 'update'])->name('rooms.update');
    Route::delete('rooms/{id}', [App\Http\Controllers\Admin\AdminRoomController::class, 'destroy'])->name('rooms.destroy');

    // Analytics
    Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/students', [AdminAnalyticsController::class, 'students'])->name('analytics.students');
    Route::get('analytics/teachers', [AdminAnalyticsController::class, 'teachers'])->name('analytics.teachers');

    // Workload
    Route::get('workload', [AdminWorkloadController::class, 'index'])->name('workload.index');

    // Parents
    Route::get('parents', [AdminParentController::class, 'index'])->name('parents.index');
    Route::post('parents', [AdminParentController::class, 'store'])->name('parents.store');
    Route::get('parents/{parent}', [AdminParentController::class, 'show'])->name('parents.show');
    Route::put('parents/{id}', [AdminParentController::class, 'update'])->name('parents.update');
    Route::delete('parents/{id}', [AdminParentController::class, 'destroy'])->name('parents.destroy');

    // Alerts
    Route::get('alerts', [AdminAlertController::class, 'index'])->name('alerts.index');
    Route::get('alerts/{id}', [AdminAlertController::class, 'show'])->name('alerts.show');
    Route::post('alerts/{id}/resolve', [AdminAlertController::class, 'resolve'])->name('alerts.resolve');
});

/*
|--------------------------------------------------------------------------
| PARENT (Protected)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:parent'])->prefix('parent')->name('parent.')->group(function () {
    Route::get('dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    Route::get('children', [ParentDashboardController::class, 'children'])->name('children');
    Route::get('children/{id}/grades', [ParentDashboardController::class, 'grades'])->name('children.grades');
    Route::get('children/{id}/attendance', [ParentDashboardController::class, 'attendance'])->name('children.attendance');
    Route::get('children/{id}/schedule', [ParentDashboardController::class, 'schedule'])->name('children.schedule');
    Route::get('children/{id}/assignments', [ParentDashboardController::class, 'assignments'])->name('children.assignments');

    Route::get('messages', [ParentDashboardController::class, 'messages'])->name('messages');
    Route::post('messages', [ParentDashboardController::class, 'sendMessage'])->name('messages.send');
});

/*
|--------------------------------------------------------------------------
| EXTENDED API ROUTES (Conference Features)
|--------------------------------------------------------------------------
| NOTE: Controllers must enforce role-specific permissions where required.
*/
Route::middleware(['auth:teacher,student'])->prefix('api')->middleware('throttle:60,1')->group(function () {

    // Whiteboard
    Route::post('conference/{conference}/whiteboard', [WhiteboardApiController::class, 'save']);
    Route::get('conference/{conference}/whiteboard', [WhiteboardApiController::class, 'load']);
    Route::delete('conference/{conference}/whiteboard', [WhiteboardApiController::class, 'clear']);

    // Breakout Rooms
    Route::get('conference/{conference}/breakout-rooms', [BreakoutRoomApiController::class, 'index']);
    Route::post('conference/{conference}/breakout-rooms', [BreakoutRoomApiController::class, 'store']);
    Route::post('conference/{conference}/breakout-rooms/auto-assign', [BreakoutRoomApiController::class, 'autoAssign']);
    Route::post('conference/{conference}/breakout-rooms/{id}/join', [BreakoutRoomApiController::class, 'join']);
    Route::post('conference/{conference}/breakout-rooms/{id}/leave', [BreakoutRoomApiController::class, 'leave']);
    Route::post('conference/{conference}/breakout-rooms/end-all', [BreakoutRoomApiController::class, 'endAll']);

    // Mood
    Route::post('conference/{conference}/mood', [ConferenceMoodApiController::class, 'store']);
    Route::get('conference/{conference}/mood/aggregate', [ConferenceMoodApiController::class, 'aggregate']);

    // Games
    Route::post('conference/{conference}/games', [GameApiController::class, 'store']);
    Route::post('conference/{conference}/games/{id}/score', [GameApiController::class, 'submitScore']);
    Route::post('conference/{conference}/games/{id}/end', [GameApiController::class, 'end']);

    // Captions
    Route::get('conference/{conference}/captions', [CaptionApiController::class, 'index']);
    Route::get('conference/{conference}/captions/search', [CaptionApiController::class, 'search']);

    // Presentations
    Route::post('conference/{conference}/presentations', [PresentationApiController::class, 'store']);
    Route::get('conference/{conference}/presentations/{id}', [PresentationApiController::class, 'show']);
    Route::get('conference/{conference}/presentations/{id}/slides/{slide}/analytics', [PresentationApiController::class, 'slideAnalytics']);

    // Study Groups & Forum
    Route::get('study-groups', [StudyGroupApiController::class, 'index']);
    Route::post('study-groups', [StudyGroupApiController::class, 'store']);
    Route::post('study-groups/{id}/join', [StudyGroupApiController::class, 'join']);

    Route::get('forums', [ForumApiController::class, 'index']);
    Route::post('forums/threads', [ForumApiController::class, 'storeThread']);
    Route::post('forums/threads/{id}/posts', [ForumApiController::class, 'storePost']);

    // Learning Paths
    Route::get('learning-paths', [LearningPathApiController::class, 'index']);
    Route::post('learning-paths/{id}/progress', [LearningPathApiController::class, 'updateProgress']);

    // Portfolio
    Route::get('portfolio', [PortfolioApiController::class, 'index']);
    Route::post('portfolio/items', [PortfolioApiController::class, 'storeItem']);

    // Study Sessions
    Route::post('study-sessions', [StudySessionApiController::class, 'start']);
    Route::post('study-sessions/{id}/end', [StudySessionApiController::class, 'end']);

    // Intervention Alerts
    Route::get('intervention-alerts', [InterventionAlertApiController::class, 'index']);

    // Recommendations
    Route::get('recommendations', [ContentRecommendationApiController::class, 'index']);

    // AI Assistant
    Route::post('ai-assistant/chat', [AIAssistantApiController::class, 'chat']);
    Route::get('ai-assistant/history', [AIAssistantApiController::class, 'history']);
});

require __DIR__ . '/db.php';
