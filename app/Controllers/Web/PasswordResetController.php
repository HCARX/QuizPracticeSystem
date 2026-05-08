<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Services\MailService;

class PasswordResetController extends Controller
{
    public function showRequest(): void
    {
        $this->view('web.auth.forgot', [
            'pageTitle' => 'Forgot Password',
        ], 'app');
    }

    public function sendLink(): void
    {
        $this->validateCsrf();
        $email = trim((string) $this->request->post('email', ''));

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user = $this->db->fetch('SELECT id, email FROM users WHERE email = ? AND status = 1', [$email]);
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);
                $this->db->insert('password_resets', [
                    'user_id' => $user['id'],
                    'token' => $token,
                    'expires_at' => $expires,
                    'used' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $link = $scheme . '://' . $host . '/reset-password/' . $token;

                $body = '<p>You requested to reset your password.</p>'
                      . '<p><a href="' . htmlspecialchars($link) . '">Click here to reset your password</a></p>'
                      . '<p>This link expires in 1 hour. If you did not request this, ignore this email.</p>';
                MailService::send($user['email'], 'Reset Your Password', $body);
            }
        }

        $_SESSION['flash_success'] = 'If the email exists, a reset link has been sent.';
        $this->redirect('/forgot-password');
    }

    public function showReset(string $token): void
    {
        $row = $this->findValidToken($token);
        if (!$row) {
            $_SESSION['flash_error'] = 'Invalid or expired reset link.';
            $this->redirect('/forgot-password');
            return;
        }
        $this->view('web.auth.reset', [
            'pageTitle' => 'Reset Password',
            'token' => $token,
        ], 'app');
    }

    public function reset(string $token): void
    {
        $this->validateCsrf();
        $row = $this->findValidToken($token);
        if (!$row) {
            $_SESSION['flash_error'] = 'Invalid or expired reset link.';
            $this->redirect('/forgot-password');
            return;
        }

        $new = (string) $this->request->post('new_password', '');
        $confirm = (string) $this->request->post('confirm_password', '');

        if (strlen($new) < 6 || $new !== $confirm) {
            $this->view('web.auth.reset', [
                'pageTitle' => 'Reset Password',
                'token' => $token,
                'error' => 'Passwords must match and be at least 6 characters.',
            ], 'app');
            return;
        }

        $this->db->update('users', [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$row['user_id']]);

        $this->db->update('password_resets', ['used' => 1], 'id = ?', [$row['id']]);

        $_SESSION['flash_success'] = 'Password reset. Please sign in.';
        $this->redirect('/login');
    }

    private function findValidToken(string $token): ?array
    {
        return $this->db->fetch(
            'SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > ?',
            [$token, date('Y-m-d H:i:s')]
        );
    }
}
