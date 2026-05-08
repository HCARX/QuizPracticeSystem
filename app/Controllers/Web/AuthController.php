<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }
        $this->view('web.auth.login', [
            'pageTitle' => 'Sign In',
        ], 'app');
    }

    public function login(): void
    {
        $this->validateCsrf();

        $username = trim($this->request->post('username', ''));
        $password = $this->request->post('password', '');

        if ($username === '' || $password === '') {
            $this->view('web.auth.login', [
                'pageTitle' => 'Sign In',
                'error' => 'Please enter both username and password.',
                'old_username' => $username,
            ], 'app');
            return;
        }

        if (!Auth::attempt($username, $password)) {
            $this->view('web.auth.login', [
                'pageTitle' => 'Sign In',
                'error' => 'Invalid credentials.',
                'old_username' => $username,
            ], 'app');
            return;
        }

        Response::redirect('/');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            Response::redirect('/');
        }
        $this->view('web.auth.register', [
            'pageTitle' => 'Create Account',
        ], 'app');
    }

    public function register(): void
    {
        $this->validateCsrf();

        $validation = $this->request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
        ]);

        $email = trim((string) $this->request->post('email', ''));

        if (!empty($validation['errors'])) {
            $this->view('web.auth.register', [
                'pageTitle' => 'Create Account',
                'error' => array_values($validation['errors'])[0],
                'old_username' => $this->request->post('username', ''),
                'old_email' => $email,
            ], 'app');
            return;
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('web.auth.register', [
                'pageTitle' => 'Create Account',
                'error' => 'A valid email address is required.',
                'old_username' => $validation['data']['username'],
                'old_email' => $email,
            ], 'app');
            return;
        }

        $existing = $this->db->fetch('SELECT id FROM users WHERE username = ?', [$validation['data']['username']]);
        if ($existing) {
            $this->view('web.auth.register', [
                'pageTitle' => 'Create Account',
                'error' => 'Username already taken.',
                'old_username' => $validation['data']['username'],
                'old_email' => $email,
            ], 'app');
            return;
        }

        $emailTaken = $this->db->fetch('SELECT id FROM users WHERE email = ?', [$email]);
        if ($emailTaken) {
            $this->view('web.auth.register', [
                'pageTitle' => 'Create Account',
                'error' => 'Email already registered.',
                'old_username' => $validation['data']['username'],
                'old_email' => $email,
            ], 'app');
            return;
        }

        $this->db->insert('users', [
            'username' => $validation['data']['username'],
            'email' => $email,
            'password_hash' => password_hash($validation['data']['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => 'user',
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        Auth::attempt($validation['data']['username'], $validation['data']['password']);
        Response::redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/');
    }
}
