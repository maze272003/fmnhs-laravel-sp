# FMNHS Laravel School Portal

A comprehensive school management system for administrators, teachers, and students built with Laravel 12.0 and PHP 8.2+.

## Table of Contents

- [Features](#features)
- [Architecture](#architecture)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Documentation](#documentation)
- [Development](#development)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [Support](#support)

---

## Features

### Student Portal 🎓
- **Profile Management**
  - View and update personal information
  - Upload and change avatar
  - Manage email and password
- **Grade Management**
  - View grades by subject and quarter (Q1, Q2, Q3, Q4)
  - Generate PDF report cards
  - Track overall academic performance
- **Schedule Viewing**
  - View class schedule by day and time
  - See subject, teacher, and room assignments
- **Assignments**
  - View assigned tasks with deadlines
  - Submit assignments with file uploads
  - Track submission status and grades
- **Attendance Records**
  - View personal attendance history
  - Monitor attendance trends by subject
- **Student Dashboard**
  - Quick access to all features
  - View recent announcements
  - Personal statistics overview

### Teacher Portal 👨‍🏫
- **Dashboard Overview**
  - View total classes and students count
  - Access advisory class information
  - View recent announcements
- **Class Management**
  - View all assigned classes
  - Access student lists by section
  - Manage advisory sections
- **Grading System**
  - Record quarterly grades (Q1, Q2, Q3, Q4)
  - View grading sheets by subject and section
  - Update or modify existing grades
  - Track student performance trends
- **Attendance Management**
  - Mark attendance by date and class
  - Track student presence: Present, Absent, Late, Excused
  - Edit attendance records
  - Generate attendance summaries
- **Assignments**
  - Create assignments with descriptions and deadlines
  - Attach resource files
  - Target specific sections/subjects
  - View student submissions
  - Grade submitted work with feedback
- **Announcements**
  - Create class announcements
  - Include images and resources
  - Target students for notifications

### Admin Portal 👩‍💼
- **Student Management**
  - Create, update, and delete student accounts
  - Search students by name, LRN, or email
  - Assign students to sections
  - Generate account credentials (password = LRN)
  - Send welcome emails
- **Teacher Management**
  - Create and update teacher accounts
  - Manage teacher profiles
  - Archive/restore teacher accounts
  - Assign teachers as section advisors
- **Subject Management**
  - Create course subjects with codes
  - Update subject information
  - Archive/restore subjects
- **Schedule Management**
  - Create class schedules
  - Assign teachers to subjects
  - Set time slots and days
  - Assign rooms
  - Manage multiple schedules per class
- **Monitoring Dashboard**
  - View all attendance logs
  - Filter by date, section, or subject
  - School-wide announcements
  - System statistics

### Communication 🔔
- **Announcement System**
  - School-wide announcements with image support
  - Role-specific announcements (student, teacher, admin)
  - Latest announcements on all dashboards
- **Email Notifications**
  - Student account creation emails with credentials
  - New assignment notifications
  - Grade update notifications
  - Announcement broadcast emails

---

## Architecture

The application follows **Clean Architecture principles** with Repository-Service-Controller pattern:

### Layered Architecture

```
┌─────────────────────────────────────────────────────────┐
│                  Controllers (HTTP Layer)            │
│  - Handle HTTP requests                              │
│  - Return responses                                   │
│  - Use form request validation                       │
│  - Delegate to services                             │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                  Services (Business Logic)          │
│  - Business rules and validation                   │
│  - Cross-repository operations                    │
│  - Orchestration and coordination               │
│  - Error handling and logging                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Repositories (Data Access)               │
│  - Abstract data access                            │
│  - Query builders                                  │
│  - Relationship loading                             │
│  - Custom query methods                            │
│  - Error handling and logging                       │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                   Models (Eloquent)                  │
│  - Data representation                             │
│  - Relationships (belongsTo, hasMany)             │
│  - Scopes and accessors                          │
└─────────────────────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│                 Database (MySQL/MariaDB)               │
└─────────────────────────────────────────────────────────┘
```

### Component Responsibilities

| Layer | Responsibility | Example |
|--------|---------------|----------|
| **Controller** | HTTP request/response handling | `StudentController@index()` |
| **Service** | Business logic and rules | `GradeService::recordGrade()` validates grade range |
| **Repository** | Data access and queries | `StudentRepository::getBySection()` fetches students |
| **Model** | Data structure and relationships | `Student` model defines database table |

### SOLID Principles Applied

- **S**ingle Responsibility - Each layer has one clear purpose
- **O**pen/Closed - Extensible through interfaces
- **L**iskov Substitution - Interfaces honored by implementations
- **I**nterface Segregation - Focused interfaces for each layer
- **D**ependency Inversion - Depend on abstractions, not concretions

---

## Technology Stack

### Backend
- **Framework:** Laravel 12.0
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+ / MariaDB 10.3+
- **PHP Version:** ^8.2

### Frontend
- **Build Tool:** Vite 7.0.7
- **CSS Framework:** TailwindCSS 4.0.0
- **Templates:** Blade (Laravel's native templating)
- **JavaScript:** Axios ^1.11.0

### Third-Party Packages
- **PDF Generation:** barryvdh/laravel-dompdf ^3.1
- **File Storage:** league/flysystem-aws-s3-v3 ^3.0 (S3 integration)
- **Development:** Laravel Pint ^1.24, PHPUnit ^11.5.3

### Development Tools
- **Local Environment:** Laravel Sail (Docker)
- **REPL:** Laravel Tinker ^2.10.1
- **Logging:** Laravel Pail ^1.2.2
- **Code Style:** Laravel Pint ^1.24
- **Testing:** PHPUnit ^11.5.3, Mockery ^1.6.1

---

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0+ or MariaDB 10.3+
- Node.js 18.x or higher
- NPM 9.x or higher

### Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd fmnhs-laravel-sp
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` file with your configuration:
   ```env
   APP_NAME="FMNHS School Portal"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fmnhs_portal
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=your_access_key
   AWS_SECRET_ACCESS_KEY=your_secret_key
   AWS_DEFAULT_REGION=ap-southeast-1
   AWS_BUCKET=your_bucket_name
   
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=587
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@fmnhs.edu.ph
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   php artisan db:seed  # Optional
   ```

6. **Link storage (for file uploads)**
   ```bash
   php artisan storage:link
   ```

7. **Start development server**
   ```bash
   npm run dev
   # Or with Sail
   ./vendor/bin/sail up
   ```

8. **Access the application**
   - **Admin Portal:** `http://localhost:8000/admin/login`
   - **Teacher Portal:** `http://localhost:8000/teacher/login`
   - **Student Portal:** `http://localhost:8000/student/login`

### Default Credentials (After Seeding)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@fmnhs.edu.ph | password |
| Teacher | teacher@fmnhs.edu.ph | password |
| Student | (LRN-based) | studentlrn |

---

## Documentation

### Architecture & Planning
- **[Architecture Proposal](plan/phase1/proposal.md)** - Detailed technical architecture and design patterns
- **[Requirements](plan/phase1/requirements.md)** - Complete functional and non-functional requirements
- **[Technology Stack](plan/phase1/techstack.md)** - Technology documentation and dependencies
- **[Implementation Plan](plan/phase1/implementation-plan.md)** - 13-phase implementation roadmap
- **[Phase 1 Progress](plan/phase1/progress.md)** - Session-by-session progress tracking

### Implementation Details
- **[Task Checklist](plan/phase1/checklist.md)** - 300+ detailed tasks with status tracking
- **[Phase 1 Completion](plan/phase1/completion-report.md)** - Repository and service layer implementation
- **[Service Interfaces Plan](plan/phase1/service-interfaces-plan.md)** - Service interface specifications
- **[Phase 2 Tasks](plan/phase1/phase2-tasks.md)** - Detailed Phase 2 task breakdown

### Codebase Documentation
- **[Codebase Analysis](plan/phase1/codebase.md)** - Current architecture and patterns
- **[Implementation Summary](plan/phase1/implementation-summary.md)** - Implementation status and statistics

### Tracking & Change Log
- **[Summary](plan/phase1/summary.md)** - Phase 1 completion summary
- **[CHANGELOG](plan/phase1/CHANGELOG.md)** - Version history and changes

### Reference
- **[Phase 1 Instructions](plan/phase1/instructions.md)** - Phase 1 guidelines and objectives

---

## Development

### Local Development Server
```bash
# Start development server with hot reload
npm run dev

# Or use Laravel Sail for Docker environment
./vendor/bin/sail up

# Stop server
Ctrl + C
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Fresh migration (WARNING: destroys data)
php artisan migrate:fresh

# Seed database
php artisan db:seed

# Open tinker for interactive testing
php artisan tinker
```

### Code Quality
```bash
# Fix code style
composer pint

# Check code style
composer pint --test

# Run static analysis
vendor/bin/phpstan analyse

# Run tests
php artisan test

# Run tests with coverage
php artisan test --coverage
```

### Caching
```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear event cache
php artisan event:clear
```

### Queue Management
```bash
# Run queue worker
php artisan queue:work

# Listen to queue in background
php artisan queue:listen

# Restart failed queue workers
php artisan queue:restart
```

### Logging
```bash
# View real-time logs (Laravel Pail)
php artisan pail

# View application logs
tail -f storage/logs/laravel.log
```

---

## Deployment

### Production Preparation

1. **Environment Setup**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   APP_KEY=<your-production-key>
   
   # Database configuration
   DB_CONNECTION=mysql
   DB_HOST=production-db-host
   DB_PORT=3306
   DB_DATABASE=fmnhs_portal_production
   DB_USERNAME=production-user
   DB_PASSWORD=<strong-password>
   
   # File storage (S3)
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=<production-access-key>
   AWS_SECRET_ACCESS_KEY=<production-secret-key>
   AWS_DEFAULT_REGION=ap-southeast-1
   AWS_BUCKET=fmnhs-production
   
   # Mail configuration
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailgun.org
   MAIL_PORT=587
   MAIL_USERNAME=<production-smtp-user>
   MAIL_PASSWORD=<production-smtp-password>
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@fmnhs.edu.ph
   ```

2. **Optimize for Production**
   ```bash
   # Cache configuration and routes
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Optimize autoloader
   composer install --optimize-autoloader --no-dev
   
   # Optimize for production
   php artisan optimize
   ```

### Deployment Steps

1. **Deploy code to server**
   ```bash
   # Using Git
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force
   php artisan cache:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

2. **Set file permissions**
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

3. **Run database migrations**
   ```bash
   php artisan migrate --force --seed
   ```

4. **Verify deployment**
   - Access admin portal
   - Test teacher login
   - Test student login
   - Verify file uploads work
   - Test email notifications

### Backup Strategy

```bash
# Backup database
mysqldump -u [username] -p [password] [database] > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz storage/

# Using Laravel backups
php artisan backup:run --only-db
php artisan backup:run --only-files
```

---

## Contributing

### Code Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use [Laravel Pint](https://github.com/laravel/pint) for code formatting
- Add [PHPDoc](https://www.phpdoc.org/) comments to all classes and methods
- Write tests for new features and bug fixes
- Keep methods focused and concise

### Commit Message Format

```
<type>(<scope>): <subject>

<type> types: feat, fix, docs, style, refactor, test, chore
<scope> affected component or file
<subject> brief description of changes

Body:
- More detailed explanation of what was changed and why
- Reference any relevant issue numbers

Examples:
feat(auth): add teacher login with multi-factor authentication
fix(attendance): resolve bug where attendance not saving for late students
docs(readme): update installation instructions with new requirements
refactor(student): move grade calculation to service layer
test(grade): add unit tests for grade average calculation
```

### Development Workflow

1. **Create feature branch** from `develop` branch
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/your-feature-name
   ```

2. **Make changes** following code standards
   - Write clean, testable code
   - Add tests for new functionality
   - Update documentation as needed
   - Run `composer pint` to fix code style

3. **Run tests**
   ```bash
   php artisan test
   ```

4. **Commit changes**
   ```bash
   git add .
   git commit -m "feat(auth): add teacher login with multi-factor authentication"
   ```

5. **Push and create pull request**
   ```bash
   git push origin feature/your-feature-name
   # Create PR through GitHub/GitLab interface
   ```

---

## Support

### Getting Help

1. **Review Documentation**
   - Check the [Architecture Overview](plan/phase1/proposal.md) for design patterns
   - Review [Task Checklist](plan/phase1/checklist.md) for implementation status
   - Check [Progress Log](plan/phase1/progress.md) for recent changes

2. **Common Issues**

   **Composer/Dependency Issues**
   ```bash
   composer dump-autoload
   composer clear-cache
   composer install
   ```

   **Cache Issues**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

   **Permission Issues**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

   **Migration Issues**
   ```bash
   php artisan migrate:rollback
   php artisan migrate:fresh  # Destroys all data
   ```

### Development Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Laracasts](https://laracasts.com) - Video tutorials
- [Laravel News](https://laravel.com/news)
- [Laracasts Forum](https://laravel.io/forum)

---

## License

The FMNHS Laravel School Portal is open-sourced software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

**Version:** 1.0.0  
**Last Updated:** January 22, 2026  
**Status:** Active Development  
**Architecture:** Repository-Service-Controller Pattern (Clean Architecture)
