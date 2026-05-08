<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;

class TemplateController extends Controller
{
    public function index(): void
    {
        $tab = $this->request->get('tab', 'blueprints');

        $blueprints = [];
        $modules = [];

        if ($tab === 'blueprints') {
            $blueprints = $this->db->fetchAll(
                "SELECT pt.*, p.title as paper_title, s.name as subject_name, u.username as creator
                 FROM paper_templates pt
                 LEFT JOIN papers p ON pt.paper_id = p.id
                 LEFT JOIN subjects s ON p.subject_id = s.id
                 LEFT JOIN users u ON pt.created_by = u.id
                 ORDER BY pt.updated_at DESC"
            );
        } else {
            $modules = $this->db->fetchAll(
                "SELECT tm.*, u.username as creator
                 FROM template_modules tm
                 LEFT JOIN users u ON tm.created_by = u.id
                 ORDER BY tm.category, tm.created_at DESC"
            );
        }

        $papers = $this->db->fetchAll(
            "SELECT p.id, p.title, s.name as subject_name
             FROM papers p LEFT JOIN subjects s ON p.subject_id = s.id
             ORDER BY p.created_at DESC"
        );

        $moduleCategories = $this->db->fetchAll(
            "SELECT category, COUNT(*) as cnt FROM template_modules GROUP BY category ORDER BY cnt DESC"
        );

        $this->view('admin.templates.index', [
            'pageTitle' => 'Templates',
            'currentNav' => 'templates',
            'tab' => $tab,
            'blueprints' => $blueprints,
            'modules' => $modules,
            'papers' => $papers,
            'moduleCategories' => $moduleCategories,
        ], 'admin');
    }

    public function editor(string $id): void
    {
        $template = $this->db->fetch(
            "SELECT pt.*, p.title as paper_title, s.name as subject_name
             FROM paper_templates pt
             LEFT JOIN papers p ON pt.paper_id = p.id
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE pt.id = ?",
            [(int) $id]
        );

        if (!$template) {
            $this->redirect('/admin/templates');
            return;
        }

        $modules = $this->db->fetchAll(
            "SELECT * FROM template_modules ORDER BY category, name"
        );

        $modulesByCategory = [];
        foreach ($modules as $m) {
            $modulesByCategory[$m['category']][] = $m;
        }

        $this->view('admin.templates.editor', [
            'pageTitle' => 'Blueprint Editor - ' . $template['name'],
            'currentNav' => 'templates',
            'template' => $template,
            'modulesByCategory' => $modulesByCategory,
        ], 'admin');
    }

    public function storeBlueprint(): void
    {
        $this->validateCsrf();

        $paperId = (int) $this->request->post('paper_id', '0');
        $name = trim($this->request->post('name', ''));

        if (!$paperId || $name === '') {
            $this->json(['error' => 'Paper and name are required.'], 422);
            return;
        }

        $paper = $this->db->fetch('SELECT id FROM papers WHERE id = ?', [$paperId]);
        if (!$paper) {
            $this->json(['error' => 'Paper not found.'], 404);
            return;
        }

        $defaultBlueprint = json_encode([
            'sections' => [
                [
                    'id' => 'section_1',
                    'title' => 'Part I',
                    'type' => 'default',
                    'blocks' => [],
                ],
            ],
        ]);

        $id = $this->db->insert('paper_templates', [
            'paper_id' => $paperId,
            'name' => $name,
            'blueprint_json' => $defaultBlueprint,
            'status' => 'draft',
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'id' => $id, 'message' => 'Blueprint created.']);
    }

    public function updateBlueprint(string $id): void
    {
        $this->validateCsrf();

        $template = $this->db->fetch('SELECT id FROM paper_templates WHERE id = ?', [(int) $id]);
        if (!$template) {
            $this->json(['error' => 'Template not found.'], 404);
            return;
        }

        $data = [];
        $blueprint = $this->request->post('blueprint_json');
        $name = $this->request->post('name');
        $status = $this->request->post('status');

        if ($blueprint !== null) {
            $decoded = json_decode($blueprint, true);
            if (!$decoded) {
                $this->json(['error' => 'Invalid blueprint JSON.'], 422);
                return;
            }
            $data['blueprint_json'] = $blueprint;
        }
        if ($name !== null) $data['name'] = trim($name);
        if ($status !== null && in_array($status, ['draft', 'active', 'archived'])) {
            $data['status'] = $status;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('paper_templates', $data, 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Blueprint saved.']);
    }

    public function deleteBlueprint(string $id): void
    {
        $this->validateCsrf();
        $this->db->delete('paper_templates', 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Blueprint deleted.']);
    }

    public function storeModule(): void
    {
        $this->validateCsrf();

        $name = trim($this->request->post('name', ''));
        $category = trim($this->request->post('category', ''));
        $content = trim($this->request->post('content', ''));

        if ($name === '' || $category === '' || $content === '') {
            $this->json(['error' => 'Name, category, and content are required.'], 422);
            return;
        }

        $id = $this->db->insert('template_modules', [
            'name' => $name,
            'category' => $category,
            'content' => $content,
            'content_format' => $this->request->post('content_format', 'text'),
            'tags' => $this->request->post('tags', '[]'),
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'id' => $id, 'message' => 'Module created.']);
    }

    public function updateModule(string $id): void
    {
        $this->validateCsrf();

        $module = $this->db->fetch('SELECT id FROM template_modules WHERE id = ?', [(int) $id]);
        if (!$module) {
            $this->json(['error' => 'Module not found.'], 404);
            return;
        }

        $data = [];
        foreach (['name', 'category', 'content', 'content_format', 'tags'] as $field) {
            $val = $this->request->post($field);
            if ($val !== null) $data[$field] = trim($val);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('template_modules', $data, 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Module updated.']);
    }

    public function deleteModule(string $id): void
    {
        $this->validateCsrf();
        $this->db->delete('template_modules', 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Module deleted.']);
    }
}
