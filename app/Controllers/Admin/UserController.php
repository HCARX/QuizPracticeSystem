<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class UserController extends Controller
{
    public function index(): void
    {
        $role = $this->request->get('role', '');
        $search = $this->request->get('search', '');
        $page = max(1, (int) $this->request->get('page', '1'));

        $where = '1=1';
        $params = [];

        if ($role !== '') {
            $where .= ' AND role = ?';
            $params[] = $role;
        }
        if ($search !== '') {
            $where .= ' AND (username LIKE ? OR email LIKE ? OR display_name LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = "SELECT u.*,
                (SELECT COUNT(*) FROM practice_sessions WHERE user_id = u.id) as session_count,
                (SELECT COUNT(*) FROM practice_sessions WHERE user_id = u.id AND status='completed') as completed_count
                FROM users u WHERE {$where} ORDER BY u.created_at DESC";

        $result = $this->db->paginate($sql, $params, $page, 20);

        $roleCounts = $this->db->fetchAll(
            "SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY cnt DESC"
        );

        $this->view('admin.users.index', [
            'pageTitle' => 'Users',
            'currentNav' => 'users',
            'users' => $result['items'],
            'pagination' => $result,
            'roleCounts' => $roleCounts,
            'filters' => compact('role', 'search'),
            'roles' => ['super_admin', 'admin', 'editor', 'reviewer', 'vip', 'user'],
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $validation = $this->request->validate([
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6',
            'email' => 'string|max:100',
            'display_name' => 'string|max:100',
            'role' => 'required|string|in:super_admin,admin,editor,reviewer,vip,user',
        ]);

        if (!empty($validation['errors'])) {
            $this->json(['error' => array_values($validation['errors'])[0]], 422);
            return;
        }

        $existing = $this->db->fetch('SELECT id FROM users WHERE username = ?', [$validation['data']['username']]);
        if ($existing) {
            $this->json(['error' => 'Username already taken.'], 422);
            return;
        }

        $data = $validation['data'];
        $password = $data['password'];
        unset($data['password']);
        $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $data['status'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = $this->db->insert('users', $data);
        $this->json(['success' => true, 'id' => $id, 'message' => 'User created.']);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [(int) $id]);
        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
            return;
        }

        if ($user['role'] === 'super_admin' && !Auth::isSuperAdmin()) {
            $this->json(['error' => 'Cannot modify super admin.'], 403);
            return;
        }

        $data = [];
        $email = $this->request->post('email');
        $displayName = $this->request->post('display_name');
        $role = $this->request->post('role');
        $password = $this->request->post('password');

        if ($email !== null) $data['email'] = trim($email);
        if ($displayName !== null) $data['display_name'] = trim($displayName);
        if ($role !== null && in_array($role, ['super_admin','admin','editor','reviewer','vip','user'])) {
            $data['role'] = $role;
        }
        if ($password !== null && $password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('users', $data, 'id = ?', [(int) $id]);

        $this->json(['success' => true, 'message' => 'User updated.']);
    }

    public function toggleStatus(string $id): void
    {
        $this->validateCsrf();

        $user = $this->db->fetch('SELECT id, status, role FROM users WHERE id = ?', [(int) $id]);
        if (!$user) {
            $this->json(['error' => 'User not found.'], 404);
            return;
        }
        if ((int) $id === Auth::id()) {
            $this->json(['error' => 'Cannot disable your own account.'], 422);
            return;
        }

        $newStatus = $user['status'] ? 0 : 1;
        $this->db->update('users', ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'status' => $newStatus]);
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        if ((int) $id === Auth::id()) {
            $this->json(['error' => 'Cannot delete your own account.'], 422);
            return;
        }

        $user = $this->db->fetch('SELECT role FROM users WHERE id = ?', [(int) $id]);
        if ($user && $user['role'] === 'super_admin') {
            $this->json(['error' => 'Cannot delete super admin.'], 422);
            return;
        }

        $this->db->delete('user_answers', "session_id IN (SELECT id FROM practice_sessions WHERE user_id = ?)", [(int) $id]);
        $this->db->delete('practice_sessions', 'user_id = ?', [(int) $id]);
        $this->db->delete('user_favorites', 'user_id = ?', [(int) $id]);
        $this->db->delete('user_mistakes', 'user_id = ?', [(int) $id]);
        $this->db->delete('user_vocabularies', 'user_id = ?', [(int) $id]);
        $this->db->delete('users', 'id = ?', [(int) $id]);

        $this->json(['success' => true, 'message' => 'User deleted.']);
    }
}
