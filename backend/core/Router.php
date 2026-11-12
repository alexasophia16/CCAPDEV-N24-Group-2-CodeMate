<?php
class Router
{
    private array $routes;

    public function __construct(array $routes) //stores array from routes.php
    {
        $this->routes = $routes;
    }

    public function dispatch(): void
    {
        //$method is 'POST' for a submitted form, otherwise 'GET', for example a clicked link or a URL
        //typed into the address bar
        $method = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'POST' : 'GET';
        //comes from the URL (index.php?page=find-tutor gives find-tutor). with no ?page=, it goes to dashboard
        //if you're logged in or login if you're not. ?? means "use this if the left side is missing"
        $page = $_GET['page'] ?? (current_user() ? 'dashboard' : 'login');
        //look up the route. it builds the key "GET find-tutor" and looks it up in the array. the method is part of the
        //key, so the same page name can do different things, like GET book-consultation shows form and POST saves the booking.
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
