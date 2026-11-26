# Unit tests

| File | What it is |
|---|---|
| `UserTest.php` | Automated tests for every public function of `backend/models/User.php` (46 checks) |
| `results.txt` | Output of our last run |

## How to run

1. Import `CCAPDEV-N24-Group-2-CodeMate.sql` and set up `backend/config/database.php` (see the main README).
2. From the project root:

   ```bash
   php tests/UserTest.php
   ```

3. To save the output:

   ```bash
   php tests/UserTest.php > tests/results.txt
   ```

The script prints PASS or FAIL for each check and a total at the end. It exits with code 1 if anything fails. Accounts it creates use emails starting with `unittest_` and are deleted at the end, and the account status it changes is set back, so the sample data is left as it was.

## What is tested

| Function | Checks |
|---|---|
| `findById()` | right user for an id, null for an unknown id, password stored as a bcrypt hash |
| `findByEmail()` | finds by email, null for an unknown email, SQL injection input matches nobody |
| `emailExists()` | true for a registered email, false otherwise |
| `create()` | tutee and tutor accounts, password hashed with a random salt, tutor profile set to pending, admins notified, admin role and duplicate email refused |
| `updatePassword()` | new password works, old one doesn't, stored as a hash |
| `all()` | every user, no password column, newest first |
| `setAccountStatus()` | deactivate and reactivate, invalid status refused, admin accounts never changed, unknown id |
| `dashboardStats()` | the four cards for each role and that their numbers match the database |
| `dashboardBookings()` | tutees and tutors only see their own bookings, admin sees all |
| `featuredTutors()` | default limit, limit kept between 1 and 20, active tutors only, rejected applications left out, verified tutors first |
