<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class PaperController extends Controller
{
    public function index(): void
    {
        $subjectId = $this->request->get('subject_id', '');
        $status = $this->request->get('status', '');
        $search = $this->request->get('search', '');
        $page = max(1, (int) $this->request->get('page', '1'));
        $perPage = 15;

        $where = '1=1';
        $params = [];

        if ($subjectId !== '') {
            $where .= ' AND p.subject_id = ?';
            $params[] = (int) $subjectId;
        }
        if ($status !== '') {
            $where .= ' AND p.status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= ' AND (p.title LIKE ? OR p.subtitle LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = "SELECT p.*, s.name as subject_name, s.cover_color as subject_color
                FROM papers p
                LEFT JOIN subjects s ON p.subject_id = s.id
                WHERE {$where}
                ORDER BY p.sort_order ASC, p.updated_at DESC";

        $result = $this->db->paginate($sql, $params, $page, $perPage);
        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');

        $this->view('admin.papers.index', [
            'pageTitle' => 'Papers',
            'currentNav' => 'papers',
            'papers' => $result['items'],
            'pagination' => $result,
            'subjects' => $subjects,
            'filters' => ['subject_id' => $subjectId, 'status' => $status, 'search' => $search],
        ], 'admin');
    }

    public function create(): void
    {
        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');

        $this->view('admin.papers.form', [
            'pageTitle' => 'Create Paper',
            'currentNav' => 'papers',
            'subjects' => $subjects,
            'paper' => null,
        ], 'admin');
    }

    public function store(): void
    {
        $this->validateCsrf();

        $validation = $this->request->validate([
            'subject_id' => 'required|integer',
            'title' => 'required|string|min:1|max:200',
            'subtitle' => 'string|max:200',
            'year' => 'string|max:10',
            'month' => 'string|max:10',
            'difficulty' => 'integer',
            'duration' => 'integer',
            'total_score' => 'string',
            'description' => 'string|max:2000',
        ]);

        if (!empty($validation['errors'])) {
            $this->json(['error' => array_values($validation['errors'])[0]], 422);
            return;
        }

        $data = $validation['data'];
        $data['subject_id'] = (int) $data['subject_id'];
        $data['difficulty'] = (int) ($data['difficulty'] ?? 3);
        $data['duration'] = (int) ($data['duration'] ?? 120);
        $data['total_score'] = (float) ($data['total_score'] ?? 0);
        $data['status'] = 'draft';
        $data['created_by'] = \App\Core\Auth::id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $id = $this->db->insert('papers', $data);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'id' => $id, 'message' => 'Paper created.']);
        } else {
            $_SESSION['flash_success'] = 'Paper created successfully.';
            Response::redirect('/admin/papers');
        }
    }

    public function edit(string $id): void
    {
        $paper = $this->db->fetch('SELECT * FROM papers WHERE id = ?', [(int) $id]);
        if (!$paper) {
            Response::error(404, 'Paper not found.');
            return;
        }

        $subjects = $this->db->fetchAll('SELECT id, name FROM subjects WHERE status = 1 ORDER BY sort_order');

        $this->view('admin.papers.form', [
            'pageTitle' => 'Edit Paper',
            'currentNav' => 'papers',
            'subjects' => $subjects,
            'paper' => $paper,
        ], 'admin');
    }

    public function update(string $id): void
    {
        $this->validateCsrf();

        $paper = $this->db->fetch('SELECT * FROM papers WHERE id = ?', [(int) $id]);
        if (!$paper) {
            $this->json(['error' => 'Paper not found.'], 404);
            return;
        }

        $validation = $this->request->validate([
            'subject_id' => 'required|integer',
            'title' => 'required|string|min:1|max:200',
            'subtitle' => 'string|max:200',
            'year' => 'string|max:10',
            'month' => 'string|max:10',
            'difficulty' => 'integer',
            'duration' => 'integer',
            'total_score' => 'string',
            'description' => 'string|max:2000',
        ]);

        if (!empty($validation['errors'])) {
            $this->json(['error' => array_values($validation['errors'])[0]], 422);
            return;
        }

        $data = $validation['data'];
        $data['subject_id'] = (int) $data['subject_id'];
        $data['difficulty'] = (int) ($data['difficulty'] ?? 3);
        $data['duration'] = (int) ($data['duration'] ?? 120);
        $data['total_score'] = (float) ($data['total_score'] ?? 0);
        $data['updated_by'] = \App\Core\Auth::id();
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('papers', $data, 'id = ?', [(int) $id]);

        if ($this->request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Paper updated.']);
        } else {
            $_SESSION['flash_success'] = 'Paper updated successfully.';
            Response::redirect('/admin/papers');
        }
    }

    public function destroy(string $id): void
    {
        $this->validateCsrf();

        $questionCount = $this->db->count('questions', 'paper_id = ?', [(int) $id]);
        if ($questionCount > 0) {
            $this->json(['error' => "Cannot delete: {$questionCount} questions exist in this paper."], 422);
            return;
        }

        $this->db->delete('papers', 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Paper deleted.']);
    }

    public function updateStatus(string $id): void
    {
        $this->validateCsrf();

        $status = $this->request->post('status', '');
        if (!in_array($status, ['draft', 'published', 'archived'])) {
            $this->json(['error' => 'Invalid status.'], 422);
            return;
        }

        $data = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($status === 'published') {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $this->db->update('papers', $data, 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => "Paper status updated to {$status}."]);
    }
}
