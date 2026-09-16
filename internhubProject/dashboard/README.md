# Company Dashboard

A PHP + MySQL company dashboard: internships, jobs, online sessions, and
open-slot categories, all stored in and served from a real database
(built for phpMyAdmin/MySQL). Every "Add", "Modify", "Edit", and "Delete"
action writes to the database — nothing on the dashboard is sample data.

## Stack
- **PHP** (plain PHP + PDO, no framework) — server-rendered pages, form
  posts do the create/update/delete work
- **MySQL** — one database, six tables (see `sql/schema.sql`)
- **Vanilla JS** (`js/script.js`) — only for the logo upload (AJAX) and
  the notification bell dropdown; everything else is regular form
  submissions, so it works even with JS off

## 1. Install a local server stack

The easiest path is **XAMPP** (Windows/Mac/Linux) or **MAMP** (Mac) —
both bundle Apache, PHP, MySQL, and phpMyAdmin together.

1. Install XAMPP: https://www.apachefriends.org
2. Start **Apache** and **MySQL** from the XAMPP control panel

## 2. Add the project files

Copy the whole `company-dashboard` folder into your server's web root:
- XAMPP (Windows): `C:\xampp\htdocs\company-dashboard`
- XAMPP (Mac/Linux): `/Applications/XAMPP/htdocs/company-dashboard` or `/opt/lampp/htdocs/company-dashboard`
- MAMP: `/Applications/MAMP/htdocs/company-dashboard`

## 3. Create the database in phpMyAdmin

1. Open `http://localhost/phpmyadmin`
2. Click **Import**
3. Choose the file `sql/schema.sql` from this project
4. Click **Go**

This creates the `company_dashboard` database with 6 tables
(`profile`, `postings`, `sessions`, `slots`, `settings`, `notifications`)
and seeds one starter row each for `profile` and `settings` so the
dashboard has something to display on first load.

## 4. Point the app at your database

Open `config/db.php` and confirm the credentials match your MySQL setup.
Fresh XAMPP/MAMP installs typically use:

```php
$DB_HOST = 'localhost';
$DB_NAME = 'company_dashboard';
$DB_USER = 'root';
$DB_PASS = '';        // MAMP often uses 'root' here instead of blank
```

## 5. Open the dashboard

Visit: `http://localhost/company-dashboard/index.php`

That's it — every number on the dashboard now comes from the database,
and every button leads somewhere real:

| Dashboard element | What it does |
|---|---|
| Internship / Job "＋ Add" | Opens a blank form → saves a new posting to `postings` |
| Internship / Job number links / "✎ Modify" | Opens a full list of postings with Edit / Delete |
| Online Session "＋ Add" / "✎ Modify" | Add sessions, toggle Active/Inactive with one click |
| Open Slots "＋ Add" / "✎ Modify" | Set total vs. filled slots; shows "Full" automatically |
| Sidebar logo | Click it to upload a logo — saves to `uploads/`, updates instantly, no reload |
| "✎ Edit profile" | Edit company name & bio; tracks an edit counter |
| ⚙️ Settings | Site name, contact email, notification toggle |
| 🔔 Bell | Live notification feed — every add/edit/delete logs one |
| Footer links | Privacy / Terms / Support — real pages, editable placeholder copy |

## Project structure

```
company-dashboard/
  config/db.php          <- database connection (edit this first)
  sql/schema.sql          <- import this in phpMyAdmin
  includes/                <- shared header/sidebar/footer/functions
  css/styles.css
  js/script.js
  uploads/                  <- uploaded logos land here
  index.php                 <- dashboard
  internships.php / jobs.php        <- list views (share postings-view.php)
  posting-form.php / posting-save.php / posting-delete.php
  sessions.php / session-form.php / session-save.php / session-delete.php / session-toggle.php
  slots.php / slot-form.php / slot-save.php / slot-delete.php
  profile.php / profile-save.php / logo-upload.php
  settings.php / settings-save.php
  privacy.php / terms.php / support.php
  api/notifications.php     <- powers the bell dropdown
```

## What I'd extend first

1. **Login/auth** — right now anyone who reaches the URL can edit
   everything. Add a simple session-based login (a `users` table +
   password hash) before this goes anywhere public.
2. **Filtered list views** — dashboard number links (Open, Accepted,
   Rejected...) all currently open the same full list. Add a
   `?status=` query param to `postings-view.php` to jump straight to
   a filtered subset.
3. **Real applicant tracking** — right now "Applications received /
   Accepted / On Hold / Rejected" are numbers the company types in
   manually per posting. A real version would have an `applicants`
   table (linked to `postings`) with actual applicant records, and
   these numbers would be computed automatically.
4. **Validation & error display** — server-side validation exists
   (required title, non-negative numbers) but errors just bounce you
   back with a flash message; inline field-level errors would be
   friendlier.
5. **CSRF protection** — the forms are plain POSTs with no token.
   Fine for a local prototype, not fine once this is internet-facing.
6. **Image handling for logos** — currently stores the raw upload;
   add resizing/cropping so oddly-sized logos don't break the sidebar
   layout.
