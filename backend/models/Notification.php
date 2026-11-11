<?php

class Notification extends Model
{
    public function latestFor(int $userId, int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    public function unreadCount(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE',
            [$userId]
        );
    }

    public function markRead(int $notificationId, int $userId): ?array
    {
        $this->execute(
            'UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_id = ?',
            [$notificationId, $userId]
        );
        return $this->fetchOne(
            'SELECT * FROM notifications WHERE notification_id = ? AND user_id = ?',
            [$notificationId, $userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->execute('UPDATE notifications SET is_read = TRUE WHERE user_id = ?', [$userId]);
    }

    // one notification for every active admin (used when a new booking needs approval)
    public function notifyAdmins(string $message, ?string $link = null): void
    {
        $this->execute(
            'INSERT INTO notifications (user_id, message, link)
             SELECT user_id, ?, ? FROM users WHERE role = "admin" AND account_status = "active"',
            [$message, $link]
        );
    }

    public function create(int $userId, string $message, ?string $link = null): void
    {
        $this->execute(
            'INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)',
            [$userId, $message, $link]
        );
    }
}
