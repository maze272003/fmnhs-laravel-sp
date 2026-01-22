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
└── Providers/
    └── AppServiceProvider.php

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

### Controller Pattern
Controllers follow basic MVC pattern with:
- Direct Model access in controllers
- Business logic embedded in controller methods
- No separation between data access and business logic
- No service or repository layers

**Example: AdminStudentController.php**
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

1. **Business Logic in Controllers**
   - Validation, data processing, and formatting in controller methods
   - Reusable queries duplicated across controllers
   - No centralized business logic

2. **No Repository Pattern**
   - Direct Eloquent calls in controllers
   - Tight coupling between controllers and database
   - Difficult to test and mock database operations
   - No abstraction layer for data access

3. **No Service Layer**
   - Complex operations (e.g., grade calculation) in controllers
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

6. **No Interface Definitions**
   - No contracts for services or repositories
   - Hard to swap implementations
   - Violates dependency inversion principle

7. **Limited Error Handling**
   - Basic try-catch blocks
   - No centralized error handling
   - Inconsistent response formats

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

## Recommended Areas for Improvement
1. Implement Repository pattern for data access abstraction
2. Implement Service layer for business logic
3. Create Interface contracts
4. Extract reusable functions to helper classes
5. Implement Form Request classes for validation
6. Add comprehensive error handling
7. Implement caching strategy
8. Create API endpoints for frontend integration
9. Add comprehensive testing suite
10. Implement logging strategy
