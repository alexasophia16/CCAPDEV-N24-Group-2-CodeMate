<?php

// The notification bell in the topbar (partials/topbar.php).
// Notifications are created by other features with Notification::create() or notifyAdmins(),
// e.g. when a booking is approved or someone replies to your discussion. This controller only
// handles clicking them. Both actions are POST because they change data (is_read).
class NotificationController extends Controller
{
    // POST notification-open: clicking one notification.
    // Marks it read, then opens the page it points to. markRead() only updates the row if it
    // belongs to you (AND user_id = ?), so a changed notification_id can't touch someone else's.
    // A link can include an id, e.g. "discussions-thread&id=7", which link_parts() splits up.
    public function open(): void
    {
        $user = $this->requireLogin();
        $notification = (new Notification())->markRead((int) input('notification_id'), $user['user_id']);

        if ($notification && $notification['link']) {
            redirect(...link_parts($notification['link']));
        }
        $this->goBack();
    }

    // POST notifications-read-all: the "Mark all as read" button. Stays on the same page.
    public function readAll(): void
    {
        $user = $this->requireLogin();
        (new Notification())->markAllRead($user['user_id']);
        $this->goBack();
    }

    // Back to the page the bell was clicked on. return_page is that page's query string,
    // e.g. "page=discussions-thread&id=7", so pages that need an id open correctly again.
    // It goes through link_parts()/url(), so it can only lead to one of our own pages.
    private function goBack(): void
    {
        $page = input('return_page');
        redirect(...link_parts($page !== '' ? $page : 'dashboard'));
    }
}
