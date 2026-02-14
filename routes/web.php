<?php

use App\Http\Controllers\Admin\AdminAlertController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminParentController;
use App\Http\Controllers\Admin\AdminWorkloadController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Api\AIAssistantApiController;
use App\Http\Controllers\Api\BreakoutRoomApiController;
use App\Http\Controllers\Api\CaptionApiController;
use App\Http\Controllers\Api\ConferenceMoodApiController;
use App\Http\Controllers\Api\ContentRecommendationApiController;
use App\Http\Controllers\Api\ForumApiController;
use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\InterventionAlertApiController;
use App\Http\Controllers\Api\LearningPathApiController;
use App\Http\Controllers\Api\PortfolioApiController;
use App\Http\Controllers\Api\PresentationApiController;
use App\Http\Controllers\Api\StudyGroupApiController;
use App\Http\Controllers\Api\StudySessionApiController;
use App\Http\Controllers\Api\WhiteboardApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConferenceAccessController;
use App\Http\Controllers\ConferencePlaybackController;
use App\Http\Controllers\Api\ConferenceApiController;
use App\Http\Controllers\Api\ConferenceRecordingController;
use App\Http\Controllers\Api\ConferenceNotificationController;
use App\Http\Controllers\Api\QuizApiController;
use App\Http\Controllers\Api\GamificationApiController;
use App\Http\Controllers\Parent\ParentAuthController;
use App\Http\Controllers\Parent\ParentDashboardController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentLearningPathController;
use App\Http\Controllers\Student\StudentPortfolioController;
use App\Http\Controllers\Student\StudentStudyController;
use App\Http\Controllers\Teacher\BulkActionController;
use App\Http\Controllers\Teacher\LessonPlanController;
use App\Http\Controllers\Teacher\ProgressReportController;
use App\Http\Controllers\Teacher\SeatingController;
use App\Http\Controllers\Teacher\TeacherController;
use App\Http\Controllers\Teacher\VideoConferenceController;
use App\Http\Controllers\TeacherAuthController;
use App\Models\Announcement;
use Illuminate\Support\Facades\Route;


Route::get('/redis-test', function(){
    try {
        Cache::store('redis')->put('health','ok',10);
        return Cache::store('redis')->get('health');
    } catch(Throwable $e){
        return $e->getMessage();
    }
});

// Default Route
Route::get('/', function () {
    $announcements = Announcement::orderBy('created_at', 'desc')->take(3)->get();

    return view('welcome', compact('announcements'));
});

// Authentication Routes
Route::get('/student/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/student/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/teacher/login', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/teacher/login', [TeacherAuthController::class, 'login'])->middleware('throttle:5,1')->name('teacher.login.submit');
Route::post('/teacher/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Public Meeting Join and Room Access
Route::get('/conference/join/{conference}', [ConferenceAccessController::class, 'showJoinForm'])->name('conference.join.form');
Route::post('/conference/join/{conference}', [ConferenceAccessController::class, 'joinWithCredentials'])->name('conference.join.attempt');
Route::post('/conference/join/{conference}/guest/validate', [ConferenceAccessController::class, 'validateGuestKey'])->name('conference.join.guest.validate');
Route::post('/conference/join/{conference}/guest', [ConferenceAccessController::class, 'joinAsGuest'])->name('conference.join.guest');

Route::get('/conference/{conference}/room', [ConferenceAccessController::class, 'room'])->name('conference.room');
Route::get('/conference/{conference}/status', [ConferenceAccessController::class, 'status'])->name('conference.status');

Route::post('/conference/{conference}/messages', [ConferenceApiController::class, 'storeMessage'])->name('conference.messages.store');
Route::post('/conference/{conference}/files', [ConferenceApiController::class, 'uploadFile'])->name('conference.files.upload');
Route::get('/conference/{conference}/messages', [ConferenceApiController::class, 'getMessages'])->name('conference.messages.index');
Route::get('/conference/{conference}/participants', [ConferenceApiController::class, 'getParticipants'])->name('conference.participants.index');
Route::post('/conference/{conference}/events', [ConferenceApiController::class, 'logEvent'])->name('conference.events.store');
Route::post('/conference/{conference}/join-log', [ConferenceApiController::class, 'recordJoin'])->name('conference.join.log');
Route::post('/conference/{conference}/leave-log', [ConferenceApiController::class, 'recordLeave'])->name('conference.leave.log');

// Shared Room Route (teacher or student session)
Route::middleware(['auth:teacher,student'])->group(function () {
    // Conference API â€” Restricted analytics endpoints
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

    // Backward-compatible aliases used by older conference bundles.
    Route::post('/conference/{conference}/join', [ConferenceApiController::class, 'recordJoin']);
    Route::post('/conference/{conference}/leave', [ConferenceApiController::class, 'recordLeave']);

    Route::prefix('api')->middleware('throttle:60,1')->group(function () {
        Route::post('/conference/{conference}/messages', [ConferenceApiController::class, 'storeMessage']);
        Route::post('/conference/{conference}/files', [ConferenceApiController::class, 'uploadFile']);
        Route::get('/conference/{conference}/messages', [ConferenceApiController::class, 'getMessages']);
        Route::get('/conference/{conference}/participants', [ConferenceApiController::class, 'getParticipants']);
        Route::post('/conference/{conference}/events', [ConferenceApiController::class, 'logEvent']);
        Route::post('/conference/{conference}/join', [ConferenceApiController::class, 'recordJoin']);
        Route::post('/conference/{conference}/leave', [ConferenceApiController::class, 'recordLeave']);
        Route::post('/conference/{conference}/join-log', [ConferenceApiController::class, 'recordJoin']);
        Route::post('/conference/{conference}/leave-log', [ConferenceApiController::class, 'recordLeave']);
        Route::get('/conference/{conference}/summary', [ConferenceApiController::class, 'getSummary']);
        Route::get('/conference/{conference}/timeline', [ConferenceApiController::class, 'getTimeline']);

        Route::get('/conference/{conference}/recordings', [ConferenceRecordingController::class, 'index']);
        Route::post('/conference/{conference}/recordings', [ConferenceRecordingController::class, 'store']);
        Route::get('/conference/{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'show']);
        Route::put('/conference/{conference}/recordings/{recording}/chapters', [ConferenceRecordingController::class, 'updateChapters']);
        Route::get('/conference/{conference}/recordings/{recording}/transcript', [ConferenceRecordingController::class, 'transcript']);
        Route::delete('/conference/{conference}/recordings/{recording}', [ConferenceRecordingController::class, 'destroy']);

        Route::get('/conference/notifications', [ConferenceNotificationController::class, 'index']);
        Route::post('/conference/notifications/read', [ConferenceNotificationController::class, 'markRead']);

        // Quiz API
        Route::get('/conference/{conference}/quizzes', [QuizApiController::class, 'index']);
        Route::post('/quizzes', [QuizApiController::class, 'store']);
        Route::get('/quizzes/{quiz}', [QuizApiController::class, 'show']);
        Route::put('/quizzes/{quiz}', [QuizApiController::class, 'update']);
        Route::delete('/quizzes/{quiz}', [QuizApiController::class, 'destroy']);
        Route::post('/quizzes/{quiz}/questions', [QuizApiController::class, 'addQuestion']);
        Route::post('/quizzes/{quiz}/start', [QuizApiController::class, 'start']);
        Route::post('/quizzes/{quiz}/end', [QuizApiController::class, 'end']);
        Route::post('/quizzes/{quiz}/questions/{question}/respond', [QuizApiController::class, 'submitResponse']);
        Route::get('/quizzes/{quiz}/leaderboard', [QuizApiController::class, 'leaderboard']);
        Route::get('/quizzes/{quiz}/results', [QuizApiController::class, 'results']);
        Route::get('/quizzes/{quiz}/statistics', [QuizApiController::class, 'statistics']);
        Route::get('/questions/{question}/results', [QuizApiController::class, 'questionResults']);

        // Gamification API
        Route::get('/gamification/summary', [GamificationApiController::class, 'summary']);
        Route::get('/gamification/leaderboard', [GamificationApiController::class, 'leaderboard']);
        Route::get('/gamification/badges', [GamificationApiController::class, 'badges']);
        Route::get('/gamification/my-badges', [GamificationApiController::class, 'studentBadges']);
        Route::get('/gamification/points-history', [GamificationApiController::class, 'pointsHistory']);
    });
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
    Route::patch('/teacher/conferences/{conference}/privacy', [VideoConferenceController::class, 'updatePrivacy'])->name('teacher.conferences.privacy');
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
    Route::put('/admin/students/{student}', [AdminStudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/admin/students/{student}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');

    // Status Changes
    Route::post('/admin/students/{student}/restore', [AdminStudentController::class, 'restore'])->name('admin.students.restore');
    Route::post('/admin/students/{student}/drop', [AdminStudentController::class, 'dropStudent'])->name('admin.students.drop');
    Route::post('/admin/students/{student}/transfer', [AdminStudentController::class, 'transferStudent'])->name('admin.students.transfer');
    Route::post('/admin/students/{student}/reenroll', [AdminStudentController::class, 'reenrollStudent'])->name('admin.students.reenroll');

    Route::get('admin/students/{student}/print', [App\Http\Controllers\Admin\AdminStudentController::class, 'printRecord'])
        ->name('admin.students.print');

    // FIX: Renamed 'students.show' to 'admin.students.show' to match the View
    Route::get('/admin/students/{student}/record', [AdminStudentController::class, 'show'])->name('admin.students.show');

    // TEACHER MANAGEMENT
    Route::get('/admin/teachers', [AdminTeacherController::class, 'index'])->name('admin.teachers.index');
    Route::put('/admin/teachers/{teacher}', [AdminTeacherController::class, 'update'])->name('admin.teachers.update');
    Route::post('/admin/teachers/{teacher}/archive', [AdminTeacherController::class, 'archive'])->name('admin.teachers.archive');
    Route::post('/admin/teachers/{teacher}/restore', [AdminTeacherController::class, 'restore'])->name('admin.teachers.restore');
    Route::post('/admin/teachers', [AdminTeacherController::class, 'store'])->name('admin.teachers.store');

    // SUBJECT MANAGEMENT
    Route::get('/admin/subjects', [AdminSubjectController::class, 'index'])->name('admin.subjects.index');
    Route::post('/admin/subjects', [AdminSubjectController::class, 'store'])->name('admin.subjects.store');
    Route::put('/admin/subjects/{subject}', [AdminSubjectController::class, 'update'])->name('admin.subjects.update');
    Route::delete('/admin/subjects/{subject}', [AdminSubjectController::class, 'destroy'])->name('admin.subjects.destroy');
    Route::post('/admin/subjects/{subject}/archive', [AdminSubjectController::class, 'archive'])->name('admin.subjects.archive');
    Route::post('/admin/subjects/{subject}/restore', [AdminSubjectController::class, 'restore'])->name('admin.subjects.restore');
    Route::delete('/admin/subjects/{subject}/force-delete', [AdminSubjectController::class, 'forceDelete'])->name('admin.subjects.force-delete');

    // ANNOUNCEMENTS
    Route::get('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::post('/admin/announcements', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::delete('/admin/announcements/{announcement}', [App\Http\Controllers\Admin\AdminAnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');

    // SCHEDULES
    Route::get('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'index'])->name('admin.schedules.index');
    Route::post('/admin/schedules', [App\Http\Controllers\Admin\AdminScheduleController::class, 'store'])->name('admin.schedules.store');
    Route::delete('/admin/schedules/{schedule}', [App\Http\Controllers\Admin\AdminScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // ATTENDANCE & GRADES
    Route::get('/admin/attendance', [App\Http\Controllers\Admin\AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::post('/admin/grades/lock', [App\Http\Controllers\Admin\AdminGradeController::class, 'lockGrades'])->name('admin.grades.lock');
    Route::post('/admin/grades/unlock', [App\Http\Controllers\Admin\AdminGradeController::class, 'unlockGrades'])->name('admin.grades.unlock');

    // UTILITIES (Audit, School Year, Rooms)
    Route::get('/admin/audit-trail', [App\Http\Controllers\Admin\AdminAuditTrailController::class, 'index'])->name('admin.audit-trail.index');

    Route::get('/admin/school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'index'])->name('admin.school-years.index');
    Route::post('/admin/school-years', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'store'])->name('admin.school-years.store');
    Route::post('/admin/school-years/{schoolYear}/activate', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'activate'])->name('admin.school-years.activate');
    Route::post('/admin/school-years/{schoolYear}/close', [App\Http\Controllers\Admin\AdminSchoolYearController::class, 'close'])->name('admin.school-years.close');

    Route::get('/admin/rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'index'])->name('admin.rooms.index');
    Route::post('/admin/rooms', [App\Http\Controllers\Admin\AdminRoomController::class, 'store'])->name('admin.rooms.store');
    Route::put('/admin/rooms/{room}', [App\Http\Controllers\Admin\AdminRoomController::class, 'update'])->name('admin.rooms.update');
    Route::delete('/admin/rooms/{room}', [App\Http\Controllers\Admin\AdminRoomController::class, 'destroy'])->name('admin.rooms.destroy');

    // ADMIN: Analytics
    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics.index');
    Route::get('/admin/analytics/students', [AdminAnalyticsController::class, 'students'])->name('admin.analytics.students');
    Route::get('/admin/analytics/teachers', [AdminAnalyticsController::class, 'teachers'])->name('admin.analytics.teachers');

    // ADMIN: Workload
    Route::get('/admin/workload', [AdminWorkloadController::class, 'index'])->name('admin.workload.index');

    // ADMIN: Parent Management
    Route::get('/admin/parents', [AdminParentController::class, 'index'])->name('admin.parents.index');
    Route::post('/admin/parents', [AdminParentController::class, 'store'])->name('admin.parents.store');
    Route::get('/admin/parents/{parent}', [AdminParentController::class, 'show'])->name('admin.parents.show');
    Route::put('/admin/parents/{parent}', [AdminParentController::class, 'update'])->name('admin.parents.update');
    Route::delete('/admin/parents/{parent}', [AdminParentController::class, 'destroy'])->name('admin.parents.destroy');

    // ADMIN: Intervention Alerts
    Route::get('/admin/alerts', [AdminAlertController::class, 'index'])->name('admin.alerts.index');
    Route::get('/admin/alerts/{alert}', [AdminAlertController::class, 'show'])->name('admin.alerts.show');
    Route::post('/admin/alerts/{alert}/resolve', [AdminAlertController::class, 'resolve'])->name('admin.alerts.resolve');
});

// Parent Portal Routes
Route::middleware(['auth:parent'])->group(function () {
    Route::get('/parent/dashboard', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
    Route::get('/parent/children', [ParentDashboardController::class, 'children'])->name('parent.children');
    Route::get('/parent/children/{id}/grades', [ParentDashboardController::class, 'grades'])->name('parent.children.grades');
    Route::get('/parent/children/{id}/attendance', [ParentDashboardController::class, 'attendance'])->name('parent.children.attendance');
    Route::get('/parent/children/{id}/schedule', [ParentDashboardController::class, 'schedule'])->name('parent.children.schedule');
    Route::get('/parent/children/{id}/assignments', [ParentDashboardController::class, 'assignments'])->name('parent.children.assignments');
    Route::get('/parent/messages', [ParentDashboardController::class, 'messages'])->name('parent.messages');
    Route::post('/parent/messages', [ParentDashboardController::class, 'sendMessage'])->name('parent.messages.send');
});

// Parent Login
Route::get('/parent/login', [ParentAuthController::class, 'showLoginForm'])->name('parent.login');
Route::post('/parent/login', [ParentAuthController::class, 'login'])->middleware('throttle:5,1')->name('parent.login.submit');
Route::post('/parent/logout', [ParentAuthController::class, 'logout'])->name('parent.logout');

// Extended Student Routes
Route::middleware(['auth:student'])->group(function () {
    // Learning Paths
    Route::get('/student/learning-path', [StudentLearningPathController::class, 'index'])->name('student.learning-path.index');
    Route::get('/student/learning-path/{id}', [StudentLearningPathController::class, 'show'])->name('student.learning-path.show');
    Route::post('/student/learning-path/{id}/progress', [StudentLearningPathController::class, 'updateProgress'])->name('student.learning-path.progress');

    // Portfolio
    Route::get('/student/portfolio', [StudentPortfolioController::class, 'index'])->name('student.portfolio.index');
    Route::post('/student/portfolio/items', [StudentPortfolioController::class, 'storeItem'])->name('student.portfolio.items.store');
    Route::put('/student/portfolio/items/{id}', [StudentPortfolioController::class, 'updateItem'])->name('student.portfolio.items.update');
    Route::delete('/student/portfolio/items/{id}', [StudentPortfolioController::class, 'destroyItem'])->name('student.portfolio.items.destroy');
    Route::post('/student/portfolio/reflections', [StudentPortfolioController::class, 'storeReflection'])->name('student.portfolio.reflections.store');

    // Study Tools
    Route::get('/student/study', [StudentStudyController::class, 'index'])->name('student.study.index');
    Route::post('/student/study/sessions', [StudentStudyController::class, 'startSession'])->name('student.study.sessions.start');
    Route::post('/student/study/sessions/{id}/end', [StudentStudyController::class, 'endSession'])->name('student.study.sessions.end');
    Route::post('/student/study/goals', [StudentStudyController::class, 'storeGoal'])->name('student.study.goals.store');
    Route::put('/student/study/goals/{id}', [StudentStudyController::class, 'updateGoal'])->name('student.study.goals.update');
});

// Extended Teacher Routes
Route::middleware(['auth:teacher'])->group(function () {
    // Lesson Plans
    Route::get('/teacher/lesson-plans', [LessonPlanController::class, 'index'])->name('teacher.lesson-plans.index');
    Route::post('/teacher/lesson-plans', [LessonPlanController::class, 'store'])->name('teacher.lesson-plans.store');
    Route::get('/teacher/lesson-plans/{id}', [LessonPlanController::class, 'show'])->name('teacher.lesson-plans.show');
    Route::put('/teacher/lesson-plans/{id}', [LessonPlanController::class, 'update'])->name('teacher.lesson-plans.update');
    Route::delete('/teacher/lesson-plans/{id}', [LessonPlanController::class, 'destroy'])->name('teacher.lesson-plans.destroy');

    // Progress Reports
    Route::get('/teacher/progress-reports', [ProgressReportController::class, 'index'])->name('teacher.progress-reports.index');
    Route::post('/teacher/progress-reports/generate', [ProgressReportController::class, 'generate'])->name('teacher.progress-reports.generate');
    Route::get('/teacher/progress-reports/{id}', [ProgressReportController::class, 'show'])->name('teacher.progress-reports.show');
    Route::post('/teacher/progress-reports/{id}/send', [ProgressReportController::class, 'send'])->name('teacher.progress-reports.send');

    // Seating Arrangements
    Route::get('/teacher/seating', [SeatingController::class, 'index'])->name('teacher.seating.index');
    Route::post('/teacher/seating', [SeatingController::class, 'store'])->name('teacher.seating.store');
    Route::get('/teacher/seating/{arrangement}', [SeatingController::class, 'show'])->name('teacher.seating.show');
    Route::put('/teacher/seating/{id}', [SeatingController::class, 'update'])->name('teacher.seating.update');
    Route::post('/teacher/seating/{id}/auto-arrange', [SeatingController::class, 'autoArrange'])->name('teacher.seating.auto-arrange');

    // Bulk Actions
    Route::post('/teacher/bulk/grades', [BulkActionController::class, 'bulkGrades'])->name('teacher.bulk.grades');
    Route::post('/teacher/bulk/attendance', [BulkActionController::class, 'bulkAttendance'])->name('teacher.bulk.attendance');
    Route::post('/teacher/bulk/assignments', [BulkActionController::class, 'duplicateAssignments'])->name('teacher.bulk.assignments');
    Route::post('/teacher/bulk/email', [BulkActionController::class, 'sendBulkEmail'])->name('teacher.bulk.email');
});

/*
|--------------------------------------------------------------------------
| Extended API Routes for Conference Features
|--------------------------------------------------------------------------
|
| These routes use shared middleware `auth:teacher,student` so both roles
| can access them. Controllers MUST perform explicit guard checks
| (e.g. Auth::guard('teacher')->check()) to enforce role-specific
| permissions where needed. Teacher-only actions include: creating/ending
| breakout rooms, managing presentations, creating/ending games, and
| viewing intervention alerts. Student-facing actions (study groups,
| forums, portfolio, study sessions, learning paths) may remain shared.
|
*/
Route::middleware(['auth:teacher,student'])->group(function () {
    Route::prefix('api')->middleware('throttle:60,1')->group(function () {
        // Whiteboard (shared — both roles collaborate)
        Route::post('/conference/{conference}/whiteboard', [WhiteboardApiController::class, 'save']);
        Route::get('/conference/{conference}/whiteboard', [WhiteboardApiController::class, 'load']);
        Route::delete('/conference/{conference}/whiteboard', [WhiteboardApiController::class, 'clear']);

        // Breakout Rooms (controllers must enforce teacher-only for create/end actions)
        Route::get('/conference/{conference}/breakout-rooms', [BreakoutRoomApiController::class, 'index']);
        Route::post('/conference/{conference}/breakout-rooms', [BreakoutRoomApiController::class, 'store']);
        Route::post('/conference/{conference}/breakout-rooms/auto-assign', [BreakoutRoomApiController::class, 'autoAssign']);
        Route::post('/conference/{conference}/breakout-rooms/{id}/join', [BreakoutRoomApiController::class, 'join']);
        Route::post('/conference/{conference}/breakout-rooms/{id}/leave', [BreakoutRoomApiController::class, 'leave']);
        Route::post('/conference/{conference}/breakout-rooms/end-all', [BreakoutRoomApiController::class, 'endAll']);

        // Mood/Feedback (shared)
        Route::post('/conference/{conference}/mood', [ConferenceMoodApiController::class, 'store']);
        Route::get('/conference/{conference}/mood/aggregate', [ConferenceMoodApiController::class, 'aggregate']);

        // Games (controllers must enforce teacher-only for create/end actions)
        Route::post('/conference/{conference}/games', [GameApiController::class, 'store']);
        Route::post('/conference/{conference}/games/{id}/score', [GameApiController::class, 'submitScore']);
        Route::post('/conference/{conference}/games/{id}/end', [GameApiController::class, 'end']);

        // Captions (shared — read-only)
        Route::get('/conference/{conference}/captions', [CaptionApiController::class, 'index']);
        Route::get('/conference/{conference}/captions/search', [CaptionApiController::class, 'search']);

        // Presentations (controllers must enforce teacher-only for store)
        Route::post('/conference/{conference}/presentations', [PresentationApiController::class, 'store']);
        Route::get('/conference/{conference}/presentations/{id}', [PresentationApiController::class, 'show']);
        Route::get('/conference/{conference}/presentations/{id}/slides/{slide}/analytics', [PresentationApiController::class, 'slideAnalytics']);

        // Study Groups & Forum (shared)
        Route::get('/study-groups', [StudyGroupApiController::class, 'index']);
        Route::post('/study-groups', [StudyGroupApiController::class, 'store']);
        Route::post('/study-groups/{id}/join', [StudyGroupApiController::class, 'join']);
        Route::get('/forums', [ForumApiController::class, 'index']);
        Route::post('/forums/threads', [ForumApiController::class, 'storeThread']);
        Route::post('/forums/threads/{id}/posts', [ForumApiController::class, 'storePost']);

        // Learning Paths (shared)
        Route::get('/learning-paths', [LearningPathApiController::class, 'index']);
        Route::post('/learning-paths/{id}/progress', [LearningPathApiController::class, 'updateProgress']);

        // Portfolio (shared)
        Route::get('/portfolio', [PortfolioApiController::class, 'index']);
        Route::post('/portfolio/items', [PortfolioApiController::class, 'storeItem']);

        // Study Sessions (shared)
        Route::post('/study-sessions', [StudySessionApiController::class, 'start']);
        Route::post('/study-sessions/{id}/end', [StudySessionApiController::class, 'end']);

        // Intervention Alerts (controllers must enforce teacher-only)
        Route::get('/intervention-alerts', [InterventionAlertApiController::class, 'index']);

        // Content Recommendations (shared)
        Route::get('/recommendations', [ContentRecommendationApiController::class, 'index']);

        // AI Assistant (shared)
        Route::post('/ai-assistant/chat', [AIAssistantApiController::class, 'chat']);
        Route::get('/ai-assistant/history', [AIAssistantApiController::class, 'history']);
    });
});

require __DIR__.'/db.php';

