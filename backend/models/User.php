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
        $this->execute(
            'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)',
            [$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $role]
        );
        return $this->lastInsertId();
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
}
