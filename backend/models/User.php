<?php
class User extends Model
{
    public function findById(int $userId): ?array {
        return $this->fetchOne('SELECT * FROM users WHERE user_id = ?', [$userId]);
    }
    public function findByEmail(string $email): ?array {
        return $this->fetchOne('SELECT * FROM users WHERE email = ?', [$email]);
    }
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== null;
    }
    public function create(string $fullName, string $email, string $password, string $role = 'tutee'): int {
        if (!in_array($role, ['tutee', 'tutor'], true)) {
            throw new InvalidArgumentException('Invalid account role.');
        }

        $this->db->beginTransaction();
        try {
            $this->execute(
                'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)',
                [$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $role]
            );
            $userId = $this->lastInsertId();

            if ($role === 'tutor') {
                $this->execute(
                    'INSERT INTO tutor_profiles (tutor_id, verification_status) VALUES (?, ?)',
                    [$userId, 'pending']
                );
            }

            $this->db->commit();
            return $userId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
    public function all(): array {
        return $this->fetchAll(
            'SELECT user_id, full_name, email, role, account_status, created_at
             FROM users ORDER BY created_at DESC, user_id DESC'
        );
    }
    public function setAccountStatus(int $userId, string $status): bool {
        if (!in_array($status, ['active', 'deactivated'], true)) return false;
        return $this->execute('UPDATE users SET account_status = ? WHERE user_id = ? AND role <> "admin"', [$status, $userId]) > 0;
    }
    public function dashboardStats(string $role, int $userId): array {
        if ($role === 'admin') {
            return [
                ['Registered Users', (int)$this->fetchValue('SELECT COUNT(*) FROM users')],
                ['Verified Tutors', (int)$this->fetchValue('SELECT COUNT(*) FROM tutor_profiles WHERE verification_status = "verified"')],
                ['Active Courses', (int)$this->fetchValue('SELECT COUNT(*) FROM courses WHERE status = "active"')],
                ['Pending Verifications', (int)$this->fetchValue('SELECT COUNT(*) FROM tutor_profiles WHERE verification_status = "pending"')],
            ];
        }
        if ($role === 'tutor') {
            return [
                ['Booking Requests', (int)$this->fetchValue('SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON b.slot_id=s.slot_id WHERE s.tutor_id=? AND b.status="pending"', [$userId])],
                ['Upcoming Sessions', (int)$this->fetchValue('SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON b.slot_id=s.slot_id WHERE s.tutor_id=? AND b.status="confirmed"', [$userId])],
                ['Completed Sessions', (int)$this->fetchValue('SELECT COUNT(*) FROM bookings b JOIN tutor_slots s ON b.slot_id=s.slot_id WHERE s.tutor_id=? AND b.status="completed"', [$userId])],
                ['Open Discussions', (int)$this->fetchValue('SELECT COUNT(*) FROM discussions WHERE status="visible"')],
            ];
        }
        return [
            ['My Bookings', (int)$this->fetchValue('SELECT COUNT(*) FROM bookings WHERE tutee_id=?', [$userId])],
            ['Verified Tutors', (int)$this->fetchValue('SELECT COUNT(*) FROM tutor_profiles WHERE verification_status="verified"')],
            ['Active Courses', (int)$this->fetchValue('SELECT COUNT(*) FROM courses WHERE status="active"')],
            ['Open Discussions', (int)$this->fetchValue('SELECT COUNT(*) FROM discussions WHERE status="visible"')],
        ];
    }


    // Dashboard chart data is scoped to the signed-in user's role.
    public function dashboardBookings(int $userId, string $role): array {
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
            'SELECT c.course_name AS subject, b.status, COUNT(*) AS total
             FROM bookings b
             JOIN tutor_slots s ON s.slot_id = b.slot_id
             JOIN courses c ON c.course_id = s.course_id
             ' . $where . '
             GROUP BY c.course_id, c.course_name, b.status
             ORDER BY c.course_name, b.status',
            $params
        );
    }

    public function featuredTutors(int $limit = 8): array {
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
             LEFT JOIN (
                 SELECT s.tutor_id, AVG(r.rating) AS credibility
                 FROM tutor_slots s
                 JOIN bookings b ON b.slot_id = s.slot_id
                 JOIN reviews r ON r.booking_id = b.booking_id
                 GROUP BY s.tutor_id
             ) rv ON rv.tutor_id = u.user_id
             LEFT JOIN (
                 SELECT s.tutor_id, COUNT(*) AS sessions
                 FROM tutor_slots s
                 JOIN bookings b ON b.slot_id = s.slot_id
                 WHERE b.status = "completed"
                 GROUP BY s.tutor_id
             ) bs ON bs.tutor_id = u.user_id
             WHERE u.role = "tutor" AND u.account_status = "active"
             GROUP BY u.user_id, u.full_name, tp.verification_status, rv.credibility, bs.sessions
             ORDER BY (tp.verification_status = "verified") DESC, credibility DESC, sessions DESC
             LIMIT ' . $limit
        );
    }
}
