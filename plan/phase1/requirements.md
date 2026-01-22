# FMNHS Laravel School Portal - Requirements

## Functional Requirements

### Authentication & Authorization
- [FR-001] Multi-role authentication system (Student, Teacher, Admin)
- [FR-002] Separate login pages for each user type
- [FR-003] Secure password hashing
- [FR-004] Session management with automatic logout
- [FR-005] Password reset functionality (future requirement)
- [FR-006] Role-based access control for routes and resources

### Student Module
- [FR-007] Student profile management
  - View personal information
  - Update profile details (email, password)
  - Avatar upload/display

- [FR-008] Grade management
  - View grades by subject and quarter
  - Generate PDF report card
  - Display grade value and subjects

- [FR-009] Schedule viewing
  - View class schedule by day and time
  - Display subject, teacher, and room information
  - Filter by section

- [FR-010] Assignment handling
  - View assigned tasks
  - Submit assignments with file upload
  - Track submission status and deadline

- [FR-011] Attendance tracking
  - View personal attendance records
  - Display attendance status by date and subject

- [FR-012] Dashboard
  - View announcements
  - Quick access to main features
  - Personal statistics (future enhancement)

### Teacher Module
- [FR-013] Teacher dashboard
  - View assigned classes count
  - View total students count
  - View advisory class information
  - View recent announcements

- [FR-014] Class management
  - View all assigned classes
  - View class details (subject, section, student count)
  - Access student lists by section

- [FR-015] Grading system
  - View grading sheet by subject and section
  - Input quarterly grades (Q1, Q2, Q3, Q4)
  - Update or modify existing grades
  - Calculate averages (future enhancement)

- [FR-016] Attendance management
  - Select class and date
  - Mark student attendance (Present, Absent, Late, Excused)
  - View attendance records
  - Edit existing attendance records

- [FR-017] Assignment creation
  - Create assignments with title, description, and deadline
  - Attach files to assignments
  - Target specific classes/sections
  - View student submissions
  - Grade submissions (future enhancement)

- [FR-018] Student management (limited)
  - View student profiles
  - View students by section

- [FR-019] Announcement management (teacher-specific)
  - Create announcements
  - Target students (future enhancement)
  - Delete own announcements

### Admin Module
- [FR-020] Admin dashboard
  - System overview statistics
  - Quick access to management modules

- [FR-021] Student management
  - Create new student accounts
  - View student list with pagination
  - Search students (by name, LRN, email)
  - Update student information
  - Delete student accounts
  - Assign students to sections
  - Send account creation emails

- [FR-022] Teacher management
  - Create new teacher accounts
  - View teacher list
  - Search teachers
  - Update teacher information
  - Archive/delete teacher accounts
  - Restore archived teachers
  - Assign teachers as advisors

- [FR-023] Subject management
  - Create subjects (code, name, description)
  - View subject list
  - Update subject information
  - Archive/delete subjects
  - Restore archived subjects

- [FR-024] Section management
  - Create sections (name, grade level, strand)
  - Assign advisors to sections
  - View section list

- [FR-025] Schedule management
  - Create class schedules
  - Assign subjects to sections and teachers
  - Set time, day, and room
  - View schedule list
  - Delete schedules

- [FR-026] Attendance monitoring
  - View all attendance logs
  - Filter by date, section, or subject

- [FR-027] Announcement management
  - Create school-wide announcements
  - Include images in announcements
  - Delete announcements

### Communication Module
- [FR-028] Email notifications
  - Student account creation emails
  - New assignment notifications
  - School announcement emails

- [FR-029] Announcement system
  - Public announcements on landing page
  - Role-specific announcements
  - Announcement history

## Non-Functional Requirements

### Performance
- [NFR-001] Page load time < 2 seconds for authenticated pages
- [NFR-002] Database query optimization with eager loading
- [NFR-003] Pagination for large datasets (10-50 items per page)
- [NFR-004] Efficient file uploads (< 10MB for assignments, < 2MB for avatars)

### Security
- [NFR-005] All user inputs validated and sanitized
- [NFR-006] SQL injection prevention via Eloquent ORM
- [NFR-007] XSS protection via Blade escaping
- [NFR-008] CSRF protection enabled
- [NFR-009] Secure password storage (bcrypt/argon2)
- [NFR-010] Secure file upload validation
- [NFR-011] HTTPS in production
- [NFR-012] Rate limiting on login attempts (future)

### Scalability
- [NFR-013] Support multiple concurrent users
- [NFR-014] Database indexing on frequently queried fields
- [NFR-015] Queue system for email sending
- [NFR-016] Caching strategy for static data (future)

### Reliability
- [NFR-017] Error handling and logging
- [NFR-018] Database transaction support for critical operations
- [NFR-019] Graceful degradation on service failures
- [NFR-020] Backup strategy for data (manual/manual process)

### Usability
- [NFR-021] Responsive design for mobile and desktop
- [NFR-022] Intuitive user interface
- [NFR-023] Clear error messages and success feedback
- [NFR-024] Consistent navigation and layout
- [NFR-025] Accessibility compliance (basic WCAG guidelines)

### Maintainability
- [NFR-026] Clean code structure
- [NFR-027] Documentation for complex logic
- [NFR-028] Version control (Git)
- [NFR-029] Code style consistency (Laravel Pint)
- [NFR-030] Modular architecture (future enhancement)

### Compatibility
- [NFR-031] Cross-browser support (Chrome, Firefox, Safari, Edge)
- [NFR-032] PHP 8.2+ compatibility
- [NFR-033] Database-agnostic design (MySQL/PostgreSQL/SQLite)
- [NFR-034] Mobile-responsive design

### Data Integrity
- [NFR-035] Foreign key constraints
- [NFR-036] Unique constraints on critical fields (email, LRN)
- [NFR-037] Data validation rules
- [NFR-038] Soft deletes for audit trail

## Technical Requirements

### Architecture
- [TR-001] MVC architecture (Laravel)
- [TR-002] RESTful routing
- [TR-003] Multi-guard authentication system
- [TR-004] Repository pattern (future implementation)
- [TR-005] Service layer (future implementation)
- [TR-006] Interface-based design (future implementation)

### Database
- [TR-007] Relational database (MySQL recommended)
- [TR-008] Migration-based schema management
- [TR-009] Seeder for sample data
- [TR-010] Eloquent ORM for database operations
- [TR-011] Database indexing strategy

### File Storage
- [TR-012] S3 integration for file uploads (avatars, assignments)
- [TR-013] Local storage fallback for development
- [TR-014] File validation and sanitization

### Email System
- [TR-015] SMTP configuration
- [TR-016] HTML email templates via Blade
- [TR-017] Queue-based email sending

### PDF Generation
- [TR-018] PDF generation for report cards
- [TR-019] Custom PDF templates

### Testing
- [TR-020] PHPUnit integration
- [TR-021] Feature tests for critical paths
- [TR-022] Unit tests for business logic (future)

### Development Tools
- [TR-023] Docker support via Laravel Sail
- [TR-024] Hot module replacement (Vite)
- [TR-025] Code formatting (Laravel Pint)
- [TR-026] REPL for testing (Tinker)

## Business Rules

### Grading Rules
- [BR-001] Grades must be numeric with 2 decimal places
- [BR-002] Valid grade range: 0.00 to 100.00
- [BR-003] Four quarters per school year (Q1, Q2, Q3, Q4)
- [BR-004] Only teachers can input grades for their assigned subjects
- [BR-005] Students cannot modify their grades

### Attendance Rules
- [BR-006] Attendance statuses: Present, Absent, Late, Excused
- [BR-007] Teachers can only mark attendance for their assigned classes
- [BR-008] Attendance records cannot be deleted, only modified
- [BR-009] Attendance must be recorded per subject and date

### Assignment Rules
- [BR-010] Assignments must have a deadline
- [BR-011] Students can only submit assignments before deadline
- [BR-012] Late submissions accepted (no penalty system yet)
- [BR-013] Only teachers can create assignments
- [BR-014] Assignments can target specific sections/subjects

### User Management Rules
- [BR-015] Student LRN (Learner Reference Number) must be unique
- [BR-016] Email addresses must be unique per user type
- [BR-017] Default password for new students is their LRN
- [BR-018] Soft deletes for teachers and subjects
- [BR-019] Archived records can be restored
- [BR-020] Students can be assigned to one section at a time

### Schedule Rules
- [BR-021] Schedules must have unique combination of section, subject, teacher, day, and time
- [BR-022] No schedule conflicts (overlap detection not implemented)
- [BR-023] Sections can have multiple subjects per day
- [BR-024] Rooms can be shared across classes

### Announcement Rules
- [BR-025] Announcements have an author and role
- [BR-026] Image attachments optional for announcements
- [BR-027] Latest 3 announcements displayed on homepage

## Compliance Requirements

### Data Privacy
- [CR-001] Student data privacy (GDPR considerations)
- [CR-002] Secure storage of personal information
- [CR-003] Access control for sensitive data

### Educational Standards
- [CR-004] Quarterly grading system (Department of Education format)
- [CR-005] Attendance tracking per DepEd requirements
- [CR-006] Report card format compliance

## Integration Requirements

### External Services
- [IR-001] Email service provider (SMTP)
- [IR-002] AWS S3 for file storage
- [IR-003] reCAPTCHA (planned)

### Future Integrations
- [IR-004] SMS notification system
- [IR-005] Payment gateway for fees
- [IR-006] Learning Management System integration
- [IR-007] Government reporting system integration

## Constraints

### Technical Constraints
- [TC-001] Must use Laravel framework
- [TC-002] Must support PHP 8.2+
- [TC-003] Must use MySQL database
- [TC-004] Must be browser-based (no mobile app initially)

### Time Constraints
- [TC-005] Phase 1 refactoring to be completed within 2 weeks
- [TC-006] Backward compatibility must be maintained

### Budget Constraints
- [TC-007] Use free/open-source technologies
- [TC-008] Minimize third-party service costs

### Resource Constraints
- [TC-009] Development team size
- [TC-010] Hosting infrastructure

## Future Enhancement Requirements

### Phase 2 Features
- [FE-001] Parent portal
- [FE-002] Mobile application
- [FE-003] Real-time notifications
- [FE-004] Grade calculation automation
- [FE-005] Advanced reporting and analytics
- [FE-006] Payment and fee management
- [FE-007] Library management
- [FE-008] Inventory management
- [FE-009] Event calendar
- [FE-010] Discussion forums
