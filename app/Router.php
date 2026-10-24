<?php
declare(strict_types=1);
namespace App;

final class Router {
    private array $routes = ['GET' => [], 'POST' => []];
    public function get(string $path, array $handler): void { $this->routes['GET'][$path] = $handler; }
    public function post(string $path, array $handler): void { $this->routes['POST'][$path] = $handler; }
    public function dispatch(string $method, string $path): void {
        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) { http_response_code(404); render('errors/404', ['title' => 'Page not found']); return; }
        [$class, $action] = $handler;
        (new $class())->$action();
    }
}
