<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $query;
    private array $body;
    private array $server;
    private array $files;

    public function __construct()
    {
        $this->server = $_SERVER;
        $this->query = $_GET;
        $this->files = $_FILES;
        $this->body = $this->parseBody();
    }

    private function parseBody(): array
    {
        if ($this->method() === 'GET') {
            return [];
        }

        $contentType = $this->server['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?: [];
        }

        return $_POST;
    }

    public function method(): string
    {
        $method = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($this->body['_method'])) {
            return strtoupper($this->body['_method']);
        }
        return $method;
    }

    public function uri(): string
    {
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return '/' . trim($uri, '/');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function body(): array
    {
        return $this->body;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function isAjax(): bool
    {
        return ($this->server['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            || str_contains($this->server['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function validateCsrf(): bool
    {
        if ($this->method() === 'GET') {
            return true;
        }
        $token = $this->input('_csrf_token')
            ?? $this->server['HTTP_X_CSRF_TOKEN']
            ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public function validate(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $ruleStr) {
            $value = $this->input($field);
            $ruleList = explode('|', $ruleStr);

            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = match ($rule) {
                    'required' => ($value === null || $value === '') ? "{$field} is required" : null,
                    'string' => (!is_string($value) && $value !== null) ? "{$field} must be a string" : null,
                    'integer' => ($value !== null && !filter_var($value, FILTER_VALIDATE_INT) && $value !== 0) ? "{$field} must be an integer" : null,
                    'min' => (is_string($value) && strlen($value) < (int)$params[0]) ? "{$field} must be at least {$params[0]} characters" : null,
                    'max' => (is_string($value) && strlen($value) > (int)$params[0]) ? "{$field} must be at most {$params[0]} characters" : null,
                    'in' => ($value !== null && !in_array($value, $params)) ? "{$field} must be one of: " . implode(', ', $params) : null,
                    'email' => ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) ? "{$field} must be a valid email" : null,
                    default => null,
                };

                if ($error !== null) {
                    $errors[$field] = $error;
                    break;
                }
            }

            if (!isset($errors[$field]) && $value !== null) {
                $data[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        return ['errors' => $errors, 'data' => $data];
    }
}
