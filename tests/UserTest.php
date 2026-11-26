<?php
// Unit tests for the User model (backend/models/User.php), one group per public function.
//
// Run from the project root, after importing CCAPDEV-N24-Group-2-CodeMate.sql:
//   php tests/UserTest.php
// To save the output:
//   php tests/UserTest.php > tests/results.txt
//
// The tests use the database in backend/config/database.php and the sample data.
// Anything a test adds or changes is undone at the end, so the sample data stays the same.

define('APP_ROOT', dirname(__DIR__));
date_default_timezone_set('Asia/Manila');

// load core and model classes the same way bootstrap.php does (bootstrap itself starts a session and sends headers)
spl_autoload_register(function ($class) {
    foreach (['core', 'models'] as $folder) {
        $file = APP_ROOT . "/backend/$folder/$class.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$passed = 0;
$failed = 0;

function group(string $name): void
{
    echo "\n$name\n";
}

function check(string $description, bool $result): void
{
    global $passed, $failed;
    if ($result) {
        $passed++;
        echo "  PASS  $description\n";
    } else {
        $failed++;
        echo "  FAIL  $description\n";
    }
}

// true if $fn throws an exception of class $class
function throws(callable $fn, string $class): bool
{
    try {
        $fn();
    } catch (Throwable $e) {
        return $e instanceof $class;
    }
    return false;
}

$db = Database::connection();
$users = new User();

// direct queries, used to check the model's answers against the database
$count = fn(string $sql, array $params = []) => (int) (function () use ($db, $sql, $params) {
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
})();

$testPrefix = 'unittest_';
$cleanup = function () use ($db, $testPrefix) {
    $db->prepare('DELETE FROM notifications WHERE message LIKE ?')->execute(['Unit Test%']);
    $db->prepare('DELETE FROM users WHERE email LIKE ?')->execute([$testPrefix . '%']); // tutor_profiles rows are removed by ON DELETE CASCADE
};
$cleanup(); // in case an earlier run stopped halfway

// sample accounts used below
$admin = $users->findByEmail('admin@codemate.local');
$tutee = $users->findByEmail('russel_chavez@dlsu.edu.ph');
$tutor = $users->findByEmail('juan_rivera@dlsu.edu.ph');
if (!$admin || !$tutee || !$tutor) {
    exit("Sample data not found. Import CCAPDEV-N24-Group-2-CodeMate.sql first.\n");
}

echo "User model unit tests (" . date('Y-m-d H:i') . ")\n";

try {
    // ---- findById() ----
    group('findById()');
    $row = $users->findById((int) $admin['user_id']);
    check('returns the right user for an existing id', $row !== null && $row['email'] === 'admin@codemate.local');
    check('returns null for an id that does not exist', $users->findById(999999) === null);
    check('stored password is a bcrypt hash, not plain text', str_starts_with((string) $row['password'], '$2y$'));

    // ---- findByEmail() ----
    group('findByEmail()');
    check('finds an account by email', $users->findByEmail('juan_rivera@dlsu.edu.ph')['role'] === 'tutor');
    check('returns null for an unknown email', $users->findByEmail('nobody@dlsu.edu.ph') === null);
    check("SQL injection input (' OR '1'='1) matches nobody", $users->findByEmail("' OR '1'='1") === null);

    // ---- emailExists() ----
    group('emailExists()');
    check('true for an email that is registered', $users->emailExists('russel_chavez@dlsu.edu.ph') === true);
    check('false for an email that is not registered', $users->emailExists('nobody@dlsu.edu.ph') === false);

    // ---- create() ----
    group('create()');
    $tuteeId = $users->create('Unit Test Tutee', $testPrefix . 'tutee@dlsu.edu.ph', 'secret123', 'tutee');
    $newTutee = $users->findById($tuteeId);
    check('creates a tutee and returns its new id', $tuteeId > 0 && $newTutee['role'] === 'tutee');
    check('new account starts as active', $newTutee['account_status'] === 'active');
    check('password is saved as a hash, not the typed text', $newTutee['password'] !== 'secret123' && str_starts_with($newTutee['password'], '$2y$'));
    check('the saved hash matches the typed password', password_verify('secret123', $newTutee['password']));
    check('a tutee gets no tutor profile', $count('SELECT COUNT(*) FROM tutor_profiles WHERE tutor_id = ?', [$tuteeId]) === 0);

    $tutorId = $users->create('Unit Test Tutor', $testPrefix . 'tutor@dlsu.edu.ph', 'secret123', 'tutor');
    check('a tutor gets a tutor profile marked pending',
        $count('SELECT COUNT(*) FROM tutor_profiles WHERE tutor_id = ? AND verification_status = "pending"', [$tutorId]) === 1);
    check('admins are notified about the new tutor',
        $count('SELECT COUNT(*) FROM notifications WHERE message = ?', ['Unit Test Tutor applied to be a tutor.']) > 0);
    check('same password hashed twice gives different hashes (random salt)',
        $users->findById($tutorId)['password'] !== $newTutee['password']);
    check('refuses to create an admin account',
        throws(fn() => $users->create('Unit Test Admin', $testPrefix . 'admin@dlsu.edu.ph', 'secret123', 'admin'), InvalidArgumentException::class));
    check('refuses a duplicate email',
        throws(fn() => $users->create('Unit Test Copy', $testPrefix . 'tutee@dlsu.edu.ph', 'secret123'), PDOException::class));
    check('the refused admin account was not saved',
        $count('SELECT COUNT(*) FROM users WHERE email = ?', [$testPrefix . 'admin@dlsu.edu.ph']) === 0);

    // ---- updatePassword() ----
    group('updatePassword()');
    $users->updatePassword($tuteeId, 'newpass456');
    $updated = $users->findById($tuteeId)['password'];
    check('the new password works', password_verify('newpass456', $updated));
    check('the old password no longer works', !password_verify('secret123', $updated));
    check('the new password is stored as a hash', str_starts_with($updated, '$2y$'));

    // ---- all() ----
    group('all()');
    $list = $users->all();
    check('returns every user', count($list) === $count('SELECT COUNT(*) FROM users'));
    check('does not include the password column', !array_key_exists('password', $list[0]));
    $sorted = true;
    for ($i = 1; $i < count($list); $i++) {
        if ($list[$i - 1]['created_at'] < $list[$i]['created_at']) {
            $sorted = false;
        }
    }
    check('newest accounts come first', $sorted);

    // ---- setAccountStatus() ----
    group('setAccountStatus()');
    check('deactivates a tutee', $users->setAccountStatus($tuteeId, 'deactivated') === true
        && $users->findById($tuteeId)['account_status'] === 'deactivated');
    check('reactivates the tutee', $users->setAccountStatus($tuteeId, 'active') === true
        && $users->findById($tuteeId)['account_status'] === 'active');
    check('rejects a status that is not active/deactivated', $users->setAccountStatus($tuteeId, 'banned') === false);
    check('never changes an admin account', $users->setAccountStatus((int) $admin['user_id'], 'deactivated') === false
        && $users->findById((int) $admin['user_id'])['account_status'] === 'active');
    check('returns false for an id that does not exist', $users->setAccountStatus(999999, 'deactivated') === false);

    // ---- dashboardStats() ----
    group('dashboardStats()');
    $labels = fn(array $cards) => array_column($cards, 0);
    $adminCards = $users->dashboardStats('admin', (int) $admin['user_id']);
    check('admin gets its four cards',
        $labels($adminCards) === ['Registered Users', 'Verified Tutors', 'Active Courses', 'Pending Verifications']);
    check('admin "Registered Users" matches the users table', $adminCards[0][1] === $count('SELECT COUNT(*) FROM users'));
    $tutorCards = $users->dashboardStats('tutor', (int) $tutor['user_id']);
    check('tutor gets its four cards',
        $labels($tutorCards) === ['Booking Requests', 'Upcoming Sessions', 'Completed Sessions', 'Open Discussions']);
    check('tutor "Booking Requests" counts only pending bookings on their own slots', $tutorCards[0][1] === $count(
        'SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON s.slot_id = b.slot_id WHERE s.tutor_id = ? AND b.status = "pending"',
        [$tutor['user_id']]));
    $tuteeCards = $users->dashboardStats('tutee', (int) $tutee['user_id']);
    check('tutee gets its four cards',
        $labels($tuteeCards) === ['My Bookings', 'Verified Tutors', 'Active Courses', 'Open Discussions']);
    check('tutee "My Bookings" counts only their own bookings',
        $tuteeCards[0][1] === $count('SELECT COUNT(*) FROM bookings WHERE tutee_id = ?', [$tutee['user_id']]));

    // ---- dashboardBookings() ----
    group('dashboardBookings()');
    $total = fn(array $rows) => array_sum(array_column($rows, 'total'));
    check('tutee sees only their own bookings',
        $total($users->dashboardBookings((int) $tutee['user_id'], 'tutee')) === $count('SELECT COUNT(*) FROM bookings WHERE tutee_id = ?', [$tutee['user_id']]));
    check('tutor sees only bookings on their slots',
        $total($users->dashboardBookings((int) $tutor['user_id'], 'tutor')) === $count(
            'SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON s.slot_id = b.slot_id WHERE s.tutor_id = ?', [$tutor['user_id']]));
    check('admin sees every booking',
        $total($users->dashboardBookings((int) $admin['user_id'], 'admin')) === $count('SELECT COUNT(*) FROM bookings'));
    $rows = $users->dashboardBookings((int) $admin['user_id'], 'admin');
    check('each row has subject, status and total', $rows !== [] && array_keys($rows[0]) === ['subject', 'status', 'total']);

    // ---- featuredTutors() ----
    group('featuredTutors()');
    $featured = $users->featuredTutors();
    check('returns at most 8 tutors by default', count($featured) > 0 && count($featured) <= 8);
    check('a limit below 1 still returns 1 tutor', count($users->featuredTutors(0)) === 1);
    check('a limit above 20 is capped at 20', count($users->featuredTutors(100)) <= 20);
    $ids = array_column($featured, 'user_id');
    $marks = implode(',', array_fill(0, count($ids), '?'));
    check('only active tutors are listed',
        $count("SELECT COUNT(*) FROM users WHERE user_id IN ($marks) AND role = 'tutor' AND account_status = 'active'", $ids) === count($ids));
    check('rejected tutor applications are not listed',
        !in_array('rejected', array_column($users->featuredTutors(20), 'verification_status'), true));
    $statuses = array_column($featured, 'verification_status');
    $firstUnverified = array_search(true, array_map(fn($s) => $s !== 'verified', $statuses), true);
    check('verified tutors come before unverified ones',
        $firstUnverified === false || !in_array('verified', array_slice($statuses, $firstUnverified), true));
} catch (Throwable $e) {
    $failed++;
    echo "\n  ERROR  " . get_class($e) . ': ' . $e->getMessage() . "\n";
} finally {
    $cleanup();
}

echo "\nResult: $passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
