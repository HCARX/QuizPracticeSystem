<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class SubjectController extends Controller
{
    public function index(): void
    {
        $search = $this->request->get('search', '');
        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= ' AND (name LIKE ? OR alias LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $subjects = $this->db->fetchAll(
            "SELECT s.*,
                    (SELECT COUNT(*) FROM papers WHERE subject_id = s.id) as paper_count
             FROM subjects s
             WHERE {$where}
             ORDER BY s.sort_order ASC, s.id ASC",
            $params
        );

        $this->view('admin.subjects.index', [
            'pageTitle' => 'Subjects',
            'currentNav' => 'subjects',
            'subjects' => $subjects,
            'search' => $search,
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $validation = $this->request->validate([
            'name' => 'required|string|min:1|max:100',
            'alias' => 'string|max:50',
            'description' => 'string|max:500',
            'cover_color' => 'string|max:20',
            'sort_order' => 'integer',
        ]);

        if (!empty($validation['errors'])) {
            $this->json(['error' => array_values($validation['errors'])[0]], 422);
            return;
        }

        $data = $validation['data'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['cover_color'] = $data['cover_color'] ?? '#4F46E5';
        $data['status'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = $this->db->insert('subjects', $data);

        $this->json(['success' => true, 'id' => $id, 'message' => 'Subject created successfully.']);
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $subject = $this->db->fetch('SELECT * FROM subjects WHERE id = ?', [(int) $id]);
        if (!$subject) {
            $this->json(['error' => 'Subject not found.'], 404);
            return;
        }

        $validation = $this->request->validate([
            'name' => 'required|string|min:1|max:100',
            'alias' => 'string|max:50',
            'description' => 'string|max:500',
            'cover_color' => 'string|max:20',
            'sort_order' => 'integer',
        ]);

        if (!empty($validation['errors'])) {
            $this->json(['error' => array_values($validation['errors'])[0]], 422);
            return;
        }

        $data = $validation['data'];
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('subjects', $data, 'id = ?', [(int) $id]);

        $this->json(['success' => true, 'message' => 'Subject updated successfully.']);
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        $paperCount = $this->db->count('papers', 'subject_id = ?', [(int) $id]);
        if ($paperCount > 0) {
            $this->json(['error' => "Cannot delete: {$paperCount} papers are linked to this subject."], 422);
            return;
        }

        $this->db->delete('subjects', 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Subject deleted successfully.']);
    }

    public function toggle(string $id): void
    {
        $this->validateCsrf();

        $subject = $this->db->fetch('SELECT status FROM subjects WHERE id = ?', [(int) $id]);
        if (!$subject) {
            $this->json(['error' => 'Subject not found.'], 404);
            return;
        }

        $newStatus = $subject['status'] ? 0 : 1;
        $this->db->update('subjects', [
            'status' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);

        $this->json(['success' => true, 'status' => $newStatus]);
    }
}
