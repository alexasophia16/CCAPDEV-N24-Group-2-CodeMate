# CodeMate — Phase 2

A peer-tutoring web application for DLSU CCS students. Phase 2 adds a PHP/MySQL backend following MVC and runs on the LAMP stack.

## Branch and structure

- Work for this phase belongs on the `phase2` branch.
- `backend/` contains PHP configuration, core MVC classes, controllers, and models.
- `frontend/views/` contains PHP-rendered views and shared layouts.
- `frontend/styles/` and `frontend/scripts/` contain the presentation and client-side behaviour.
- `legacy-phase1/` is retained as a reference only; it is not used by the PHP router.

## Requirements

- Ubuntu 22.04 or compatible Linux server
- Apache 2.4
- PHP 8.1+ with `pdo_mysql` and `mbstring`
- MySQL 8+ or MariaDB 10.6+

## Local / CCS Cloud setup

1. Clone the repository and switch to the correct branch:

   ```bash
   git clone <repository-url>
   cd CCAPDEV-N24-Group-2-CodeMate
   git switch phase2
   ```

2. Install the LAMP packages on Ubuntu:

   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-mbstring
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

   In the MySQL prompt:

   ```sql
   CREATE DATABASE codemate CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'codemate_user'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
   GRANT ALL PRIVILEGES ON codemate.* TO 'codemate_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

   Then import the SQL file:

   ```bash
   mysql -u codemate_user -p codemate < CCAPDEV-N24-Group-2-CodeMate.sql
   ```

5. Create the private database configuration file:

   ```bash
   cp backend/config/database.example.php backend/config/database.php
   nano backend/config/database.php
   ```

   Set the database name, username, and password to the values created above. `database.php` is intentionally excluded from version control; never commit real credentials.

6. Open the application in a browser:

   ```text
   http://<server-ip>/codemate/
   ```

   The application entry point is `index.php`. Pages are routed using `index.php?page=<route>`.

## Demo accounts

The SQL seed data includes these demo accounts. The demo password is `password123`.

| Role | Email |
|---|---|
| Tutee | `alexa_sophia_canaria@dlsu.edu.ph` |
| Tutor | `juan_rivera@dlsu.edu.ph` |
| Admin | `admin@codemate.local` |

The seeded demo accounts use the original sample password format for compatibility. New sign-ups are stored using PHP `password_hash()`. On a successful login, a legacy demo password is upgraded to a password hash.

## Core & Accounts functionality

- Login and sign-up with server-side validation
- Session-based authentication and logout
- Role-aware dashboard and navigation
- Notifications list, mark-as-read, and mark-all-as-read
- Admin user listing and account activation/deactivation
- Admin tutor-application approval/rejection
- CSRF tokens on POST forms

## Important notes

- Do not use the Phase 1 static HTML pages as the running application; they are retained under `legacy-phase1/` for reference.
- All application links should use the `url()` helper and route through `index.php`.
- Do not commit `backend/config/database.php`.
- For a production deployment, configure HTTPS, secure session cookie settings, backups, and least-privilege database permissions.


### Google Sign-In Setup

Google sign-in/sign-up uses Google's OAuth 2.0 authorization-code flow. To enable it:

1. Create an OAuth 2.0 Client ID of type **Web application** in Google Cloud Console and configure the OAuth consent screen.
2. Add your deployed CodeMate callback URL to **Authorized redirect URIs**. It must exactly match the `redirect_uri` configured in the app, for example `https://YOUR_HOST/YOUR_PROJECT_PATH/index.php?page=google-callback`.
3. Copy `backend/config/google.example.php` to `backend/config/google.php`.
4. Enter the Client ID, Client Secret, and exact redirect URI in `google.php`. This file is ignored by Git and must not be committed.
5. Ensure the VM can make outbound HTTPS requests and PHP has URL stream access enabled.

Google sign-up creates a default Tutee account. Google login requires an existing CodeMate account with the same verified Google email. Existing account roles are preserved.
