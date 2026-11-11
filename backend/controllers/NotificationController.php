<?php

class NotificationController extends Controller
{
    // mark as read, then go to its page
    public function open(): void
    {
        $user = $this->requireLogin();
        $notification = (new Notification())->markRead((int) input('notification_id'), $user['user_id']);

        if ($notification && $notification['link']) {
            redirect($notification['link']);
        }
        $this->goBack();
    }

    public function readAll(): void
    {
        $user = $this->requireLogin();
        (new Notification())->markAllRead($user['user_id']);
        $this->goBack();
    }

    private function goBack(): void
    {
        $page = input('return_page');
        redirect($page !== '' ? $page : 'dashboard');
    }
}
