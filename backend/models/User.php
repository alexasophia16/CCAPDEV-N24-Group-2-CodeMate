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

    // plain text for now, hashing comes later
    public function create(string $fullName, string $email, string $password, string $role = 'tutee'): int
    {
        $this->execute(
            'INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)',
            [$fullName, $email, $password, $role]
        );
        return $this->lastInsertId();
    }
}
