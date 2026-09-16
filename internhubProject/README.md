# InternHub — merged project

This is a role-based internship/job platform with three account types —
**students**, **companies**, and **admins** — sharing one database
(`internhub`, `sql/schema.sql`).

## Roles at a glance

| Role      | Register at                | Log in at                | Dashboard         |
|-----------|-----------------------------|---------------------------|--------------------|
| Student   | `student-register.php`      | `login.php?role=student`  | `student/index.php` |
| Company   | `company-register.html`     | `login.php?role=company`  | `dashboard/index.php` |
| Admin     | *(seeded — see below)*      | `login.php?role=admin`    | `admin/index.php` |

`index.html` is the front/landing page linking to all three.

### Default admin account
The schema seeds one admin so the panel is reachable on first run:
- email: `admin@internhub.com`
- password: `Admin@123`

**Change this password immediately after your first login** (there's no
admin self-registration by design — new admins should be added by an
existing admin directly in the database or via a future "add admin"
page).

## What's in each area

- **Front page** (`index.html`) — three cards routing to student/company
  registration or login, plus an admin login link.
- **Login** (`login.php`) — one page, three tabs (Student / Company /
  Admin). Each tab checks its own table (`students` / `companies` /
  `admins`) and sets its own session key
  (`student_id` / `company_id` / `admin_id`).
- **Forgot / reset password** (`forgot-password.php`,
  `reset-password.php`) — works for both students and companies via a
  `role` parameter. **Bug fix:** reset links used to show as "expired"
  the instant they were opened. The cause was a timezone mismatch —
  the expiry was computed with PHP's `date()`/`time()`, then compared
  against MySQL's `NOW()`; if PHP's timezone and the MySQL server's
  timezone don't match (common on default XAMPP/WAMP installs), the
  stored expiry could already be in the past. Fixed by computing the
  expiry entirely inside MySQL (`NOW() + INTERVAL 1 HOUR`) so issuing
  and checking a token always use the same clock.
- **Student registration** (`student-register.php` +
  `student-register-save.php`) — name, email (OTP-verified), phone,
  college, course, graduation year, password + CAPTCHA. Logs the
  student in and sends them to their dashboard on success.
- **Student dashboard** (`student/`) — browse/search internships and
  jobs, apply with one click, track application status, edit profile.
- **Admin panel** (`admin/`) — overview counts plus full manage pages
  (edit / update / delete) for Companies, Students, Postings, Sessions,
  and Open Slots. Deletes go through a single CSRF-protected
  `admin/delete.php` with a table whitelist.

## Database

`sql/schema.sql` creates: `companies`, `profile`, `settings`,
`postings`, `sessions`, `slots`, `notifications`, `password_reset_tokens`
(shared by students + companies), `students`, `admins` (seeded), and
`applications` (a student applying to a posting).

## ⚠️ Before you deploy this anywhere public

`mail_config.php` has a **live Gmail address and app password** in it,
in plain text, in a file you just shared. Anyone who sees this project
(a teammate, a grader, a public GitHub repo) can send email as that
account. Please:
1. Go to https://myaccount.google.com/apppasswords and **revoke that
   app password now**.
2. Generate a new one and put it in `mail_config.php` locally.
3. Add `mail_config.php` to `.gitignore` before pushing anywhere.
4. Change the seeded admin password (`admin@internhub.com` /
   `Admin@123`) after your first login.

## Setup (XAMPP/MAMP/WAMP)

1. Copy this whole `internhub` folder into your web root, e.g.
   `C:\xampp\htdocs\internhub`.
2. Start Apache + MySQL.
3. Open phpMyAdmin → Import → choose `sql/schema.sql` → Go. This
   creates the `internhub` database with all tables, including the
   seeded admin account.
4. Check `db.php` (root) and `dashboard/config/db.php` /
   `student/config/db.php` / `admin/config/db.php` — all four should
   already point at `internhub` with the default XAMPP `root` / blank
   password. Adjust if your MySQL user/password differs.
5. `APP_BASE_URL` is set to `/internhub` in
   `dashboard/includes/functions.php`, `student/includes/functions.php`,
   and `admin/includes/functions.php`. If you deploy under a different
   folder name or path, update that constant in all three so
   login-redirect links resolve correctly.
6. Put a real Gmail address + App Password in `mail_config.php` (see
   the warning above — the one currently in there should be revoked).
7. Visit `http://localhost/internhub/index.html` to see the landing
   page, or jump straight to `http://localhost/internhub/login.php`.

## How the pieces connect

```
index.html            -> student-register.php / company-register.html / login.php
student-register.php  -> send_otp.php -> student-register-save.php
                          (validates, checks CAPTCHA + OTP, inserts into
                           students, logs the student in)
company-register.html -> send_otp.php -> register.php
                          (validates, checks CAPTCHA + OTP, inserts into
                           companies + profile + settings, logs the
                           company in)
login.php    -> role tab picks companies / students / admins table,
                sets $_SESSION['company_id'|'student_id'|'admin_id']
dashboard/*  -> require_login()         filters by company_id
student/*    -> require_student_login() filters by student_id
admin/*      -> require_admin()         sees every row, every table
logout.php / student/logout.php / admin/logout.php -> destroy session
```

