<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\MailService;

class MailSettingsController extends Controller
{
    private const FIELDS = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_encryption', 'from_email', 'from_name'];

    public function index(): void
    {
        $rows = $this->db->fetchAll("SELECT key, value FROM settings WHERE group_name = 'mail'");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }

        $this->view('admin.mail.index', [
            'pageTitle' => 'Mail Settings',
            'currentNav' => 'mail',
            'settings' => $settings,
        ], 'admin');
    }

    public function update(): void
    {
        $this->validateCsrf();

        foreach (self::FIELDS as $f) {
            $value = (string) $this->request->post($f, '');
            if ($f === 'smtp_encryption' && !in_array($value, ['none', 'ssl', 'tls'], true)) {
                $value = 'none';
            }
            $this->upsert($f, $value);
        }

        $_SESSION['flash_success'] = 'Mail settings saved.';
        $this->redirect('/admin/mail');
    }

    public function test(): void
    {
        $this->validateCsrf();
        $to = trim((string) $this->request->post('to', ''));
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Please provide a valid recipient email.';
            $this->redirect('/admin/mail');
            return;
        }

        $result = MailService::send($to, 'Test Email - Quiz System', '<p>This is a test email from Quiz Practice System.</p>');
        if ($result['success']) {
            $_SESSION['flash_success'] = 'Test email sent successfully to ' . $to;
        } else {
            $_SESSION['flash_error'] = 'Failed to send test email: ' . ($result['error'] ?? 'unknown error');
        }
        $this->redirect('/admin/mail');
    }

    private function upsert(string $key, string $value): void
    {
        $this->db->query(
            "INSERT INTO settings (key, value, group_name, updated_at) VALUES (?, ?, 'mail', CURRENT_TIMESTAMP)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value, group_name = 'mail', updated_at = CURRENT_TIMESTAMP",
            [$key, $value]
        );
    }
}
