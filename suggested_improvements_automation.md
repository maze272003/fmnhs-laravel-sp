# FMNHS SIS — MASTER TODO LIST

## 🚨 1. CRITICAL BLOCKERS (FIX FIRST)

### 🔴 Authentication & Parent Portal

* [ ] Add `parent` guard in `config/auth.php`

* [ ] Add `parents` provider in `config/auth.php`

* [ ] Verify middleware `auth:parent` works correctly

* [ ] Fix parent routes calling missing controller methods

* [ ] Align route method names with controller implementations

* [ ] Rename controller methods if needed to match routes

---

### 🔴 Route-Controller Contract Fix (GLOBAL PASS)

* [ ] Audit routes between `routes/web.php:238-422`
* [ ] Implement or remove missing controller methods:

#### Admin

* [ ] `AdminAnalyticsController::students`
* [ ] `AdminAnalyticsController::teachers`
* [ ] `AdminSubjectController::destroy`
* [ ] `AdminSubjectController::forceDelete`

#### AI / Conference / Classroom APIs

* [ ] `AIAssistantApiController::chat`
* [ ] `BreakoutRoomApiController::autoAssign`
* [ ] `BreakoutRoomApiController::endAll`
* [ ] `CaptionApiController::search`

#### Forum / Game / Learning APIs

* [ ] `ForumApiController::index`
* [ ] `ForumApiController::storeThread`
* [ ] `ForumApiController::storePost`
* [ ] `GameApiController::submitScore`
* [ ] `LearningPathApiController::updateProgress`

#### Portfolio / Presentation / Whiteboard APIs

* [ ] `PortfolioApiController::index`
* [ ] `PortfolioApiController::storeItem`
* [ ] `PresentationApiController::slideAnalytics`
* [ ] `WhiteboardApiController::save`
* [ ] `WhiteboardApiController::load`

#### Student Controllers

* [ ] `StudentLearningPathController::updateProgress`
* [ ] `StudentPortfolioController::storeItem`
* [ ] `StudentPortfolioController::updateItem`
* [ ] `StudentPortfolioController::destroyItem`
* [ ] `StudentPortfolioController::storeReflection`
* [ ] `StudentStudyController::startSession`
* [ ] `StudentStudyController::endSession`
* [ ] `StudentStudyController::storeGoal`
* [ ] `StudentStudyController::updateGoal`

#### Bulk / Reports / Seating

* [ ] `BulkActionController::bulkGrades`

* [ ] `BulkActionController::bulkAttendance`

* [ ] `BulkActionController::duplicateAssignments`

* [ ] `ProgressReportController::show`

* [ ] `ProgressReportController::send`

* [ ] `SeatingController::update`

* [ ] `SeatingController::autoArrange`

* [ ] Add CI static route-contract check

---

### 🔴 Authorization Bugs

* [ ] Separate student-only API routes
* [ ] Separate teacher-only API routes
* [ ] Use explicit guards (`Auth::guard('student')`)
* [ ] Remove reliance on `Auth::id()` across mixed roles

---

### 🔴 Announcement Security

* [ ] Remove hardcoded email authorization
* [ ] Add ownership fields:

  * [ ] `created_by_type`
  * [ ] `created_by_id`
* [ ] Implement Laravel Policy for Announcement deletion

---

---

# 🟡 2. CONFIRMED UNFINISHED WORK

### Parent Portal UI

* [ ] Fix layout wiring for parent portal

* [ ] Create missing layouts:

  * [ ] `layouts/app.blade.php`
  * [ ] `layouts/parent.blade.php`

* [ ] Pass announcements data to parent dashboard controller

* [ ] Replace dead quick-action links with real routes

---

### Missing View Files

Create:

* [ ] `admin/alerts/index.blade.php`
* [ ] `admin/workload/index.blade.php`
* [ ] `admin/analytics/index.blade.php`
* [ ] `student/learning-paths/index.blade.php`
* [ ] `student/portfolios/index.blade.php`
* [ ] `student/study/index.blade.php`
* [ ] `teacher/reports/index.blade.php`
* [ ] `teacher/seating/index.blade.php`
* [ ] `parent/auth/login.blade.php`

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

# 🟢 3. FRONTEND IMPROVEMENTS

### Layout Standardization

* [ ] Create unified base layouts:

  * [ ] `layouts.app`
  * [ ] `layouts.student`
  * [ ] `layouts.teacher`
  * [ ] `layouts.admin`
  * [ ] `layouts.parent`

* [ ] Convert all views to extend role-specific layout

---

### Parent Feature Completion

* [ ] Children list page
* [ ] Child grades page
* [ ] Attendance page
* [ ] Schedule page
* [ ] Assignments page
* [ ] Messaging page

---

### View Naming Cleanup

* [ ] Fix singular/plural mismatches
  Example:

  * `student/portfolio/index` → `student.portfolios.index`

---

### UX Fixes

* [ ] Replace all `href="#"` placeholders
* [ ] Standardize empty-state UI
* [ ] Fix parent login error display to use Blade `@error`

---

---

# 🔵 4. BACKEND IMPROVEMENTS

### Route Stabilization Sprint

* [ ] Freeze route signatures
* [ ] Align controller parameter names globally

---

### Authorization Policies

Create policies for:

* [ ] Announcement
* [ ] VideoConference
* [ ] Portfolio
* [ ] StudySession
* [ ] LearningPath
* [ ] ProgressReport

---

### Model Binding Fix

* [ ] Fix `{id}` routes expecting typed model instances

---

### Abuse Protection

* [ ] Add login throttling
* [ ] Add API throttling middleware
* [ ] Add rate limits to sensitive endpoints

---

### Analytics Contract Fix

* [ ] Align `AdminAnalyticsController::exportReport()`
* [ ] Ensure service receives correct payload format

---

---

# 🟣 5. MIGRATIONS & DATA MODEL

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

# 🔐 6. SECURITY HARDENING

* [ ] Ensure DB reset route fully removed

* [ ] Ensure all secrets rotated

* [ ] Add login throttling per role:

  * [ ] student
  * [ ] teacher
  * [ ] admin
  * [ ] parent

* [ ] Restrict conference uploads by MIME type

* [ ] Remove static default password for teacher creation

* [ ] Stop emailing raw student passwords

* [ ] Add Security Headers Middleware:

  * [ ] CSP
  * [ ] HSTS

* [ ] Include reCAPTCHA partial in all login pages

---

---

