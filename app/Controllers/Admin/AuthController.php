<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check() && Auth::isAdmin()) {
            Response::redirect('/admin/dashboard');
        }
        Response::view('admin.auth.login', []);
    }

    public function login(): void
    {
        $this->validateCsrf();

        $username = trim($this->request->post('username', ''));
        $password = $this->request->post('password', '');

        if ($username === '' || $password === '') {
            Response::view('admin.auth.login', [
                'error' => 'Please enter both username and password.',
                'old_username' => $username,
            ]);
            return;
        }

        if (!Auth::attempt($username, $password)) {
            Response::view('admin.auth.login', [
                'error' => 'Invalid credentials. Please try again.',
                'old_username' => $username,
            ]);
            return;
        }

        if (!Auth::isAdmin()) {
            Auth::logout();
            Response::view('admin.auth.login', [
                'error' => 'You do not have admin privileges.',
                'old_username' => $username,
            ]);
            return;
        }

        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $this->request->ip(),
        ], 'id = ?', [Auth::id()]);

        Response::redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/admin/login');
    }
}
