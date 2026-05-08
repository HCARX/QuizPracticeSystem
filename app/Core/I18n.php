<?php

declare(strict_types=1);

namespace App\Core;

class I18n
{
    private static array $messages = [];
    private static ?string $locale = null;
    public const SUPPORTED = ['zh', 'en'];
    public const DEFAULT = 'zh';

    public static function setLocale(string $code): void
    {
        if (!in_array($code, self::SUPPORTED, true)) {
            return;
        }
        $_SESSION['locale'] = $code;
        self::$locale = $code;
        self::$messages = [];
    }

    public static function getLocale(): string
    {
        if (self::$locale !== null) {
            return self::$locale;
        }
        $code = $_SESSION['locale'] ?? self::DEFAULT;
        if (!in_array($code, self::SUPPORTED, true)) {
            $code = self::DEFAULT;
        }
        return self::$locale = $code;
    }

    public static function load(): array
    {
        if (!empty(self::$messages)) {
            return self::$messages;
        }
        $locale = self::getLocale();
        $file = dirname(__DIR__, 2) . '/resources/lang/' . $locale . '.php';
        if (file_exists($file)) {
            self::$messages = require $file;
        }
        return self::$messages;
    }

    public static function t(string $key, array $params = []): string
    {
        $messages = self::load();
        $value = $messages[$key] ?? $key;
        foreach ($params as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }
        return $value;
    }

    public static function htmlLang(): string
    {
        return self::getLocale() === 'zh' ? 'zh-CN' : 'en';
    }
}
