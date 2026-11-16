<?php

// The notifications table, shown in the topbar bell.
// Each row has a message, the user it's for, a link to open, and is_read.
// Other features call create() or notifyAdmins(); NotificationController handles clicks.
class Notification extends Model
{
    // The newest notifications for the bell panel. Controller::layoutData() calls this on
    // every page. LIMIT can't be a ? parameter, so $limit is cast to int to keep it safe.
    public function latestFor(int $userId, int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    // How many are unread, for the red dot on the bell
    public function unreadCount(int $userId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE',
            [$userId]
        );
    }

    // Marks one notification as read and returns it (so the controller can follow its link).
    // "AND user_id = ?" makes sure you can only mark your own; someone else's id returns null.
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

    // "Mark all as read" in the bell panel
    public function markAllRead(int $userId): void
    {
        $this->execute('UPDATE notifications SET is_read = TRUE WHERE user_id = ?', [$userId]);
    }

    // One notification for every active admin (new booking requests, tutor applications,
    // reports, new resources). INSERT ... SELECT adds one row per admin in a single query.
    public function notifyAdmins(string $message, ?string $link = null): void
    {
        $this->execute(
            'INSERT INTO notifications (user_id, message, link)
             SELECT user_id, ?, ? FROM users WHERE role = "admin" AND account_status = "active"',
            [self::fit($message), $link]
        );
    }

    // One notification for one user.
    // $link is a page, optionally with parameters, e.g. "discussions-thread&id=7"
    public function create(int $userId, string $message, ?string $link = null): void
    {
        $this->execute(
            'INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)',
            [$userId, self::fit($message), $link]
        );
    }

    // message is VARCHAR(255); long titles are cut so the insert can't fail
    private static function fit(string $message): string
    {
        return mb_strlen($message) > 255 ? mb_substr($message, 0, 252) . '...' : $message;
    }
}
