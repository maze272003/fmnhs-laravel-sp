## FMNHS SIS Improvement Audit (2026-02-14)

### 1) Critical blockers to fix first

1. Public database reset endpoint is exposed on web routes.
   - Evidence: `routes/web.php:426`, `routes/db.php:5`, `routes/db.php:8`, `routes/db.php:49`
   - Risk: Full data loss in production using a static query key.
   - Fix: Remove this route from web, move reset actions to CLI-only (`php artisan ...`) and restrict to local environment.

2. Real credentials/secrets are committed in `.env.example`.
   - Evidence: `.env.example:4`, `.env.example:29`, `.env.example:67`, `.env.example:73`, `.env.example:74`, `.env.example:89`
   - Risk: Account compromise (DB, mail, object storage, realtime channel).
   - Fix: Immediately rotate all leaked secrets and replace with placeholders.

3. Parent auth is routed but not configured in auth guards/providers.
   - Evidence: `routes/web.php:295`, `routes/web.php:306`, `config/auth.php:38`, `config/auth.php:74`
   - Risk: `auth:parent` middleware and login flow can fail at runtime.
   - Fix: Add `parent` guard and `parents` provider in `config/auth.php`.

4. Parent routes reference methods that do not exist.
   - Evidence: `routes/web.php:297` to `routes/web.php:302`, `routes/web.php:306`, `app/Http/Controllers/Parent/ParentDashboardController.php:26`, `app/Http/Controllers/Parent/ParentDashboardController.php:39`, `app/Http/Controllers/Parent/ParentDashboardController.php:51`, `app/Http/Controllers/Parent/ParentAuthController.php:16`
   - Risk: 500 errors on parent portal pages.
   - Fix: Align route method names with controller implementations (or rename controller methods).

5. Route-controller contract drift is widespread across SIS modules.
   - Evidence: `routes/web.php:238` to `routes/web.php:422`
   - Confirmed missing controller methods include:
     - `AdminAnalyticsController::students`, `AdminAnalyticsController::teachers`
     - `AdminSubjectController::destroy`, `AdminSubjectController::forceDelete`
     - `AIAssistantApiController::chat`
     - `BreakoutRoomApiController::autoAssign`, `BreakoutRoomApiController::endAll`
     - `CaptionApiController::search`
     - `ForumApiController::index`, `ForumApiController::storeThread`, `ForumApiController::storePost`
     - `GameApiController::submitScore`
     - `LearningPathApiController::updateProgress`
     - `PortfolioApiController::index`, `PortfolioApiController::storeItem`
     - `PresentationApiController::slideAnalytics`
     - `WhiteboardApiController::save`, `WhiteboardApiController::load`
     - `StudentLearningPathController::updateProgress`
     - `StudentPortfolioController::storeItem`, `StudentPortfolioController::updateItem`, `StudentPortfolioController::destroyItem`, `StudentPortfolioController::storeReflection`
     - `StudentStudyController::startSession`, `StudentStudyController::endSession`, `StudentStudyController::storeGoal`, `StudentStudyController::updateGoal`
     - `BulkActionController::bulkGrades`, `BulkActionController::bulkAttendance`, `BulkActionController::duplicateAssignments`
     - `ProgressReportController::show`, `ProgressReportController::send`
     - `SeatingController::update`, `SeatingController::autoArrange`
   - Fix: Do one route-contract pass and enforce static checks in CI.

6. Mixed role middleware with student-only queries can produce authorization bugs.
   - Evidence: `routes/web.php:89`, `routes/web.php:361`, `app/Http/Controllers/Api/StudySessionApiController.php:31`, `app/Http/Controllers/Api/GameApiController.php:81`, `app/Http/Controllers/Api/ContentRecommendationApiController.php:24`, `app/Http/Controllers/Api/LearningPathApiController.php:63`, `app/Http/Controllers/Api/PortfolioApiController.php:76`, `app/Http/Controllers/Api/PresentationApiController.php:91`
   - Risk: Teacher sessions may resolve wrong student IDs (`Auth::id()` collision).
   - Fix: Split student-only and teacher-only API route groups and use explicit guards (`Auth::guard('student')`).

7. Announcement deletion authorization is weak.
   - Evidence: `app/Http/Controllers/Admin/AdminAnnouncementController.php:59`, `app/Http/Controllers/Admin/AdminAnnouncementController.php:64`, `app/Http/Controllers/Teacher/TeacherAnnouncementController.php:54`, `app/Services/AnnouncementManagementService.php:63`
   - Risk: Hardcoded emails and no ownership checks allow unsafe deletes.
   - Fix: Move to policy-based authorization and store creator IDs (`created_by_type`, `created_by_id`) on announcements.

### 2) Confirmed undone work

1. Missing parent portal pages and layout wiring.
   - Evidence: `resources/views/parent/login.blade.php:1`, `resources/views/parent/dashboard.blade.php:1`, missing `resources/views/layouts/app.blade.php`, missing `resources/views/layouts/parent.blade.php`
   - Additional mismatch: view expects announcements not passed by controller (`resources/views/parent/dashboard.blade.php:69`, `app/Http/Controllers/Parent/ParentDashboardController.php:20`).
   - Additional UX gap: quick actions still dead links (`resources/views/parent/dashboard.blade.php:97`, `resources/views/parent/dashboard.blade.php:107`, `resources/views/parent/dashboard.blade.php:117`).

2. Missing module view folders used by controllers.
   - Missing: `resources/views/admin/alerts/index.blade.php`, `resources/views/admin/workload/index.blade.php`, `resources/views/admin/analytics/index.blade.php`, `resources/views/student/learning-paths/index.blade.php`, `resources/views/student/portfolios/index.blade.php`, `resources/views/student/study/index.blade.php`, `resources/views/teacher/reports/index.blade.php`, `resources/views/teacher/seating/index.blade.php`, `resources/views/parent/auth/login.blade.php`.

3. Broken route names referenced in code.
   - Evidence: `app/Http/Controllers/Teacher/ProgressReportController.php:56`, `app/Http/Controllers/Teacher/SeatingController.php:52`, `app/Http/Controllers/Student/StudentPortfolioController.php:71`, `app/Http/Controllers/Student/StudentStudyController.php:73`, `app/Http/Controllers/Admin/AdminParentController.php:92`, `resources/views/student/portfolio/index.blade.php:100`
   - Fix: Normalize names to currently declared routes or add missing named routes.

4. Placeholder/non-production implementations still in core SIS flows.
   - Portfolio export creates `.txt`, not PDF: `app/Services/PortfolioService.php:67`
   - Progress report "PDF" also `.txt`: `app/Services/ReportGenerationService.php:72`

### 3) Frontend improvements (recommended)

1. Standardize layout architecture.
   - Create role-specific base layouts and convert pages to a common shell (`layouts.app`, `layouts.student`, `layouts.teacher`, `layouts.admin`, `layouts.parent`).

2. Complete parent UX flow.
   - Add children list, child grades, attendance, schedule, assignments, and messaging pages to match routes.

3. Resolve naming drift between singular/plural feature views.
   - Current mismatch example: existing `student/portfolio/index.blade.php` versus controller expecting `student.portfolios.index`.

4. Finish dead UI actions and align to backend endpoints.
   - Replace all `href="#"` placeholders in parent dashboard with real route actions.

5. Add empty-state and error handling consistency.
   - Parent login uses `session('error')` but controller uses validation errors (`withErrors`); wire Blade `@error` output.

### 4) Backend improvements (recommended)

1. Route/API contract stabilization sprint.
   - Freeze route signatures, then align all controller methods and parameter names in one pass.

2. Enforce authorization using policies/gates.
   - Add policies for `Announcement`, `VideoConference`, `Portfolio`, `StudySession`, `LearningPath`, and `ProgressReport`.

3. Fix model binding parameter mismatches.
   - Route uses `{id}` while controller expects typed model:
     - `routes/web.php:240` + `app/Http/Controllers/Admin/AdminSubjectController.php:46`
     - `routes/web.php:285` and `routes/web.php:286` + `app/Http/Controllers/Admin/AdminParentController.php:74`

4. Add request throttling and abuse protection.
   - No current rate limiter usage found for auth/API endpoints; add `throttle` to login and high-frequency API routes.

5. Correct analytics service-controller contract.
   - Controller calls `exportReport($validated)` but service expects report data payload (`app/Http/Controllers/Admin/AdminAnalyticsController.php:59`, `app/Services/AnalyticsAggregationService.php:153`).

### 5) Migration and data-model improvements (recommended)

1. Normalize schedules schema usage.
   - Current code still writes/validates `room` string (`database/migrations/2025_11_25_042154_create_schedules_table.php:23`, `app/Services/ScheduleManagementService.php:20`) while later migration introduces `room_id` and `school_year_id` (`database/migrations/2026_02_06_100005_add_phase2_columns_to_existing_tables.php:38`, `database/migrations/2026_02_06_100005_add_phase2_columns_to_existing_tables.php:47`).
   - Also fix `Schedule` model fillables to use `school_year_id` (currently `school_year`): `app/Models/Schedule.php:21`.

2. Add composite uniqueness constraints for SIS integrity.
   - `submissions`: unique `(assignment_id, student_id)`.
   - `attendances`: unique `(student_id, subject_id, date)`.
   - `grades`: unique `(student_id, subject_id, teacher_id, quarter, school_year_id)`.
   - `parent_student`: unique `(parent_id, student_id)` (`database/migrations/2026_02_12_000037_create_parent_student_table.php:11`).
   - `study_group_members`: unique `(study_group_id, student_id)` (`database/migrations/2026_02_12_000052_create_study_group_members_table.php:11`).

3. Remove or deprecate stale conference password field.
   - Legacy `video_conferences.password` remains alongside `secret_key_hash` (`database/migrations/2026_02_11_000001_enhance_video_conferences_table.php:20`, `database/migrations/2026_02_14_000100_add_privacy_fields_to_video_conferences_table.php:13`, `app/Models/VideoConference.php:20`).

### 6) Security hardening improvements (recommended)

1. Immediately remove the dangerous reset route and rotate all leaked secrets.
2. Add dedicated login throttling (`student`, `teacher`, `admin`, `parent`).
3. Enforce secure upload constraints for conference files.
   - Current validation allows any file type by size only (`app/Http/Controllers/Api/ConferenceApiController.php:59`).
4. Enforce secure credential defaults.
   - Teacher account default password is static `"password"` (`app/Services/TeacherManagementService.php:28`).
   - Student onboarding emails the raw password (`app/Services/StudentLifecycleService.php:24`, `app/Services/StudentLifecycleService.php:34`).
5. Add security headers middleware and CSP/HSTS configuration.
6. Activate bot protection consistently.
   - `resources/views/partials/recaptcha.blade.php` exists but is not included in login views.

### 7) Correct SIS implementation approach (recommended sequence)

1. Stabilization (Week 1-2)
   - Remove critical security exposures.
   - Fix route-controller mismatches and parent auth guard.
   - Make parent portal minimally functional.

2. Data integrity (Week 2-3)
   - Ship normalization migrations and composite unique indexes.
   - Backfill `room_id`, `school_year_id`, and ownership columns.

3. Authorization hardening (Week 3-4)
   - Add policies and split API route middleware by role.
   - Add throttling and audit logging for sensitive actions.

4. Feature completion (Week 4-6)
   - Complete missing views and unfinished modules (alerts, analytics, workload, reports, seating, student study/portfolio/learning paths, parent messaging).

5. Test expansion (parallel)
   - Add feature tests for parent portal, role-isolated APIs, policy enforcement, and migration integrity.
   - Existing tests are strong for enrollment/schedule/quiz/conference but do not cover these incomplete modules.
﻿
