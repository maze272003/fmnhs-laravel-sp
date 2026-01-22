# FMNHS Laravel School Portal - Tech Stack

## Core Technologies

### Backend Framework
- **Laravel**: 12.0 (Latest stable)
  - PHP Framework for Web Artisans
  - MVC architecture
  - Eloquent ORM
  - Blade templating engine
  - Robust routing system
  - Built-in authentication & authorization
  - Queue system for background jobs
  - Event broadcasting support

### Programming Language
- **PHP**: ^8.2 (Minimum version 8.2)
  - Strict typing support
  - Improved error handling
  - Enhanced performance
  - Modern features (named arguments, enums, readonly properties)

### Database
- **MySQL/MariaDB** (Default)
  - Relational database management system
  - Supported via Laravel's database abstraction layer
  - Migration support for version control

### Frontend
- **Vite**: 7.0.7
  - Next-generation frontend build tool
  - Fast HMR (Hot Module Replacement)
  - Optimized production builds

- **TailwindCSS**: 4.0.0
  - Utility-first CSS framework
  - Highly customizable
  - Responsive design utilities
  - Dark mode support

- **Blade Templates**: (Native to Laravel)
  - Server-side templating
  - Template inheritance
  - Components support
  - Directives for control structures

### JavaScript Libraries
- **Axios**: ^1.11.0
  - HTTP client for API requests
  - Promise-based
  - Request/response interceptors

- **Concurrently**: ^9.0.1
  - Run multiple commands concurrently
  - Used in development scripts

## Third-Party Packages

### PDF Generation
- **barryvdh/laravel-dompdf**: ^3.1
  - Generate PDF from HTML/Blade views
  - Used for student report cards
  - Wrapper around DomPDF library

### File Storage
- **league/flysystem-aws-s3-v3**: ^3.0
  - Amazon S3 filesystem integration
  - Used for file uploads (avatars, assignment files)
  - Abstract file storage layer

## Development Tools

### Code Quality
- **Laravel Pint**: ^1.24
  - Official PHP code style fixer for Laravel
  - Automatically formats code
  - Enforces PSR-12 coding standards
  - Minimal configuration required

### Testing
- **PHPUnit**: ^11.5.3
  - PHP unit testing framework
  - Integrated with Laravel
  - Feature and unit test support

- **Mockery**: ^1.6
  - Mock object framework
  - Used for testing with mocks
  - Flexible mock creation

### Debugging & Development
- **Laravel Tinker**: ^2.10.1
  - REPL (Read-Eval-Print Loop) for Laravel
  - Interact with application in command line
  - Test code snippets quickly

- **Laravel Sail**: ^1.41
  - Docker-based local development environment
  - Pre-configured Docker containers
  - Cross-platform development setup

- **Laravel Pail**: ^1.2.2
  - Real-time log viewer
  - Tail Laravel logs in terminal
  - Color-coded output

- **Faker**: ^1.23
  - Generate fake data for testing
  - Used in database seeders
  - Various data types supported

- **Collision**: ^8.6
  - Error handler for command-line applications
  - Beautiful error reporting
  - Integration with PHPUnit

## Build & Asset Management

### Vite Configuration
- **laravel-vite-plugin**: ^2.0.0
  - Vite integration for Laravel
  - Blade template support
  - Hot module replacement

### Asset Processing
- CSS: TailwindCSS via Vite
- JavaScript: ES6+ modules via Vite
- Build: Vite optimization for production

## Security Features

### Laravel Built-in Security
- CSRF Protection (Cross-Site Request Forgery)
- XSS Protection (Cross-Site Scripting)
- SQL Injection Prevention (via Eloquent ORM)
- Password Hashing (bcrypt/argon2)
- Authentication & Authorization system
- Middleware for route protection

### Third-Party Security
- Referenced reCAPTCHA (implementation in views)

## Database Tools

### Migrations
- Laravel Migration System
- Schema Builder
- Version control for database
- Rollback support

### Seeders
- Database Seeders
- Faker integration
- Model Factories
- Sample data generation

## Mail System

### Mail Configuration
- Laravel Mail Service
- SMTP Support
- Email Templates (Blade)
- Queue support for async sending
- Mail Notifications

### Mail Classes
- `StudentAccountCreated` - Welcome email for new students
- `NewAssignmentNotification` - Assignment alerts
- `AnnouncementMail` - School announcements

## Authentication

### Multi-Guard System
- `web` - Default authentication
- `student` - Student-specific guard
- `teacher` - Teacher-specific guard
- `admin` - Administrator guard

### Session Management
- Session-based authentication
- Database-backed sessions (configurable)
- Session drivers supported (file, database, redis, etc.)

## API Support

### Potential API Usage
- Axios client included
- RESTful architecture ready
- JSON response support (Laravel native)

## Deployment Considerations

### Environment
- Docker support via Laravel Sail
- Environment-based configuration (.env)
- Production optimizations available

### Performance
- Eager loading (Eloquent)
- Pagination support
- Query optimization opportunities
- Caching layer available

## Future Tech Stack Considerations

### Potential Additions
- Redis for caching and queues
- Horizon for queue monitoring
- Telescope for debugging
- Sanctum/Passport for API authentication
- Livewire or Inertia.js for SPA-like experience
- Scout for full-text search
- Horizon for queue management
- Telescope for application monitoring

### Frontend Framework Options
- Vue.js/React integration via Inertia.js
- Alpine.js for enhanced interactivity
- Livewire for dynamic components

## Compatibility

### Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- ES6+ JavaScript support required

### Server Requirements
- PHP 8.2 or higher
- Composer package manager
- Node.js & NPM for asset building
- Database server (MySQL/MariaDB/PostgreSQL/SQLite)

### Platform Support
- Windows (with WSL recommended)
- Linux (native)
- macOS (native)

## Version Control
- Git (source control)
- Git attributes configured
- Git ignore patterns defined

## Documentation
- Laravel Official Documentation
- Package Documentation
- Code comments (minimal)
- README (Laravel default template)

## Development Workflow

### Available Scripts
```bash
composer setup          # Complete project setup
composer dev           # Development server + queues + logs + vite
composer test          # Run test suite
npm run dev            # Vite development server
npm run build          # Production build
```

### Artisan Commands
- Database migrations: `php artisan migrate`
- Database seeding: `php artisan db:seed`
- Cache clearing: `php artisan cache:clear`
- Queue workers: `php artisan queue:work`
- Tinker: `php artisan tinker`

## Dependencies Summary

### Production Dependencies (composer)
- laravel/framework ^12.0
- laravel/tinker ^2.10.1
- barryvdh/laravel-dompdf ^3.1
- league/flysystem-aws-s3-v3 ^3.0

### Development Dependencies (composer)
- fakerphp/faker ^1.23
- laravel/pail ^1.2.2
- laravel/pint ^1.24
- laravel/sail ^1.41
- mockery/mockery ^1.6
- nunomaduro/collision ^8.6
- phpunit/phpunit ^11.5.3

### Node Dependencies (npm)
- @tailwindcss/vite ^4.0.0
- axios ^1.11.0
- concurrently ^9.0.1
- laravel-vite-plugin ^2.0.0
- tailwindcss ^4.0.0
- vite ^7.0.7
