<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected Request $request;
    protected Database $db;

    public function __construct()
    {
        $app = Application::getInstance();
        $this->request = $app->request();
        $this->db = $app->db();
    }

    protected function json(mixed $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function view(string $template, array $data = [], ?string $layout = null): void
    {
        Response::view($template, $data, $layout);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }

    protected function validateCsrf(): void
    {
        if (!$this->request->validateCsrf()) {
            if ($this->request->isAjax()) {
                Response::json(['error' => 'CSRF token mismatch'], 403);
            }
            Response::error(403, 'CSRF token mismatch');
        }
    }

    protected function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
