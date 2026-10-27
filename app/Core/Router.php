<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\AuthorizationException;
use App\Exceptions\NotFoundException;

final class Router
{
    /** @var array<string, array<int, array{pattern: string, handler: mixed, middleware: array<int, string>}>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'PATCH' => [],
        'DELETE' => [],
    ];

    /** @var array<int, string> */
    private array $groupMiddleware = [];

    public function get(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function patch(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    public function delete(string $path, callable|array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(array $middleware, callable $callback): void
    {
        $previous = $this->groupMiddleware;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $callback($this);
        $this->groupMiddleware = $previous;
    }

    private function addRoute(string $method, string $path, callable|array $handler, array $middleware): void
    {
        $pattern = $this->compilePattern($path);

        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
            'raw' => $path,
        ];
    }

    private function compilePattern(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        $allowedMethods = [];

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, static fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                $this->runRoute($route, $request, $params);
                return;
            }
        }

        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                if (preg_match($route['pattern'], $path)) {
                    Response::abort(404, 'Method not allowed for this route.');
                    return;
                }
            }
        }

        Response::abort(404, 'Page not found.');
    }

    private function runRoute(array $route, Request $request, array $params): void
    {
        try {
            foreach ($route['middleware'] as $middlewareName) {
                [$name, $param] = array_pad(explode(':', $middlewareName, 2), 2, null);
                $middlewareClass = 'App\\Middleware\\' . $name;
                if (!class_exists($middlewareClass)) {
                    throw new \RuntimeException("Middleware [{$name}] not found.");
                }
                (new $middlewareClass())->handle($request, $param);
            }

            $handler = $route['handler'];

            if (is_array($handler)) {
                [$controllerClass, $action] = $handler;
                $controller = new $controllerClass();
                $controller->{$action}($request, ...array_values($params));
                return;
            }

            $handler($request, ...array_values($params));
        } catch (AuthorizationException $e) {
            if ($request->wantsJson()) {
                Response::error($e->getMessage() ?: 'Forbidden', [], 403);
            }
            Response::abort(403, $e->getMessage());
        } catch (NotFoundException $e) {
            if ($request->wantsJson()) {
                Response::error($e->getMessage() ?: 'Not found', [], 404);
            }
            Response::abort(404, $e->getMessage());
        }
    }
}
