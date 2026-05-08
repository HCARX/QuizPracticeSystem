<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class AiSettingsController extends Controller
{
    public function index(): void
    {
        $provider = $this->db->fetch("SELECT * FROM ai_provider_config WHERE provider = 'openai'");
        $models = $this->db->fetchAll('SELECT * FROM ai_models ORDER BY sort_order');
        $prompts = $this->db->fetchAll('SELECT * FROM ai_prompts ORDER BY scene');
        $recentLogs = $this->db->fetchAll(
            "SELECT al.*, u.username FROM ai_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 20"
        );

        $logStats = $this->db->fetch(
            "SELECT COUNT(*) as total, SUM(total_tokens) as tokens, SUM(CASE WHEN status='error' THEN 1 ELSE 0 END) as errors FROM ai_logs"
        );

        $this->view('admin.ai.index', [
            'pageTitle' => 'AI Settings',
            'currentNav' => 'ai',
            'provider' => $provider,
            'models' => $models,
            'prompts' => $prompts,
            'recentLogs' => $recentLogs,
            'logStats' => $logStats,
        ], 'admin');
    }

    public function updateProvider(): void
    {
        $this->validateCsrf();

        $data = [
            'base_url' => trim($this->request->post('base_url', '')),
            'api_key_encrypted' => trim($this->request->post('api_key', '')),
            'default_model' => trim($this->request->post('default_model', 'gpt-4o')),
            'timeout' => (int) $this->request->post('timeout', '30'),
            'temperature' => (float) $this->request->post('temperature', '0.7'),
            'max_tokens' => (int) $this->request->post('max_tokens', '2000'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (empty($data['base_url'])) {
            $this->json(['error' => 'Base URL is required.'], 422);
            return;
        }

        $existing = $this->db->fetch("SELECT id FROM ai_provider_config WHERE provider = 'openai'");
        if ($existing) {
            if ($data['api_key_encrypted'] === '••••••••') {
                unset($data['api_key_encrypted']);
            }
            $this->db->update('ai_provider_config', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['provider'] = 'openai';
            $data['status'] = 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('ai_provider_config', $data);
        }

        $this->json(['success' => true, 'message' => 'Provider configuration saved.']);
    }

    public function updatePrompt(string $id): void
    {
        $this->validateCsrf();

        $data = [
            'system_prompt' => $this->request->post('system_prompt', ''),
            'user_prompt_template' => $this->request->post('user_prompt_template', ''),
            'status' => (int) $this->request->post('status', '1'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->update('ai_prompts', $data, 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'message' => 'Prompt updated.']);
    }

    public function storeModel(): void
    {
        $this->validateCsrf();
        $data = $this->modelPayload();
        if ($err = $this->validateModel($data)) { $this->json(['error' => $err], 422); return; }
        $data['created_at'] = date('Y-m-d H:i:s');
        $id = $this->db->insert('ai_models', $data);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function updateModel(string $id): void
    {
        $this->validateCsrf();
        $data = $this->modelPayload();
        if ($err = $this->validateModel($data)) { $this->json(['error' => $err], 422); return; }
        $this->db->update('ai_models', $data, 'id = ?', [(int) $id]);
        $this->json(['success' => true]);
    }

    public function toggleModel(string $id): void
    {
        $this->validateCsrf();
        $m = $this->db->fetch('SELECT status FROM ai_models WHERE id = ?', [(int) $id]);
        if (!$m) { $this->json(['error' => 'Not found'], 404); return; }
        $this->db->update('ai_models', ['status' => $m['status'] ? 0 : 1], 'id = ?', [(int) $id]);
        $this->json(['success' => true, 'status' => $m['status'] ? 0 : 1]);
    }

    public function deleteModel(string $id): void
    {
        $this->validateCsrf();
        $this->db->delete('ai_models', 'id = ?', [(int) $id]);
        $this->json(['success' => true]);
    }

    private function modelPayload(): array
    {
        $roles = trim((string) $this->request->post('allowed_roles', ''));
        if ($roles === '') {
            $roles = '["super_admin","admin"]';
        } else {
            $arr = array_values(array_filter(array_map('trim', explode(',', $roles))));
            $roles = json_encode($arr, JSON_UNESCAPED_UNICODE);
        }
        return [
            'name' => trim((string) $this->request->post('name', '')),
            'model_id' => trim((string) $this->request->post('model_id', '')),
            'provider' => trim((string) $this->request->post('provider', 'openai')) ?: 'openai',
            'description' => trim((string) $this->request->post('description', '')),
            'sort_order' => (int) $this->request->post('sort_order', '0'),
            'allowed_roles' => $roles,
        ];
    }

    private function validateModel(array $d): ?string
    {
        if ($d['name'] === '' || $d['model_id'] === '') return 'Name and Model ID are required.';
        return null;
    }
}
