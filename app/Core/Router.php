<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private Request $request;
    private array $middlewareGroups = [];
    private string $prefix = '';
    private array $currentMiddleware = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function group(array $options, callable $callback): self
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->currentMiddleware;

        if (isset($options['prefix'])) {
            $this->prefix = $previousPrefix . '/' . trim($options['prefix'], '/');
        }
        if (isset($options['middleware'])) {
            $mw = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            $this->currentMiddleware = array_merge($this->currentMiddleware, $mw);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->currentMiddleware = $previousMiddleware;

        return $this;
    }

    public function get(string $path, array|string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array|string $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, array|string $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|string $handler): self
    {
        $fullPath = $this->prefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $this->currentMiddleware,
            'pattern' => $this->buildPattern($fullPath),
        ];

        return $this;
    }

    private function buildPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $this->request->method();
        $uri = $this->request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                foreach ($route['middleware'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    if (!$middleware->handle($this->request)) {
                        return;
                    }
                }

                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        if ($this->request->isAjax()) {
            Response::json(['error' => 'Not Found'], 404);
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }

    private function callHandler(array|string $handler, array $params): void
    {
        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $params);
        } elseif (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }
}
