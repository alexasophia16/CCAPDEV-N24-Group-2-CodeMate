<?php
class ModelPasswordUpgrade extends Model
{
    public function upgrade(int $userId, string $password): void {
        $this->execute('UPDATE users SET password = ? WHERE user_id = ?', [password_hash($password, PASSWORD_DEFAULT), $userId]);
    }
}
