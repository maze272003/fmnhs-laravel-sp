# FMNHS Laravel School Portal - Codebase Documentation

## Project Overview
**Project Name:** FMNHS Laravel School Portal (School Management System)
**Framework:** Laravel 12.0
**PHP Version:** ^8.2
**Purpose:** A comprehensive school management system for administrators, teachers, and students

## Current Architecture

### Directory Structure
```
app/
├── Contracts/
│   ├── Repositories/
│   │   ├── BaseRepositoryInterface.php
│   │   ├── StudentRepositoryInterface.php
│   │   ├── TeacherRepositoryInterface.php
│   │   ├── AdminRepositoryInterface.php
│   │   ├── SectionRepositoryInterface.php
│   │   ├── SubjectRepositoryInterface.php
│   │   ├── GradeRepositoryInterface.php
│   │   ├── ScheduleRepositoryInterface.php
│   │   ├── AttendanceRepositoryInterface.php
│   │   ├── AssignmentRepositoryInterface.php
│   │   ├── SubmissionRepositoryInterface.php
│   │   ├── AnnouncementRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   └── Services/
│       ├── AuthServiceInterface.php
│       ├── GradeServiceInterface.php
│       ├── AttendanceServiceInterface.php
│       ├── AssignmentServiceInterface.php
│       ├── SubmissionServiceInterface.php
│       ├── NotificationServiceInterface.php
│       ├── ReportServiceInterface.php
│       └── DashboardServiceInterface.php
├── Http/
│   └── Controllers/
│       ├── Admin/
│       │   ├── AdminAnnouncementController.php
│       │   ├── AdminAttendanceController.php
│       │   ├── AdminDashboardController.php
│       │   ├── AdminScheduleController.php
│       │   ├── AdminStudentController.php
│       │   ├── AdminSubjectController.php
│       │   └── AdminTeacherController.php
│       ├── Student/
│       │   ├── StudentAssignmentController.php
│       │   ├── StudentAttendanceController.php
│       │   ├── StudentController.php
│       │   ├── StudentDashboardController.php
│       │   └── StudentProfileController.php
│       ├── Teacher/
│       │   ├── AssignmentController.php
│       │   ├── AttendanceController.php
│       │   ├── TeacherAnnouncementController.php
│       │   └── TeacherController.php
│       ├── AdminAuthController.php
│       ├── AuthController.php
│       ├── Controller.php
│       └── TeacherAuthController.php
├── Mail/
│   ├── AnnouncementMail.php
│   ├── NewAssignmentNotification.php
│   └── StudentAccountCreated.php
├── Models/
│   ├── Admin.php
│   ├── Announcement.php
│   ├── Assignment.php
│   ├── Attendance.php
│   ├── Grade.php
│   ├── Schedule.php
│   ├── Section.php
│   ├── Student.php
│   ├── Submission.php
│   ├── Subject.php
│   ├── Teacher.php
│   └── User.php
 ├── Repositories/
 │   └── Eloquent/
 ├── Providers/
 │   ├── AppServiceProvider.php
 │   ├── RepositoryServiceProvider.php (✅ Complete)
 │   └── ServiceServiceProvider.php (✅ Complete)
│       ├── BaseRepository.php
│       ├── StudentRepository.php
│       ├── TeacherRepository.php
│       ├── AdminRepository.php
│       ├── SectionRepository.php
│       ├── SubjectRepository.php
│       ├── GradeRepository.php
│       ├── ScheduleRepository.php
│       ├── AttendanceRepository.php
│       ├── AssignmentRepository.php
│       ├── SubmissionRepository.php
│       ├── AnnouncementRepository.php
│       └── UserRepository.php
 ├── Services/
 │   ├── BaseService.php (✅ Complete)
 │   ├── AuthService.php (✅ Complete)
 │   ├── GradeService.php (✅ Complete)
 │   ├── AttendanceService.php (✅ Complete)
 │   ├── AssignmentService.php (✅ Complete)
 │   ├── SubmissionService.php (✅ Complete)
 │   ├── NotificationService.php (✅ Complete)
 │   ├── ReportService.php (✅ Complete)
 │   └── DashboardService.php (✅ Complete)
├── Support/
│   └── Exceptions/
│       └── RepositoryException.php
 └── Providers/
     ├── AppServiceProvider.php
     ├── RepositoryServiceProvider.php (✅ Complete)
     └── ServiceServiceProvider.php (✅ Complete)

database/
├── migrations/
└── seeders/
```

### Database Schema

#### Core Tables
1. **students** - Student records
   - id, lrn, first_name, last_name, email, password, section_id, avatar
   - Relationships: belongsTo Section, hasMany Grade, hasMany Submission

2. **teachers** - Teacher records
   - id, employee_id, first_name, last_name, email, password, department
   - Soft deletes enabled
   - Relationships: hasOne advisorySection, hasMany Grade

3. **admins** - Administrator records
   - id, name, email, password

4. **sections** - Class sections
   - id, name, grade_level, strand, teacher_id (advisor)
   - Relationships: belongsTo Teacher (advisor), hasMany Student, hasMany Schedule

5. **subjects** - Subject offerings
   - id, code, name, description
   - Soft deletes enabled
   - Relationships: hasMany Grade

6. **grades** - Student grades
   - id, student_id, teacher_id, subject_id, quarter, grade_value
   - Relationships: belongsTo Student, Teacher, Subject

7. **schedules** - Class schedules
   - id, section_id, subject_id, teacher_id, day, start_time, end_time, room
   - Relationships: belongsTo Section, Subject, Teacher

8. **assignments** - Student assignments
   - id, teacher_id, subject_id, section_id, title, description, file_path, deadline
   - Relationships: belongsTo Teacher, Subject, Section, hasMany Submission

9. **submissions** - Assignment submissions
   - id, assignment_id, student_id, file_path, submitted_at
   - Relationships: belongsTo Assignment, Student

10. **attendances** - Attendance records
    - id, student_id, subject_id, teacher_id, section_id, date, status
    - Relationships: belongsTo Student, Subject, Teacher, Section

11. **announcements** - School announcements
    - id, title, content, author_name, role, image

### Authentication System
Multiple authentication guards configured:
- `web` - Default user authentication
- `student` - Student-specific authentication
- `teacher` - Teacher-specific authentication
- `admin` - Administrator authentication

### Routing Structure
All routes in `web.php`, grouped by authentication middleware:
- Public routes (login forms)
- Student-protected routes (`/student/*`)
- Teacher-protected routes (`/teacher/*`)
- Admin-protected routes (`/admin/*`)

## Current Code Patterns

### Repository Pattern (Phase 1 - Implemented ✅)
Repository pattern has been implemented to abstract data access:
- BaseRepositoryInterface with common CRUD operations
- BaseRepository with default implementations
- 12 specific repository interfaces (Student, Teacher, Admin, Section, Subject, Grade, Schedule, Attendance, Assignment, Submission, Announcement, User)
- 12 concrete repository implementations extending BaseRepository
- Custom exception handling via RepositoryException

**Repository Directory Structure:**
```
app/
├── Contracts/
│   └── Repositories/
│       ├── BaseRepositoryInterface.php
│       ├── StudentRepositoryInterface.php
│       ├── TeacherRepositoryInterface.php
│       ├── AdminRepositoryInterface.php
│       └── [9 more repository interfaces]
└── Repositories/
    └── Eloquent/
        ├── BaseRepository.php
        ├── StudentRepository.php
        ├── TeacherRepository.php
        ├── AdminRepository.php
        └── [9 more repository implementations]
```

### Service Layer (Phase 2 - Complete ✅)
All service interfaces and implementations have been completed:
- 8 service interfaces created in app/Contracts/Services/
- 8 service implementations created in app/Services/
- BaseService class with error handling and logging
- Service providers registered in bootstrap/providers.php

**Service Interfaces:**
- AuthServiceInterface ✅
- GradeServiceInterface ✅
- AttendanceServiceInterface ✅
- AssignmentServiceInterface ✅
- SubmissionServiceInterface ✅
- NotificationServiceInterface ✅
- ReportServiceInterface ✅
- DashboardServiceInterface ✅

**Service Implementations:**
- BaseService ✅ - Error handling, logging, validation helpers
- AuthService ✅ - Multi-guard login/logout, password management
- GradeService ✅ - Grade recording, updates, calculations, report cards
- AttendanceService ✅ - Attendance marking, tracking, summaries
- AssignmentService ✅ - CRUD operations, teacher/student queries
- SubmissionService ✅ - Assignment submission, grading
- NotificationService ✅ - Email notifications (welcome, assignments, announcements, grades)
- ReportService ✅ - PDF generation (report cards, attendance, grades, performance)
- DashboardService ✅ - Dashboard data for teacher, student, admin

**Service Providers:**
- RepositoryServiceProvider ✅ - Binds 13 repository interfaces
- ServiceServiceProvider ✅ - Binds 8 service interfaces

### Controller Pattern
Controllers follow basic MVC pattern with:
- Mixed usage: some still using direct Model access, others partially refactored
- Business logic embedded in controller methods
- Limited separation between data access and business logic
- Some controllers ready for repository integration
- Service layer not yet integrated

**Example: AdminStudentController.php (Current)**
```php
public function index(Request $request)
{
    $query = Student::with('section');
    // Filtering logic
    if ($request->filled('search')) {
        // ... search logic
    }
    $students = $query->orderBy('last_name')->paginate(10);
    $sections = Section::all();
    return view('admin.manage_student', compact('students', 'sections'));
}
```

### Model Pattern
Models use Eloquent with:
- Basic relationships defined
- Fillable attributes
- No accessors/mutators (except Student.avatarUrl)
- No query scopes
- No model events

### Code Issues Identified

1. **Business Logic in Controllers** (Partial - Phase 2 In Progress)
   - Validation, data processing, and formatting in controller methods
   - Reusable queries duplicated across controllers
   - No centralized business logic
   - Service layer interfaces defined but not yet implemented

2. **Repository Pattern** (Phase 1 - Complete ✅)
   - Repository interfaces and implementations created
   - Controllers still using direct Model access
   - Need to refactor controllers to use repositories
   - Dependency injection not yet configured

3. **Service Layer** (Phase 2 - In Progress 🚧)
   - Complex operations (e.g., grade calculation) in controllers
   - Service interfaces created but implementations pending
   - No encapsulation of business rules
   - Code duplication across similar features

4. **Lack of Reusable Components**
   - Similar query patterns repeated (e.g., fetching teacher classes)
   - No helper classes or utilities
   - No form request classes for validation

5. **Poor Separation of Concerns**
   - Controllers handling multiple responsibilities
   - Authentication mixed with business logic
   - View preparation in controllers

6. **Interface Definitions** (Partial)
   - Repository contracts created ✅
   - Service contracts created ✅
   - No service provider registrations yet
   - Dependency injection not yet configured

7. **Limited Error Handling**
   - RepositoryException created for repository errors
   - Basic try-catch blocks
   - No ServiceException for service layer
   - No centralized error handling
   - Inconsistent response formats

## Clean Architecture Implementation (In Progress)

### Architecture Overview
The application is being refactored to follow Clean Architecture principles with:
- **Repository Pattern**: Abstracts data access logic
- **Service Layer**: Contains business logic
- **Dependency Injection**: Loose coupling between layers
- **Interface Segregation**: Clear contracts for each layer

### Layered Architecture
```
┌─────────────────────────────────────┐
│       Controllers (Presentation)    │
│      HTTP Request/Response          │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│       Service Layer (Business)      │
│     Business Logic & Rules          │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│     Repository Layer (Data Access)  │
│    Database Operations (Eloquent)   │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│         Database (MySQL)            │
│      Persistent Data Storage        │
└─────────────────────────────────────┘
```

### Data Flow (New Pattern)
**Example: Attendance Marking**
1. Request → `AttendanceController@store`
2. Controller → `AttendanceService->markAttendance()`
3. Service → `AttendanceRepository->markAttendance()`
4. Repository → Database (Eloquent)
5. Response flows back through layers

### Key Benefits
- **Testability**: Each layer can be tested independently
- **Maintainability**: Business logic separated from HTTP handling
- **Scalability**: Easy to swap implementations (e.g., caching, different DB)
- **Reusability**: Services can be called from multiple controllers or commands

## Technology Stack

### Backend
- Laravel 12.0
- PHP ^8.2
- MySQL (database)

### Frontend
- Blade Templates
- TailwindCSS 4.0
- Vite 7.0

### Third-Party Packages
- barryvdh/laravel-dompdf (PDF generation)
- league/flysystem-aws-s3-v3 (S3 file storage)

### Development Tools
- PHPUnit (testing)
- Laravel Pint (code style)
- Laravel Tinker (REPL)
- Laravel Sail (Docker)
- Laravel Pail (log viewer)

## Feature Modules

### 1. Student Module
- Profile management
- View grades and generate PDF report card
- View schedule
- View and submit assignments
- View attendance records

### 2. Teacher Module
- Dashboard with statistics
- Manage classes and students
- Grading system (quarterly grades)
- Attendance tracking
- Assignment creation
- Announcement management

### 3. Admin Module
- Student management (CRUD)
- Teacher management (CRUD, soft deletes)
- Subject management (CRUD, soft deletes)
- Schedule management
- Attendance logs viewing
- Announcements

### 4. Authentication Module
- Multi-guard authentication
- Role-based access control
- Separate login flows for each user type

### 5. Communication Module
- Email notifications (account creation, assignments, announcements)
- Announcement system with image support

## Current Data Flow

### Example: Student Viewing Grades
1. Route: `/student/grades` → `StudentController@grades`
2. Controller: Fetches student ID from auth
3. Query: `Subject::whereHas('grades')...` with eager loading
4. View: Returns `student.grades` blade view
5. View displays data directly from controller

### Example: Teacher Submitting Attendance
1. Route: `/teacher/attendance` → `AttendanceController@store`
2. Controller: Validates request, loops through student statuses
3. Query: `Attendance::updateOrCreate()` for each student
4. Response: Redirects back with success message

## Code Quality Observations

### Strengths
- Clean separation of controller types (Admin, Student, Teacher)
- Proper use of Eloquent relationships
- Multi-authentication properly configured
- Good use of route model binding where applicable

### Weaknesses
- No service layer or repository pattern
- Business logic embedded in controllers
- Code duplication (e.g., teacher class fetching)
- No interface definitions
- Limited testing infrastructure
- No form request classes
- Missing API routes
- No validation classes

## Metrics
- Total Controllers: 15+ files
- Total Models: 12 files
- Total Migrations: 17 files
- Total Seeders: 9 files
- Authentication Guards: 4 (web, student, teacher, admin)
- User Roles: 3 (Admin, Teacher, Student)

## Dependencies on External Services
- Email (SMTP) - for notifications
- AWS S3 - for file storage (avatars, assignment files)
- reCAPTCHA - referenced in partial view

## Configuration Files
- `.env.example` - Environment variables template
- `config/auth.php` - Authentication configuration
- `config/database.php` - Database configuration
- `config/filesystems.php` - File storage configuration
- `config/mail.php` - Email configuration

## Testing Infrastructure
- PHPUnit configured in `phpunit.xml`
- Basic example tests in `tests/` directory
- No feature or unit tests for actual functionality

## Security Considerations
- Password hashing using Laravel's Hash facade
- CSRF protection enabled
- Route middleware for authentication
- No rate limiting observed
- No input sanitization helpers

## Performance Considerations
- Eager loading used in some queries
- Pagination implemented (10 items per page)
- No caching mechanisms observed
- No query optimization monitoring

## Known Issues and Technical Debt
1. Hard-coded queries in multiple places
2. No query optimization
3. Lack of caching
4. No API endpoints
5. Limited error handling
6. No logging strategy
7. Duplicate code patterns
8. Mixed responsibilities in controllers

## Implementation Status (As of January 22, 2026)

### Completed ✅
- **Phase 1: Foundation (100%)**
  - BaseRepositoryInterface with common CRUD operations
  - BaseRepository implementation with error handling
  - 12 repository interfaces (Student, Teacher, Admin, Section, Subject, Grade, Schedule, Attendance, Assignment, Submission, Announcement, User)
  - 12 concrete repository implementations
  - RepositoryException and ServiceException for error handling
  - Documentation updated

- **Phase 2: Service Layer (100%)**
  - 8 service interfaces created
  - BaseService class with error handling, logging, validation helpers
  - 8 service implementations (AuthService, GradeService, AttendanceService, AssignmentService, SubmissionService, NotificationService, ReportService, DashboardService)
  - Service providers created and registered

### Pending ⏳
- **Phase 3: Helper Classes** - Create reusable utility classes
- **Phase 4: Form Request Classes** - Create validation classes
- **Phase 5: Controller Refactoring** - Update all controllers to use repositories and services
- **Phase 6: Model Enhancements** - Add scopes, accessors, and events
- **Phase 7: Testing** - Write comprehensive test suite
- **Phase 8: Documentation** - Complete API and developer documentation
- **Phase 9: Performance & Optimization** - Query optimization and caching

## Recommended Areas for Improvement
1. ~~Implement Repository pattern for data access abstraction~~ ✅ Complete
2. ~~Implement Service layer for business logic~~ ✅ Complete
3. ~~Create Interface contracts~~ ✅ Complete (Repository and Service interfaces created)
4. Extract reusable functions to helper classes (TODO)
5. Implement Form Request classes for validation (TODO)
6. ~~Add comprehensive error handling~~ ✅ Complete
7. ~~Implement logging strategy~~ ✅ Complete (in BaseService)
8. Create API endpoints for frontend integration (TODO)
9. Add comprehensive testing suite (TODO)
10. Implement caching strategy (TODO)
