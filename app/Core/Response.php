<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function view(string $__view, array $data = [], ?string $__layout = null): void
    {
        $basePath = dirname(__DIR__, 2) . '/resources/views';
        $templateFile = $basePath . '/' . str_replace('.', '/', $__view) . '.php';

        if (!file_exists($templateFile)) {
            throw new \RuntimeException("View not found: {$__view}");
        }

        $data['csrf_token'] = $_SESSION['csrf_token'] ?? '';
        $data['auth'] = Auth::user();
        $data['locale'] = I18n::getLocale();
        $data['htmlLang'] = I18n::htmlLang();
        $data['t'] = static fn(string $key, array $params = []): string => I18n::t($key, $params);

        extract($data, EXTR_SKIP);

        if ($__layout !== null) {
            ob_start();
            require $templateFile;
            $content = ob_get_clean();

            $layoutFile = $basePath . '/layout/' . $__layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$__layout}");
            }
            require $layoutFile;
        } else {
            require $templateFile;
        }
        exit;
    }

    public static function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer);
    }

    public static function error(int $code, string $message = ''): void
    {
        http_response_code($code);
        echo "<h1>{$code}</h1>";
        if ($message) {
            echo '<p>' . htmlspecialchars($message) . '</p>';
        }
        exit;
    }
}
