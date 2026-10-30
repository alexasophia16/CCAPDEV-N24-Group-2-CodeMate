<?php
class Router
{
    private array $routes;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'POST' : 'GET';
        $page = $_GET['page'] ?? (current_user() ? 'dashboard' : 'login');
        $route = $this->routes["$method $page"] ?? null;

        if ($route === null) {
            $otherMethod = $method === 'POST' ? 'GET' : 'POST';
            $errors = new ErrorController();
            isset($this->routes["$otherMethod $page"]) ? $errors->methodNotAllowed() : $errors->notFound();
            return;
        }

        [$controller, $action] = $route;
        (new $controller())->$action();
    }
}
