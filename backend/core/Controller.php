<?php
class Controller
{
    // $data keys become view variables; styles/scripts are file names without extension
    protected function render(string $view, array $data = [], string $layout = 'app'): void
    {
        $data += [
            'pageTitle'  => 'CodeMate',
            'activePage' => $_GET['page'] ?? 'dashboard',
            'styles'     => [],
            'scripts'    => [],
        ];

        if ($layout === 'app') {
            $data += $this->layoutData();
        }

        extract($data);

        ob_start();
        require APP_ROOT . "/frontend/views/$view.php";
        $content = ob_get_clean();

        require APP_ROOT . "/frontend/views/layouts/$layout.php";
    }

    //if nobody is logged in, it flashes "Please log in first" and redirects to login. 
    protected function requireLogin(array $roles = []): array
    {
        $user = current_user();

        if ($user === null) {
            flash('error', 'Please log in first.');
            redirect('login');
        }

        if ($roles && !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            $this->render('errors/error', [
                'pageTitle' => 'CodeMate - Access denied',
                'title'     => 'Access denied',
                'message'   => 'Your account type cannot open this page.',
                'styles'    => ['role-pages'],
            ]);
            exit;
        }

        return $user;
    }

    // temporary page until the feature is built
    protected function placeholder(string $title, string $phase1File): void
    {
        $this->render('partials/placeholder', [
            'pageTitle'  => "CodeMate - $title",
            'title'      => $title,
            'phase1File' => $phase1File,
            'styles'     => ['role-pages'],
        ]);
    }

    //adds what the layout needs on every page: the current user, their latest notifications, 
    // the unread notification count and the unread message count for the sidebar badge
    private function layoutData(): array
    {
        $user = current_user();
        if ($user === null) {
            return [];
        }

        $notifications = new Notification();

        return [
            'currentUser'         => $user,
            'notifications'       => $notifications->latestFor($user['user_id']),
            'unreadNotifications' => $notifications->unreadCount($user['user_id']),
            'unreadConversations' => (new Message())->unreadConversationCount($user['user_id']),
        ];
    }
}
