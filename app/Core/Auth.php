<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $db = Database::getInstance();
        $user = $db->fetch(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 1',
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? '';
        $_SESSION['user_display_name'] = $user['display_name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';

        return true;
    }

    public static function refresh(): void
    {
        if (!self::check()) return;
        $user = Database::getInstance()->fetch('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
        if ($user) {
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? '';
            $_SESSION['user_display_name'] = $user['display_name'] ?? '';
            $_SESSION['user_email'] = $user['email'] ?? '';
        }
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'role' => $_SESSION['user_role'],
            'username' => $_SESSION['user_name'],
            'avatar' => $_SESSION['user_avatar'] ?? '',
            'display_name' => $_SESSION['user_display_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
        ];
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        $role = self::role();
        return in_array($role, ['super_admin', 'admin', 'editor'], true);
    }

    public static function isSuperAdmin(): bool
    {
        return self::role() === 'super_admin';
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            if ((new Request())->isAjax()) {
                Response::json(['error' => 'Unauthorized'], 401);
            }
            Response::redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Response::error(403, 'Forbidden');
        }
    }
}
