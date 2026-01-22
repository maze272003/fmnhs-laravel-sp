# Phase 2 Tasks

## Overview
Phase 2 focuses on creating the service layer, which encapsulates business logic and provides a clean separation between controllers and repositories.

## Objectives
1. Create service interfaces for all business operations
2. Implement concrete service classes
3. Create base service class for common functionality
4. Register services in service providers
5. Begin controller refactoring

---

## Task 2.1: Create Service Interfaces

### 2.1.1 AuthServiceInterface
**File:** `app/Contracts/Services/AuthServiceInterface.php`

**Methods:**
- `login(string $email, string $password, string $guard): bool`
- `logout(string $guard): void`
- `register(array $data, string $role): User`

**Dependencies:**
- UserRepositoryInterface

### 2.1.2 GradeServiceInterface
**File:** `app/Contracts/Services/GradeServiceInterface.php`

**Methods:**
- `recordGrade(int $studentId, int $subjectId, int $teacherId, int $quarter, float $grade): Grade`
- `updateGrade(int $gradeId, float $value): bool`
- `getStudentGrades(int $studentId): Collection`
- `getClassGrades(int $subjectId, int $sectionId): Collection`
- `generateReportCard(int $studentId): string`
- `calculateQuarterlyAverage(int $studentId, int $subjectId, int $quarter): float`

**Dependencies:**
- GradeRepositoryInterface
- StudentRepositoryInterface
- SubjectRepositoryInterface

### 2.1.3 AttendanceServiceInterface
**File:** `app/Contracts/Services/AttendanceServiceInterface.php`

**Methods:**
- `markAttendance(array $data): bool`
- `getAttendanceSheet(int $subjectId, int $sectionId, string $date): Collection`
- `getStudentAttendanceSummary(int $studentId, int $subjectId): array`
- `updateAttendance(int $attendanceId, string $status): bool`

**Dependencies:**
- AttendanceRepositoryInterface
- StudentRepositoryInterface
- ScheduleRepositoryInterface

### 2.1.4 AssignmentServiceInterface
**File:** `app/Contracts/Services/AssignmentServiceInterface.php`

**Methods:**
- `createAssignment(array $data, $file): Assignment`
- `getAssignmentsForStudent(int $studentId): Collection`
- `getAssignmentsForTeacher(int $teacherId): Collection`
- `getAssignmentDetails(int $assignmentId): Assignment`
- `updateAssignment(int $assignmentId, array $data): bool`
- `deleteAssignment(int $assignmentId): bool`

**Dependencies:**
- AssignmentRepositoryInterface
- TeacherRepositoryInterface
- StudentRepositoryInterface
- FileHelper

### 2.1.5 SubmissionServiceInterface
**File:** `app/Contracts/Services/SubmissionServiceInterface.php`

**Methods:**
- `submitAssignment(int $assignmentId, int $studentId, $file): Submission`
- `getSubmissionsForAssignment(int $assignmentId): Collection`
- `getSubmissionsForStudent(int $studentId): Collection`
- `gradeSubmission(int $submissionId, float $grade): bool`

**Dependencies:**
- SubmissionRepositoryInterface
- AssignmentRepositoryInterface
- StudentRepositoryInterface
- FileHelper

### 2.1.6 NotificationServiceInterface
**File:** `app/Contracts/Services/NotificationServiceInterface.php`

**Methods:**
- `sendStudentAccountEmail(Student $student, string $password): void`
- `sendAssignmentNotification(Assignment $assignment): void`
- `sendAnnouncementEmail(Announcement $announcement, array $recipients): void`

**Dependencies:**
- Laravel Mail facade
- StudentRepositoryInterface

### 2.1.7 ReportServiceInterface
**File:** `app/Contracts/Services/ReportServiceInterface.php`

**Methods:**
- `generateStudentReportCard(int $studentId): string`
- `generateAttendanceReport(int $sectionId, string $dateRange): string`
- `generateClassReport(int $subjectId, int $sectionId): array`

**Dependencies:**
- GradeRepositoryInterface
- AttendanceRepositoryInterface
- StudentRepositoryInterface
- PDFHelper

### 2.1.8 DashboardServiceInterface
**File:** `app/Contracts/Services/DashboardServiceInterface.php`

**Methods:**
- `getTeacherDashboardData(int $teacherId): array`
- `getStudentDashboardData(int $studentId): array`
- `getAdminDashboardData(): array`

**Dependencies:**
- TeacherRepositoryInterface
- StudentRepositoryInterface
- GradeRepositoryInterface
- AttendanceRepositoryInterface
- AnnouncementRepositoryInterface
- ScheduleRepositoryInterface

---

## Task 2.2: Create Base Service Class

### 2.2.1 BaseService
**File:** `app/Services/BaseService.php`

**Features:**
- Error handling with try-catch blocks
- Logging support using Laravel Log facade
- Validation helpers
- Common service methods

**Methods:**
- `handleException(\Exception $e): void`
- `logInfo(string $message, array $context = []): void`
- `logError(string $message, array $context = []): void`

---

## Task 2.3: Implement Concrete Services

### 2.3.1 AuthService
**File:** `app/Services/AuthService.php`

**Functionality:**
- Login with email and password
- Logout user
- Register new user
- Password reset (future)

**Implementation Notes:**
- Use Laravel's Auth facade
- Validate credentials
- Handle different guards (web, teacher, admin, student)

### 2.3.2 GradeService
**File:** `app/Services/GradeService.php`

**Functionality:**
- Record grades with validation
- Update grades
- Get student grades by quarter
- Get class grades for teacher
- Calculate averages
- Generate report cards

**Implementation Notes:**
- Validate grade range (0-100)
- Check if teacher is assigned to subject
- Handle quarter validation
- Use PDFHelper for report card generation

### 2.3.3 AttendanceService
**File:** `app/Services/AttendanceService.php`

**Functionality:**
- Mark attendance for entire class
- Get attendance sheet for grading
- Get student attendance summary
- Update attendance status

**Implementation Notes:**
- Validate attendance status (Present, Absent, Late, Excused)
- Check if teacher is assigned to subject
- Handle date validation
- Use DateHelper for quarter calculation

### 2.3.4 AssignmentService
**File:** `app/Services/AssignmentService.php`

**Functionality:**
- Create assignments with file attachment
- Get assignments for student
- Get assignments for teacher
- Update assignment details
- Delete assignment

**Implementation Notes:**
- Use FileHelper for file upload
- Validate deadline is future date
- Check teacher permissions
- Handle file deletion on update/delete

### 2.3.5 SubmissionService
**File:** `app/Services/SubmissionService.php`

**Functionality:**
- Submit assignment with file
- Get submissions for assignment
- Get submissions for student
- Grade submission

**Implementation Notes:**
- Validate submission deadline
- Use FileHelper for file upload
- Check if assignment exists
- Handle file deletion on resubmit

### 2.3.6 NotificationService
**File:** `app/Services/NotificationService.php`

**Functionality:**
- Send student account creation email
- Send assignment notification
- Send announcement email

**Implementation Notes:**
- Use Laravel Mail facade
- Queue emails for better performance
- Handle email failures gracefully
- Log all email attempts

### 2.3.7 ReportService
**File:** `app/Services/ReportService.php`

**Functionality:**
- Generate student report card
- Generate attendance report
- Generate class report

**Implementation Notes:**
- Use PDFHelper for PDF generation
- Cache report data
- Handle large datasets with pagination
- Validate date ranges

---

## Task 2.4: Create Service Provider

### 2.4.1 ServiceServiceProvider
**File:** `app/Providers/ServiceServiceProvider.php`

**Bindings:**
```php
$this->app->bind(AuthServiceInterface::class, AuthService::class);
$this->app->bind(GradeServiceInterface::class, GradeService::class);
$this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
$this->app->bind(AssignmentServiceInterface::class, AssignmentService::class);
$this->app->bind(SubmissionServiceInterface::class, SubmissionService::class);
$this->app->bind(NotificationServiceInterface::class, NotificationService::class);
$this->app->bind(ReportServiceInterface::class, ReportService::class);
$this->app->bind(DashboardServiceInterface::class, DashboardService::class);
```

### 2.4.2 Register Provider
**File:** `config/app.php`

Add to providers array:
```php
App\Providers\ServiceServiceProvider::class,
```

---

## Task 2.5: Begin Controller Refactoring

### 2.5.1 Refactor AdminStudentController
**File:** `app/Http/Controllers/Admin/AdminStudentController.php`

**Changes:**
- Inject StudentRepositoryInterface in constructor
- Inject AuthServiceInterface in constructor
- Inject NotificationServiceInterface in constructor
- Replace direct model calls with service methods
- Use form request validation

### 2.5.2 Refactor GradeController
**File:** `app/Http/Controllers/GradeController.php`

**Changes:**
- Inject GradeServiceInterface in constructor
- Remove business logic from controller
- Use service methods for grade operations

### 2.5.3 Refactor AttendanceController
**File:** `app/Http/Controllers/AttendanceController.php`

**Changes:**
- Inject AttendanceServiceInterface in constructor
- Remove business logic from controller
- Use service methods for attendance operations

---

## Task 2.6: Create Form Request Classes

### 2.6.1 Auth Requests
- LoginRequest
- RegisterRequest

### 2.6.2 Grade Requests
- StoreGradeRequest
- UpdateGradeRequest

### 2.6.3 Attendance Requests
- StoreAttendanceRequest

### 2.6.4 Assignment Requests
- CreateAssignmentRequest
- UpdateAssignmentRequest

### 2.6.5 Submission Requests
- SubmitAssignmentRequest

---

## Success Criteria

- [ ] All 8 service interfaces created
- [ ] BaseService class created
- [ ] All 7 concrete services implemented
- [ ] ServiceServiceProvider created and registered
- [ ] At least 3 controllers refactored to use services
- [ ] Form request classes created for refactored controllers
- [ ] All services have unit tests
- [ ] No regression bugs in refactored controllers

---

## Estimated Timeline

| Task | Estimated Time |
|------|----------------|
| Create service interfaces | 2-3 hours |
| Create BaseService | 1 hour |
| Implement AuthService | 2 hours |
| Implement GradeService | 3-4 hours |
| Implement AttendanceService | 2-3 hours |
| Implement AssignmentService | 2-3 hours |
| Implement SubmissionService | 2 hours |
| Implement NotificationService | 1-2 hours |
| Implement ReportService | 2-3 hours |
- Implement DashboardService | 2 hours |
| Create ServiceServiceProvider | 0.5 hours |
| Refactor 3 controllers | 4-5 hours |
| Create form requests | 2 hours |
| Write unit tests | 4-5 hours |
| **Total** | **26-33 hours** |

---

## Dependencies

**Prerequisites:**
- Phase 1 must be complete (repository interfaces and implementations)
- Helper classes should be created (DateHelper, FileHelper, PDFHelper)

**Required for Phase 3:**
- Phase 2 must be complete before full controller refactoring

---

## Notes

### Design Decisions
1. **Service Layer Pattern** - All business logic in services, controllers thin
2. **Dependency Injection** - Inject interfaces, not concrete classes
3. **Error Handling** - Services throw exceptions, controllers handle them
4. **Validation** - Use form requests for input validation
5. **Logging** - All service operations logged for debugging

### Best Practices
- Keep services focused on business logic
- Use repositories for data access
- Don't use models directly in services
- Validate all inputs before processing
- Handle exceptions gracefully
- Log all important operations

### Testing Strategy
- Unit tests for each service method
- Mock repository dependencies
- Test error scenarios
- Test validation rules
- Integration tests for critical flows

---

**Last Updated:** January 22, 2026
**Status:** Ready to begin
