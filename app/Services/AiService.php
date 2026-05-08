<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

class AiService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function explainWord(string $word, string $sentence, int $userId): array
    {
        $prompt = $this->db->fetch("SELECT * FROM ai_prompts WHERE scene = 'word_explain' AND status = 1");
        if (!$prompt) {
            return ['error' => 'Word explanation prompt not configured.'];
        }

        $userPrompt = $prompt['user_prompt_template'] ?? '';
        $userPrompt = str_replace(['{{word}}', '{{sentence}}'], [$word, $sentence], $userPrompt);

        $config = $this->getProviderConfig();
        if (!$config || empty($config['api_key_encrypted'])) {
            return ['error' => 'AI provider not configured. Set API key in admin panel.'];
        }

        $model = $this->resolveModel($prompt['default_model_id'], $userId);
        $result = $this->callApi($config, $model, $prompt['system_prompt'] ?? '', $userPrompt, $userId, 'word_explain');

        return $result;
    }

    public function analyzeQuestion(int $questionId, int $userId): array
    {
        $question = $this->db->fetch('SELECT * FROM questions WHERE id = ?', [$questionId]);
        if (!$question) {
            return ['error' => 'Question not found.'];
        }

        $prompt = $this->db->fetch("SELECT * FROM ai_prompts WHERE scene = 'question_analysis' AND status = 1");
        if (!$prompt) {
            return ['error' => 'Analysis prompt not configured.'];
        }

        $content = json_decode($question['content_json'], true) ?: [];
        $stem = $content['stem'] ?? $content['passage'] ?? $content['instructions'] ?? '';
        $options = '';
        if (!empty($content['options'])) {
            foreach ($content['options'] as $k => $v) {
                $options .= "{$k}. {$v}\n";
            }
        }

        $typeName = ucwords(str_replace('_', ' ', $question['type']));
        $answer = $question['answer_json'] ?? '';

        $userPrompt = $prompt['user_prompt_template'] ?? '';
        $userPrompt = str_replace(
            ['{{question_type}}', '{{stem}}', '{{options}}', '{{answer}}'],
            [$typeName, $stem, $options, $answer],
            $userPrompt
        );
        $userPrompt = preg_replace('/\{\{#options\}\}(.*?)\{\{\/options\}\}/s', $options ? '$1' : '', $userPrompt);

        $config = $this->getProviderConfig();
        if (!$config || empty($config['api_key_encrypted'])) {
            return ['error' => 'AI provider not configured.'];
        }

        $model = $this->resolveModel($prompt['default_model_id'], $userId);
        return $this->callApi($config, $model, $prompt['system_prompt'] ?? '', $userPrompt, $userId, 'question_analysis');
    }

    private function getProviderConfig(): ?array
    {
        return $this->db->fetch("SELECT * FROM ai_provider_config WHERE status = 1 LIMIT 1");
    }

    private function resolveModel(?int $modelId, int $userId): string
    {
        if ($modelId) {
            $model = $this->db->fetch('SELECT model_id, allowed_roles FROM ai_models WHERE id = ? AND status = 1', [$modelId]);
            if ($model) {
                return $model['model_id'];
            }
        }

        $config = $this->getProviderConfig();
        return $config['default_model'] ?? 'gpt-4o-mini';
    }

    private function callApi(array $config, string $model, string $systemPrompt, string $userPrompt, int $userId, string $scene): array
    {
        $url = rtrim($config['base_url'], '/') . '/chat/completions';
        $apiKey = $config['api_key_encrypted'] ?? '';

        $payload = [
            'model' => $model,
            'messages' => [],
            'temperature' => (float) ($config['temperature'] ?? 0.7),
            'max_tokens' => (int) ($config['max_tokens'] ?? 2000),
        ];

        if ($systemPrompt) {
            $payload['messages'][] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $payload['messages'][] = ['role' => 'user', 'content' => $userPrompt];

        $startTime = microtime(true);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => (int) ($config['timeout'] ?? 30),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $responseTime = (int) ((microtime(true) - $startTime) * 1000);

        if ($error) {
            $this->logCall($userId, $scene, $model, 0, 0, $responseTime, 'error', "cURL error: {$error}");
            return ['error' => 'Failed to connect to AI service.'];
        }

        $data = json_decode($response, true);

        if ($httpCode !== 200 || !isset($data['choices'][0]['message']['content'])) {
            $errMsg = $data['error']['message'] ?? "HTTP {$httpCode}";
            $this->logCall($userId, $scene, $model, 0, 0, $responseTime, 'error', $errMsg);
            return ['error' => "AI request failed: {$errMsg}"];
        }

        $content = $data['choices'][0]['message']['content'];
        $promptTokens = $data['usage']['prompt_tokens'] ?? 0;
        $completionTokens = $data['usage']['completion_tokens'] ?? 0;

        $this->logCall($userId, $scene, $model, $promptTokens, $completionTokens, $responseTime, 'success');

        return ['analysis' => $content, 'model' => $model, 'tokens' => $promptTokens + $completionTokens];
    }

    private function logCall(int $userId, string $scene, string $model, int $promptTokens, int $completionTokens, int $responseTime, string $status, ?string $error = null): void
    {
        $this->db->insert('ai_logs', [
            'user_id' => $userId,
            'scene' => $scene,
            'model' => $model,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'response_time' => $responseTime,
            'status' => $status,
            'error_message' => $error,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
