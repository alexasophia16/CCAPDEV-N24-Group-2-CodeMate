<?php
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

    public function create(string $fullName, string $email, string $password, string $role = 'tutee'): int
    {
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

            // New tutors enter the existing verification workflow as pending.
            if ($role === 'tutor') {
                $this->execute(
                    'INSERT INTO tutor_profiles (tutor_id, verification_status) VALUES (?, ?)',
                    [$userId, 'pending']
                );
            }

            $this->db->commit();
            return $userId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function updatePassword(int $userId, string $password): void
    {
        $this->execute(
            'UPDATE users SET password = ? WHERE user_id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $userId]
        );
    }
}
