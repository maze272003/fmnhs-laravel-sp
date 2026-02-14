# FMNHS SIS - MASTER TODO LIST

## 1. CRITICAL BLOCKERS (FIX FIRST)

### Authentication & Parent Portal

* [ ] Add `parent` guard in `config/auth.php`

* [ ] Add `parents` provider in `config/auth.php`

* [ ] Verify middleware `auth:parent` works correctly

* [ ] Fix parent routes calling missing controller methods

* [ ] Align route method names with controller implementations

* [ ] Rename controller methods if needed to match routes

---

### Route-Controller Contract Fix (GLOBAL PASS)

* [x] Audit routes between `routes/web.php:238-422`
* [x] Implement or remove missing controller methods:

#### Admin

* [x] `AdminAnalyticsController::students`
* [x] `AdminAnalyticsController::teachers`
* [x] `AdminSubjectController::destroy`
* [x] `AdminSubjectController::forceDelete`

#### AI / Conference / Classroom APIs

* [x] `AIAssistantApiController::chat`
* [x] `BreakoutRoomApiController::autoAssign`
* [x] `BreakoutRoomApiController::endAll`
* [x] `CaptionApiController::search`

#### Forum / Game / Learning APIs

* [x] `ForumApiController::index`
* [x] `ForumApiController::storeThread`
* [x] `ForumApiController::storePost`
* [x] `GameApiController::submitScore`
* [x] `LearningPathApiController::updateProgress`

#### Portfolio / Presentation / Whiteboard APIs

* [x] `PortfolioApiController::index`
* [x] `PortfolioApiController::storeItem`
* [x] `PresentationApiController::slideAnalytics`
* [x] `WhiteboardApiController::save`
* [x] `WhiteboardApiController::load`

#### Student Controllers

* [x] `StudentLearningPathController::updateProgress`
* [x] `StudentPortfolioController::storeItem`
* [x] `StudentPortfolioController::updateItem`
* [x] `StudentPortfolioController::destroyItem`
* [x] `StudentPortfolioController::storeReflection`
* [x] `StudentStudyController::startSession`
* [x] `StudentStudyController::endSession`
* [x] `StudentStudyController::storeGoal`
* [x] `StudentStudyController::updateGoal`

#### Bulk / Reports / Seating

* [x] `BulkActionController::bulkGrades`

* [x] `BulkActionController::bulkAttendance`

* [x] `BulkActionController::duplicateAssignments`

* [x] `ProgressReportController::show`

* [x] `ProgressReportController::send`

* [x] `SeatingController::update`

* [x] `SeatingController::autoArrange`

* [ ] Add CI static route-contract check

---

### Authorization Bugs

* [x] Separate student-only API routes
* [x] Separate teacher-only API routes
* [x] Use explicit guards (`Auth::guard('student')`)
* [ ] Remove reliance on `Auth::id()` across mixed roles

---

### Announcement Security

* [ ] Remove hardcoded email authorization
* [x] Add ownership fields:

  * [x] `created_by_type`
  * [x] `created_by_id`
* [x] Implement Laravel Policy for Announcement deletion

---

---

# 2. CONFIRMED UNFINISHED WORK

### Parent Portal UI

* [ ] Fix layout wiring for parent portal

* [x] Create missing layouts:

  * [x] `layouts/app.blade.php`
  * [x] `layouts/parent.blade.php`

* [ ] Pass announcements data to parent dashboard controller

* [ ] Replace dead quick-action links with real routes

---

### Missing View Files

Create:

* [x] `admin/alerts/index.blade.php`
* [x] `admin/workload/index.blade.php`
* [x] `admin/analytics/index.blade.php`
* [x] `student/learning-paths/index.blade.php`
* [x] `student/portfolios/index.blade.php`
* [x] `student/study/index.blade.php`
* [x] `teacher/reports/index.blade.php`
* [x] `teacher/seating/index.blade.php`
* [x] `parent/auth/login.blade.php`

---

### Broken Route Names

* [ ] Normalize route names referenced in controllers
* [ ] Add missing named routes where required

---

### Fake Export Implementations

* [ ] Replace Portfolio TXT export with real PDF generation
* [ ] Replace Progress Report TXT export with real PDF generation

---

---

# 3. FRONTEND IMPROVEMENTS

### Layout Standardization

* [x] Create unified base layouts:

  * [x] `layouts.app`
  * [x] `layouts.student`
  * [x] `layouts.teacher`
  * [x] `layouts.admin`
  * [x] `layouts.parent`

* [ ] Convert all views to extend role-specific layout

* [ ] all pages have a action most have a modal validation for confirmation optional use the reusable if need for maximize the code quality structure
---

### Parent Feature Completion

* [x] Children list page
* [x] Child grades page
* [x] Attendance page
* [x] Schedule page
* [x] Assignments page
* [x] Messaging page

---

### View Naming Cleanup

* [ ] Fix singular/plural mismatches
  Example:

  * `student/portfolio/index` -> `student.portfolios.index`

---

### UX Fixes

* [ ] Replace all `href="#"` placeholders
* [ ] Standardize empty-state UI
* [ ] Fix parent login error display to use Blade `@error`

---

---

# 4. BACKEND IMPROVEMENTS

### Route Stabilization Sprint

* [ ] Freeze route signatures
* [ ] Align controller parameter names globally

---

### Authorization Policies

Create policies for:

* [x] Announcement
* [x] VideoConference
* [x] Portfolio
* [x] StudySession
* [x] LearningPath
* [x] ProgressReport

---

### Model Binding Fix

* [ ] Fix `{id}` routes expecting typed model instances

---

### Abuse Protection

* [x] Add login throttling
* [x] Add API throttling middleware
* [ ] Add rate limits to sensitive endpoints

---

### Analytics Contract Fix

* [x] Align `AdminAnalyticsController::exportReport()`
* [x] Ensure service receives correct payload format

---

---

# 5. MIGRATIONS & DATA MODEL

### Schedule Schema Normalization

* [ ] Stop using string `room`
* [ ] Fully migrate to `room_id`
* [ ] Fully migrate to `school_year_id`
* [ ] Fix Schedule model fillables

---

### Add Composite Unique Constraints

* [ ] submissions `(assignment_id, student_id)`
* [ ] attendances `(student_id, subject_id, date)`
* [ ] grades `(student_id, subject_id, teacher_id, quarter, school_year_id)`
* [ ] parent_student `(parent_id, student_id)`
* [ ] study_group_members `(study_group_id, student_id)`

---

### Video Conference Cleanup

* [ ] Remove deprecated `video_conferences.password`
* [ ] Keep only `secret_key_hash`

---

---

# 6. SECURITY HARDENING

* [ ] Ensure DB reset route fully removed

* [ ] Ensure all secrets rotated

* [x] Add login throttling per role:

  * [x] student
  * [x] teacher
  * [x] admin
  * [x] parent

* [ ] Restrict conference uploads by MIME type

* [ ] Remove static default password for teacher creation

* [ ] Stop emailing raw student passwords

* [x] Add Security Headers Middleware:

  * [x] CSP
  * [x] HSTS

* [ ] Include reCAPTCHA partial in all login pages

# 7. Code Cleanup & Simplification

* [ ] Remove unused controllers, services, models, helpers, and middleware
* [ ] Remove unused routes (web + api)
* [ ] Remove unused Blade views and components
* [ ] Remove unused JS/CSS assets
* [ ] Remove unused migrations or abandoned schema columns
* [ ] Remove dead feature flags and commented legacy code

---

## Reuse & Refactor Repeated Logic

* [ ] Extract duplicated controller logic into reusable Services
* [ ] Move repeated DB queries into Repository / Query classes
* [ ] Create shared FormRequest validation classes for repeated validation rules
* [ ] Move repeated file upload logic into a single UploadService
* [ ] Move repeated PDF/export logic into a dedicated ExportService
* [ ] Create reusable Notification service instead of inline notification code
* [ ] Centralize API response format (success/error JSON helper)

---

## Controller & Route Simplification

* [ ] Convert fat controllers into Service-based architecture
* [ ] Remove duplicate endpoints performing same action
* [ ] Standardize REST naming (index/show/store/update/destroy)
* [ ] Group routes by role (`student`, `teacher`, `admin`, `parent`)
* [ ] Remove controller methods not used by any route

---

## Model Cleanup

* [ ] Remove unused relationships in models
* [ ] Remove unused `$fillable` or `$casts` entries
* [ ] Remove legacy accessors/mutators not used
* [ ] Standardize timestamps and soft delete usage
* [ ] Move heavy logic from models into Services

---

## Blade / Frontend Cleanup

* [ ] Extract repeated UI into Blade components
* [ ] Extract repeated tables into reusable table component
* [ ] Extract repeated forms into partials
* [ ] Remove inline JS duplicated across pages
* [ ] Move shared scripts into global JS file
* [ ] Standardize flash message component
* [ ] Standardize modal component usage

---

## Laravel Best Practice Improvements

* [ ] Replace manual validation with FormRequest classes
* [ ] Replace inline authorization checks with Policies
* [ ] Replace repeated `Auth::user()` role checks with middleware
* [ ] Replace hardcoded config values with config files
* [ ] Replace inline env() calls outside config with config()

---

## Performance & Query Cleanup

* [ ] Remove N+1 queries using eager loading
* [ ] Replace repeated joins with reusable scopes
* [ ] Cache heavy dashboard queries
* [ ] Add indexes for frequently filtered columns
* [ ] Remove debug logs in production

---

## Naming & Structure Consistency

* [ ] Standardize naming conventions (singular vs plural)
* [ ] Align folder structure per role module
* [ ] Rename unclear classes to descriptive names
* [ ] Remove duplicate utility/helper functions

---

## Safety Before Removing Anything

* [ ] Search usage before deleting files (`global search`)
* [ ] Check routes referencing the file
* [ ] Check Blade includes referencing the file
* [ ] Check Service container bindings
* [ ] Run full test suite after cleanup

---

## Optional Advanced Cleanup (Recommended for Large SIS)

* [ ] Introduce BaseController for shared logic
* [ ] Introduce BaseRepository pattern for DB access
* [ ] Introduce BaseService pattern
* [ ] Add shared trait for pagination/filter logic
* [ ] Add shared trait for activity logging
* [ ] Introduce centralized Exception Handler responses
* [ ] Introduce unified API Resource transformers

---
