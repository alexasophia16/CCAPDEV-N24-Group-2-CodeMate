<?php

// Error pages. This controller isn't in routes.php: the Router calls it when no route matches.
//   404 Not Found          - the page name doesn't exist (e.g. index.php?page=abc)
//   405 Method Not Allowed - the page exists, but only for the other method, e.g. typing a
//                            POST-only address like index.php?page=booking-cancel in the browser
// The third error, 403 Access denied (wrong role), comes from Controller::requireLogin().
// All three use the same view, frontend/views/errors/error.php.
class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->showError('Page not found', 'The page you were looking for does not exist.');
    }

    public function methodNotAllowed(): void
    {
        http_response_code(405);
        $this->showError('Action not available', 'That action can only be done using the buttons in the app.');
    }

    // Logged in: the error appears inside the normal layout (sidebar, topbar) with a link
    // back to the dashboard. Logged out: the plain login-style layout with a link to log in.
    private function showError(string $title, string $message): void
    {
        $this->render('errors/error', [
            'pageTitle' => "CodeMate - $title",
            'title'     => $title,
            'message'   => $message,
            'styles'    => ['role-pages'],
        ], current_user() ? 'app' : 'auth');
    }
}
