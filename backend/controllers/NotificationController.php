<?php

class NotificationController extends Controller
{
    // mark as read, then go to its page (a link can include an id, e.g. "discussions-thread&id=7")
    public function open(): void
    {
        $user = $this->requireLogin();
        $notification = (new Notification())->markRead((int) input('notification_id'), $user['user_id']);

        if ($notification && $notification['link']) {
            redirect(...link_parts($notification['link']));
        }
        $this->goBack();
    }

    public function readAll(): void
    {
        $user = $this->requireLogin();
        (new Notification())->markAllRead($user['user_id']);
        $this->goBack();
    }

    // return_page is the query string of the page the bell was clicked on, e.g.
    // "page=discussions-thread&id=7", so pages that need an id open correctly again
    private function goBack(): void
    {
        $page = input('return_page');
        redirect(...link_parts($page !== '' ? $page : 'dashboard'));
    }
}
