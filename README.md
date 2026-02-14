# FMNHS School Portal (Laravel)

An integrated **School Information System (SIS)** and **Learning Portal** for FMNHS, built with Laravel.  
The system supports day-to-day school operations (enrollment, schedules, grades, attendance, announcements) and modern classroom workflows (video conferences, quizzes/polls, gamification, study tools, and analytics).

---

## 1) Purpose of This System

This project is designed to centralize school operations into one platform for:

- **Administrators**: manage students, teachers, subjects, rooms, schedules, school years, and analytics.
- **Teachers**: manage classes, grades, assignments, attendance, announcements, lesson plans, conferences, and reports.
- **Students**: view schedules/grades/attendance, submit assignments, join conferences, and track learning progress.
- **Parents**: access child-focused information and updates (parent portal routes are present in the system).

In short: this system helps schools run academic processes digitally while enabling live/interactive learning.

---

## 2) Key Features

### Core SIS Features
- Multi-role authentication (`student`, `teacher`, `admin`, and parent routes)
- Student and teacher management
- Subject, section, room, and school-year management
- Class schedules, attendance tracking, and grade management
- Announcements and audit trail support

### Conference & Real-Time Learning Features
- Live conference rooms with public/private access
- Custom WebSocket signaling server for WebRTC (`conference:signal-serve`)
- Conference chat, file sharing, event logs, and participant tracking
- Conference recording metadata, transcript/chapter workflows, and playback support
- Notifications and digest email support for conference activity

### Engagement & Advanced Learning Features
- Quiz/poll/survey APIs for conference-integrated assessments
- Gamification system (points, badges, achievements, leaderboard)
- Learning paths, portfolio tools, study session tracking
- AI-assisted and analytics-related service foundations

---

## 3) Tech Stack

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend build**: Vite, TailwindCSS
- **Database**: MySQL/MariaDB
- **Queue/Cache options**: Database/Redis (configurable)
- **Realtime signaling**: Workerman (`workerman/workerman`)
- **Storage**: local/S3-compatible disk (conference files/recordings)
- **Containerization**: Docker + Supervisor + Nginx

---

## 4) Project Structure (High-Level)

- `app/Http/Controllers` → web/API controllers per module/role
- `app/Services` → business logic layer for SIS, conference, gamification, analytics, etc.
- `app/Realtime` → custom conference signaling server
- `resources/views` → Blade UI pages/components
- `resources/js` → conference frontend modules (media, peers, signaling, chat, recording)
- `routes/web.php` → application routes
- `database/migrations` + `database/seeders` → schema and development data
- `docs/` → feature, security, and WebRTC signaling documentation

---

## 5) Local Setup (How to Run)

## 5.1 Prerequisites
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL/MariaDB

## 5.2 Install Dependencies
```bash
composer install
npm install
```

## 5.3 Configure Environment
Create your environment file and update values:

```bash
copy .env.example .env
```

Then edit `.env` (database, mail, storage, app URL, websocket settings).

Generate app key:
```bash
php artisan key:generate
```

## 5.4 Database Setup
```bash
php artisan migrate --seed
```

Optional for storage links:
```bash
php artisan storage:link
```

## 5.5 Start Development Services
Recommended (single command defined in `composer.json`):
```bash
composer dev
```

This starts:
- Laravel server
- Queue listener
- Log viewer (`pail`)
- Vite dev server

App URL (default): `http://127.0.0.1:8000`

---

## 6) Conference Signaling Server

For conference WebRTC signaling, run the custom server:

```bash
php artisan conference:signal-serve --host=127.0.0.1 --port=6001
```

Related config file: `config/conference_signaling.php`  
Related env vars:

- `CONFERENCE_SIGNALING_ENABLED`
- `CONFERENCE_SIGNALING_HOST`
- `CONFERENCE_SIGNALING_BIND_HOST`
- `CONFERENCE_SIGNALING_PORT`
- `CONFERENCE_SIGNALING_SCHEME`
- `CONFERENCE_SIGNALING_PATH`
- `CONFERENCE_SIGNALING_TOKEN_TTL`
- `WEBRTC_ICE_SERVERS`
- `WEBRTC_TURN_URL`, `WEBRTC_TURN_USERNAME`, `WEBRTC_TURN_CREDENTIAL`

Detailed signaling reference: `docs/webrtc-custom-signaling.md`

---

## 7) Main Login/Entry Routes

- Home: `/`
- Student login: `/student/login`
- Teacher login: `/teacher/login`
- Admin login: `/admin/login`
- Parent login: `/parent/login`
- Conference join: `/conference/join/{conference}`

---

## 8) API Highlights

Authenticated teacher/student API groups include endpoints for:

- Conference messaging/files/participants/events/summary/timeline
- Conference recordings and notifications
- Quiz lifecycle (`/api/quizzes/...`, `/api/conference/{conference}/quizzes`)
- Gamification (`/api/gamification/...`)
- Whiteboard, breakout rooms, mood feedback, games, captions, presentations
- Study groups, forum, learning paths, portfolio, study sessions, recommendations, AI assistant

See `routes/web.php` for full route map.

---

## 9) Scheduled Jobs

Defined in `routes/console.php`:

- Assignment reminders (08:00 and 18:00 daily)
- At-risk student detection (weekly, Monday)
- Weekly progress report generation (Friday)

To run scheduler locally:
```bash
php artisan schedule:work
```

---

## 10) Development Seed Accounts (Non-Production)

From current seeders (`AdminSeeder`, `TeacherSeeder`, `StudentSeeder`), common development accounts include:

- Admin: `admin@school.com` / `password`
- Teacher: `rasosjoanna@gmail.com` / `password`
- Student: `dev@gmail.com` / `password`

> Use only for local development/testing. Change credentials for staging/production.

---

## 11) Testing & Quality

Run tests:
```bash
composer test
```

Or:
```bash
php artisan test
```

---

## 12) Docker Deployment Notes

This repository includes `Dockerfile` and `docker-compose.yml` with:

- PHP-FPM + Nginx + Supervisor
- Frontend build stage (Node)
- Custom websocket process via Supervisor

Basic commands:

```bash
docker compose build
docker compose up -d
```

> Note: current `docker-compose.yml` references an **external network** (`dokploy-network`).
> Ensure that network exists in your environment or adjust the compose file for local use.

---

## 13) Important Security Notes

Before production:

1. Rotate and secure all real credentials/secrets.
2. Replace development/default passwords.
3. Review/remove risky maintenance routes and debug shortcuts.
4. Set strict production env values (`APP_ENV=production`, `APP_DEBUG=false`, proper HTTPS, etc.).

Security and implementation references:
- `docs/SECURITY_SUMMARY.md`
- `suggested_improvements_automation.md`

---

## 14) Additional Documentation

- `docs/PHASE_1_FEATURES.md` → phase feature details (quiz + gamification, etc.)
- `docs/SECURITY_SUMMARY.md` → security review summary
- `docs/webrtc-custom-signaling.md` → custom signaling architecture and contract

---

## 15) Quick Command Reference

```bash
# First-time setup
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Start development
composer dev

# Optional: run signaling server separately
php artisan conference:signal-serve --host=127.0.0.1 --port=6001

# Tests
composer test
```


