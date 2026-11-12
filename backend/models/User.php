<?php

// Accounts (the users table), plus the numbers and lists shown on the dashboard.
class User extends Model
{
    public function findById(int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE user_id = ?', [$userId]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    // Creates a tutee or tutor account and returns its id.
    // A tutor also gets a tutor_profiles row marked "pending", so they only show up in
    // Find a Tutor after an admin verifies them. Both inserts happen in one transaction.
    public function create(string $fullName, string $email, string $password, string $role = 'tutee'): int
    {
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            throw new InvalidArgumentException('Invalid account role.');
        }

        $this->db->beginTransaction();
        try {
            $this->execute(
                'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)',
                [$fullName, $email, $password, $role]
            );
            $userId = $this->lastInsertId();

            if ($role === 'tutor') {
                $this->execute(
                    'INSERT INTO tutor_profiles (tutor_id, verification_status) VALUES (?, "pending")',
                    [$userId]
                );
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }

        if ($role === 'tutor') {
            (new Notification())->notifyAdmins("$fullName applied to be a tutor.", 'admin-verify-tutors');
        }
        return $userId;
    }

    // User Management list, newest first
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT user_id, full_name, email, role, account_status, created_at
               FROM users
              ORDER BY created_at DESC, user_id DESC'
        );
    }

    // Activate or deactivate an account. Admin accounts are never changed.
    public function setAccountStatus(int $userId, string $status): bool
    {
        if (!in_array($status, ['active', 'deactivated'], true)) {
            return false;
        }
        return $this->execute(
            'UPDATE users SET account_status = ? WHERE user_id = ? AND role <> "admin"',
            [$status, $userId]
        ) > 0;
    }

    // ---- Dashboard ----

    // The four number cards at the top of the dashboard: [label, number] pairs per role
    public function dashboardStats(string $role, int $userId): array
    {
        $verifiedTutors = $this->count('SELECT COUNT(*) FROM tutor_profiles WHERE verification_status = "verified"');
        $activeCourses = $this->count('SELECT COUNT(*) FROM courses WHERE status = "active"');
        $openDiscussions = $this->count('SELECT COUNT(*) FROM discussions WHERE status = "visible"');

        if ($role === 'admin') {
            return [
                ['Registered Users', $this->count('SELECT COUNT(*) FROM users')],
                ['Verified Tutors', $verifiedTutors],
                ['Active Courses', $activeCourses],
                ['Pending Verifications', $this->count('SELECT COUNT(*) FROM tutor_profiles WHERE verification_status = "pending"')],
            ];
        }

        if ($role === 'tutor') {
            $tutorBookings = 'SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON s.slot_id = b.slot_id
                               WHERE s.tutor_id = ? AND b.status = ?';
            return [
                ['Booking Requests', $this->count($tutorBookings, [$userId, 'pending'])],
                ['Upcoming Sessions', $this->count($tutorBookings, [$userId, 'confirmed'])],
                ['Completed Sessions', $this->count($tutorBookings, [$userId, 'completed'])],
                ['Open Discussions', $openDiscussions],
            ];
        }

        return [
            ['My Bookings', $this->count('SELECT COUNT(*) FROM bookings WHERE tutee_id = ?', [$userId])],
            ['Verified Tutors', $verifiedTutors],
            ['Active Courses', $activeCourses],
            ['Open Discussions', $openDiscussions],
        ];
    }

    private function count(string $sql, array $params = []): int
    {
        return (int) $this->fetchValue($sql, $params);
    }

    // Booking counts per course and status for the dashboard chart.
    // Tutees see their own bookings, tutors see bookings on their slots, admins see everything.
    public function dashboardBookings(int $userId, string $role): array
    {
        $where = '';
        $params = [];
        if ($role === 'tutee') {
            $where = 'WHERE b.tutee_id = ?';
            $params[] = $userId;
        } elseif ($role === 'tutor') {
            $where = 'WHERE s.tutor_id = ?';
            $params[] = $userId;
        }

        return $this->fetchAll(
            "SELECT c.course_name AS subject, b.status, COUNT(*) AS total
               FROM bookings b
               JOIN tutor_slots s ON s.slot_id = b.slot_id
               JOIN courses c ON c.course_id = s.course_id
             $where
              GROUP BY c.course_id, c.course_name, b.status
              ORDER BY c.course_name, b.status",
            $params
        );
    }

    // Tutor cards on a tutee's dashboard: verified tutors first, then by rating and sessions.
    // $limit is kept between 1 and 20 because LIMIT can't be a ? parameter.
    public function featuredTutors(int $limit = 8): array
    {
        $limit = max(1, min(20, $limit));
        return $this->fetchAll(
            'SELECT u.user_id, u.full_name, tp.verification_status,
                    COALESCE(rv.credibility, 0) AS credibility,
                    COALESCE(bs.sessions, 0) AS sessions,
                    GROUP_CONCAT(DISTINCT c.course_name ORDER BY c.course_name SEPARATOR "||") AS subjects
               FROM users u
               JOIN tutor_profiles tp ON tp.tutor_id = u.user_id
               LEFT JOIN tutor_courses tc ON tc.tutor_id = u.user_id
               LEFT JOIN courses c ON c.course_id = tc.course_id AND c.status = "active"
               LEFT JOIN (SELECT s.tutor_id, AVG(r.rating) AS credibility
                            FROM tutor_slots s
                            JOIN bookings b ON b.slot_id = s.slot_id
                            JOIN reviews r ON r.booking_id = b.booking_id
                           GROUP BY s.tutor_id) rv ON rv.tutor_id = u.user_id
               LEFT JOIN (SELECT s.tutor_id, COUNT(*) AS sessions
                            FROM tutor_slots s
                            JOIN bookings b ON b.slot_id = s.slot_id
                           WHERE b.status = "completed"
                           GROUP BY s.tutor_id) bs ON bs.tutor_id = u.user_id
              WHERE u.role = "tutor" AND u.account_status = "active"
              GROUP BY u.user_id, u.full_name, tp.verification_status, rv.credibility, bs.sessions
              ORDER BY (tp.verification_status = "verified") DESC, credibility DESC, sessions DESC
              LIMIT ' . $limit
        );
    }
}
