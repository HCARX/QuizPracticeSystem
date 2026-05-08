<?php

declare(strict_types=1);

namespace App\Core;

class Application
{
    private static ?self $instance = null;
    private Router $router;
    private Database $database;
    private Request $request;
    private array $config = [];

    private function __construct()
    {
        $this->loadConfig();
        $this->request = new Request();
        $this->database = Database::getInstance($this->config['database'] ?? []);
        $this->router = new Router($this->request);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function boot(): self
    {
        $app = self::getInstance();
        session_start();
        $app->initCsrfToken();
        return $app;
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function db(): Database
    {
        return $this->database;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }
        return $value;
    }

    private function loadConfig(): void
    {
        $configPath = dirname(__DIR__, 2) . '/config';
        foreach (glob($configPath . '/*.php') as $file) {
            $name = basename($file, '.php');
            if ($name === 'routes') {
                continue;
            }
            $this->config[$name] = require $file;
        }
    }

    private function initCsrfToken(): void
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function handleException(\Throwable $e): void
    {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        $message = sprintf(
            "[%s] %s in %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );
        file_put_contents($logDir . '/error.log', $message, FILE_APPEND);

        $isDebug = $this->config('app.debug', false);
        if ($this->request->isAjax()) {
            Response::json(['error' => $isDebug ? $e->getMessage() : 'Internal Server Error'], 500);
        } else {
            http_response_code(500);
            if ($isDebug) {
                echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            } else {
                echo '<h1>500 - Internal Server Error</h1>';
            }
        }
    }
}
