<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Application;

class MailService
{
    private static ?array $config = null;

    public static function config(): array
    {
        if (self::$config !== null) {
            return self::$config;
        }

        $db = Application::getInstance()->db();
        $rows = $db->fetchAll("SELECT key, value FROM settings WHERE group_name = 'mail'");
        $cfg = [
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_encryption' => 'tls',
            'from_email' => '',
            'from_name' => 'Quiz System',
        ];
        foreach ($rows as $r) {
            $cfg[$r['key']] = $r['value'];
        }
        self::$config = $cfg;
        return $cfg;
    }

    public static function send(string $to, string $subject, string $htmlBody): array
    {
        $cfg = self::config();
        if (empty($cfg['smtp_host']) || empty($cfg['from_email'])) {
            return ['success' => false, 'error' => 'SMTP not configured'];
        }

        $host = $cfg['smtp_host'];
        $port = (int) ($cfg['smtp_port'] ?: 25);
        $enc = $cfg['smtp_encryption'] ?: 'none';
        $user = $cfg['smtp_user'];
        $pass = $cfg['smtp_pass'];
        $fromEmail = $cfg['from_email'];
        $fromName = $cfg['from_name'] ?: 'Quiz System';

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $errno = 0; $errstr = '';
        $fp = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            return ['success' => false, 'error' => "Connect failed: $errstr ($errno)"];
        }
        stream_set_timeout($fp, 15);

        $read = function () use ($fp) {
            $data = '';
            while (!feof($fp)) {
                $line = fgets($fp, 515);
                if ($line === false) break;
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $data;
        };
        $write = function (string $cmd) use ($fp) {
            fwrite($fp, $cmd . "\r\n");
        };
        $expect = function (string $resp, string $code) {
            return strpos($resp, $code) === 0;
        };

        try {
            $resp = $read();
            if (!$expect($resp, '220')) throw new \RuntimeException('Greeting failed: ' . trim($resp));

            $ehloHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $write('EHLO ' . $ehloHost);
            $resp = $read();
            if (!$expect($resp, '250')) throw new \RuntimeException('EHLO failed: ' . trim($resp));

            if ($enc === 'tls') {
                $write('STARTTLS');
                $resp = $read();
                if (!$expect($resp, '220')) throw new \RuntimeException('STARTTLS failed: ' . trim($resp));
                if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT)) {
                    throw new \RuntimeException('TLS negotiation failed');
                }
                $write('EHLO ' . $ehloHost);
                $resp = $read();
                if (!$expect($resp, '250')) throw new \RuntimeException('EHLO (TLS) failed: ' . trim($resp));
            }

            if ($user !== '') {
                $write('AUTH LOGIN');
                $resp = $read();
                if (!$expect($resp, '334')) throw new \RuntimeException('AUTH LOGIN failed: ' . trim($resp));
                $write(base64_encode($user));
                $resp = $read();
                if (!$expect($resp, '334')) throw new \RuntimeException('Username rejected: ' . trim($resp));
                $write(base64_encode($pass));
                $resp = $read();
                if (!$expect($resp, '235')) throw new \RuntimeException('Auth failed: ' . trim($resp));
            }

            $write('MAIL FROM:<' . $fromEmail . '>');
            $resp = $read();
            if (!$expect($resp, '250')) throw new \RuntimeException('MAIL FROM failed: ' . trim($resp));

            $write('RCPT TO:<' . $to . '>');
            $resp = $read();
            if (!$expect($resp, '250') && !$expect($resp, '251')) throw new \RuntimeException('RCPT TO failed: ' . trim($resp));

            $write('DATA');
            $resp = $read();
            if (!$expect($resp, '354')) throw new \RuntimeException('DATA failed: ' . trim($resp));

            $boundary = 'bnd_' . bin2hex(random_bytes(8));
            $headers = [];
            $headers[] = 'Date: ' . date('r');
            $headers[] = 'From: ' . self::encodeHeader($fromName) . ' <' . $fromEmail . '>';
            $headers[] = 'To: <' . $to . '>';
            $headers[] = 'Subject: ' . self::encodeHeader($subject);
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Content-Transfer-Encoding: 8bit';
            $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
            // Dot-stuff lines starting with '.'
            $message = preg_replace('/^\./m', '..', $message);
            $write($message);
            $resp = $read();
            if (!$expect($resp, '250')) throw new \RuntimeException('Send failed: ' . trim($resp));

            $write('QUIT');
            fclose($fp);
            return ['success' => true, 'error' => null];
        } catch (\Throwable $e) {
            @fclose($fp);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private static function encodeHeader(string $text): string
    {
        if (preg_match('/[^\x20-\x7e]/', $text)) {
            return '=?UTF-8?B?' . base64_encode($text) . '?=';
        }
        return $text;
    }
}
