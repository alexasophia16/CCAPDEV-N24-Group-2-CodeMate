<?php

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
