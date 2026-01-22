# Phase 1.5: Service Interfaces

## Status: Pending Implementation

### Service Interfaces to Create

#### 1. AuthServiceInterface
```php
interface AuthServiceInterface
{
    public function login(string $email, string $password, string $guard): bool;
    public function logout(string $guard): void;
    public function register(array $data, string $role): User;
}
```

#### 2. GradeServiceInterface
```php
interface GradeServiceInterface
{
    public function recordGrade(int $studentId, int $subjectId, int $teacherId, int $quarter, float $grade): Grade;
    public function updateGrade(int $gradeId, float $value): bool;
    public function getStudentGrades(int $studentId): Collection;
    public function getClassGrades(int $subjectId, int $sectionId): Collection;
    public function generateReportCard(int $studentId): string;
    public function calculateQuarterlyAverage(int $studentId, int $subjectId, int $quarter): float;
}
```

#### 3. AttendanceServiceInterface
```php
interface AttendanceServiceInterface
{
    public function markAttendance(array $data): bool;
    public function getAttendanceSheet(int $subjectId, int $sectionId, string $date): Collection;
    public function getStudentAttendanceSummary(int $studentId, int $subjectId): array;
    public function updateAttendance(int $attendanceId, string $status): bool;
}
```

#### 4. AssignmentServiceInterface
```php
interface AssignmentServiceInterface
{
    public function createAssignment(array $data, $file): Assignment;
    public function getAssignmentsForStudent(int $studentId): Collection;
    public function getAssignmentsForTeacher(int $teacherId): Collection;
    public function getAssignmentDetails(int $assignmentId): Assignment;
    public function updateAssignment(int $assignmentId, array $data): bool;
    public function deleteAssignment(int $assignmentId): bool;
}
```

#### 5. SubmissionServiceInterface
```php
interface SubmissionServiceInterface
{
    public function submitAssignment(int $assignmentId, int $studentId, $file): Submission;
    public function getSubmissionsForAssignment(int $assignmentId): Collection;
    public function getSubmissionsForStudent(int $studentId): Collection;
    public function gradeSubmission(int $submissionId, float $grade): bool;
}
```

#### 6. NotificationServiceInterface
```php
interface NotificationServiceInterface
{
    public function sendStudentAccountEmail(Student $student, string $password): void;
    public function sendAssignmentNotification(Assignment $assignment): void;
    public function sendAnnouncementEmail(Announcement $announcement, array $recipients): void;
}
```

#### 7. ReportServiceInterface
```php
interface ReportServiceInterface
{
    public function generateStudentReportCard(int $studentId): string;
    public function generateAttendanceReport(int $sectionId, string $dateRange): string;
    public function generateClassReport(int $subjectId, int $sectionId): array;
}
```

#### 8. DashboardServiceInterface
```php
interface DashboardServiceInterface
{
    public function getTeacherDashboardData(int $teacherId): array;
    public function getStudentDashboardData(int $studentId): array;
    public function getAdminDashboardData(): array;
}
```

## Implementation Priority

1. AuthService - Critical for authentication
2. GradeService - Core functionality
3. AttendanceService - Core functionality
4. AssignmentService - Core functionality
5. NotificationService - Communication
6. ReportService - Reports
7. DashboardService - Dashboard data

## Next Steps

1. Create all service interfaces in app/Contracts/Services/
2. Implement services in app/Services/
3. Create service providers
4. Update controllers to use services
