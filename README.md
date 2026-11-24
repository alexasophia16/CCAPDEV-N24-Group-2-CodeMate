# CodeMate (Phase 3)

A peer-tutoring web application for DLSU CCS students. Phase 2 added the PHP/MySQL backend (MVC, LAMP stack). Phase 3 adds the security features listed in the Security section below.

## Branch and structure

- Work for this phase belongs on the `phase3-security` branch. `phase2` is kept as it was submitted.
- `backend/` contains PHP configuration, core MVC classes, controllers, and models.
- `frontend/views/` contains PHP-rendered views and shared layouts.
- `frontend/styles/` and `frontend/scripts/` contain the presentation and client-side behaviour.
- `legacy-phase1/` is retained as a reference only; it is not used by the PHP router.

## Requirements

- Ubuntu 22.04 or compatible Linux server
- Apache 2.4
- PHP 8.1+ with `pdo_mysql`, `mbstring` and `curl` (curl is used by Google sign-in)
- MySQL 8+ or MariaDB 10.6+
- Internet access in the browser for Chart.js (loaded from a CDN on the dashboard)

## Local / CCS Cloud setup

1. Clone the repository and switch to the correct branch:

   ```bash
   git clone <repository-url>
   cd CCAPDEV-N24-Group-2-CodeMate
   git switch phase3-security
   ```

2. Install the LAMP packages on Ubuntu:

   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-mbstring php-curl
   sudo systemctl enable --now apache2
   sudo systemctl enable --now mysql
   ```

3. Put the project in the Apache document root (or configure an Apache virtual host whose document root points to the project folder). For a simple server setup:

   ```bash
   sudo cp -r . /var/www/html/codemate
   sudo chown -R www-data:www-data /var/www/html/codemate
   ```

4. Create the database and import the supplied schema and sample data:

   ```bash
   sudo mysql
   ```

   In the MySQL prompt, create the user the app will log in as. It only gets SELECT, INSERT, UPDATE and DELETE, so it can't drop or change tables:

   ```sql
   CREATE DATABASE codemate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'codemate_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
   GRANT SELECT, INSERT, UPDATE, DELETE ON codemate.* TO 'codemate_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

   Then import the SQL file as root (the file drops and recreates the `codemate` database, which the app user isn't allowed to do):

   ```bash
   sudo mysql < CCAPDEV-N24-Group-2-CodeMate.sql
   ```

5. Create the private database configuration file:

   ```bash
   cp backend/config/database.example.php backend/config/database.php
   nano backend/config/database.php
   ```

   Set the database name, username, and password to the values created above. `database.php` is intentionally excluded from version control; never commit real credentials.

6. Optional: set up Google sign-in. Copy the example config and enter the OAuth client ID, client secret and redirect URI from Google Cloud Console. The redirect URI must end in `index.php?page=google-callback` and match the one registered in Google exactly. Without this file, the Google buttons show "Google sign-in is not set up yet" and email/password login still works.

   ```bash
   cp backend/config/google.example.php backend/config/google.php
   nano backend/config/google.php
   ```

7. Turn on HTTPS in Apache. We use a self-signed certificate because we can't get a real one for the CCS Cloud domain, so the browser will show a "Not secure" warning the first time (click Advanced, then Proceed). The connection is still encrypted.

   ```bash
   sudo a2enmod ssl
   sudo mkdir -p /etc/apache2/ssl
   sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/codemate.key -out /etc/apache2/ssl/codemate.crt -subj "/CN=<server-hostname>"
   ```

   Add these lines right after `<VirtualHost *:80>` in `/etc/apache2/sites-enabled/000-default.conf`, then run `sudo apache2ctl configtest` and `sudo systemctl restart apache2`:

   ```text
   SSLEngine on
   SSLCertificateFile /etc/apache2/ssl/codemate.crt
   SSLCertificateKeyFile /etc/apache2/ssl/codemate.key
   ```

   Google sign-in needs this, since Google only accepts `https://` redirect URIs (except for localhost).

8. Open the application in a browser:

   ```text
   https://<server-ip>/codemate/
   ```

   The application entry point is `index.php`. Pages are routed using `index.php?page=<route>`.

## Demo accounts

The SQL seed data includes these demo accounts. The demo password is `password123`. Re-importing `CCAPDEV-N24-Group-2-CodeMate.sql` resets the database to this sample data.

| Role | Email |
|---|---|
| Tutee | `alexa_sophia_canaria@dlsu.edu.ph` |
| Tutor | `juan_rivera@dlsu.edu.ph` |
| Admin | `admin@codemate.local` |

The passwords in the SQL file are stored as bcrypt hashes, not as `password123` itself.

## Features

**Accounts**
- Login, sign-up as a tutee or tutor, and logout, with server-side validation
- Sign in and sign up with Google (OAuth 2.0)
- Session-based authentication with role-based access (tutee, tutor, admin)
- Role-aware dashboard, navigation, and notifications

**Tutees**
- Find a Tutor: search, course filter, and sorting
- Tutor profiles with open schedules and reviews
- Booking a session, My Bookings with a status filter, cancelling, and leaving a review

**Tutors**
- Managing their profile, tutoring slots, and booking requests (marking sessions completed)
- Viewing their reviews

**Community**
- Messages between tutees and tutors
- Course discussions with replies
- Shared study resources
- Reporting a tutor, discussion, reply, resource, or user

**Admins**
- Approving or declining booking requests and verifying tutor applications
- User management (activate / deactivate accounts)
- Course management (add, edit, archive, restore)
- Content moderation and report handling (removing reported content or deactivating accounts)

## Security

What we added in Phase 3 and where to find it:

- **Password hashing.** Passwords are saved with `password_hash()` (bcrypt) and checked with `password_verify()`. The sample accounts in the SQL file are hashed too. See `User::create()` and `AuthController::login()`.
- **Password rules.** Sign-up needs at least 8 characters with at least one letter and one number.
- **Login attempt limit.** After 5 wrong passwords, login is blocked for 60 seconds. The count is kept in the session and resets after a successful login.
- **CSRF tokens.** Every POST form has a hidden token (`csrf_field()` in `helpers.php`). `Router::dispatch()` checks it before running any POST action, so a missing or wrong token gets HTTP 419. The discussion quick reply sends the token from a `<meta>` tag.
- **Sessions.** The session ID is regenerated on login, sign-up and Google sign-in, so an old session ID can't be reused. The session cookie is `HttpOnly`, `SameSite=Lax`, ends when the browser closes, and is `Secure` when the site is opened over HTTPS. Users are logged out after 30 minutes of inactivity.
- **SQL injection.** All queries use PDO prepared statements with `?` placeholders.
- **XSS.** Views print user and database values through `e()` (`htmlspecialchars`).
- **Access control.** Pages check the logged-in user's role, so tutees and tutors can't open admin pages. Tutees can only cancel or review their own bookings, and tutors can only delete their own slots and mark their own sessions completed, even if someone changes the id in the form.
- **Security headers.** Every page sends `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` and `Referrer-Policy: same-origin` (set in `bootstrap.php`).
- **Error messages.** PHP errors go to the server log instead of the page, so file paths and SQL aren't shown to users.
- **Database user.** The app logs in as a MySQL user with only SELECT, INSERT, UPDATE and DELETE on the `codemate` database.
- **Secrets.** `database.php` and `google.php` are in `.gitignore`, so the database password and Google client secret aren't on GitHub.
- **HTTPS.** Apache serves the site over HTTPS with a self-signed certificate (setup step 7).

## Important notes

- Do not use the Phase 1 static HTML pages as the running application; they are retained under `legacy-phase1/` for reference.
- All application links should use the `url()` helper and route through `index.php`.
- Do not commit `backend/config/database.php` or `backend/config/google.php`; they hold real credentials. Use the `.example.php` files as templates.
