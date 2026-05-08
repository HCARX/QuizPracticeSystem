<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Auth;
use App\Services\AiService;

class AiController extends Controller
{
    public function explainWord(): void
    {
        $word = trim($this->request->post('word', ''));
        $sentence = trim($this->request->post('sentence', ''));

        if ($word === '') {
            $this->json(['error' => 'Word is required.'], 422);
            return;
        }

        $service = new AiService();
        $result = $service->explainWord($word, $sentence, Auth::id());

        if (isset($result['error'])) {
            $this->json($result, 500);
            return;
        }

        $this->json($result);
    }

    public function analyzeQuestion(): void
    {
        $questionId = (int) $this->request->post('question_id', '0');

        if (!$questionId) {
            $this->json(['error' => 'Question ID is required.'], 422);
            return;
        }

        $service = new AiService();
        $result = $service->analyzeQuestion($questionId, Auth::id());

        if (isset($result['error'])) {
            $this->json($result, 500);
            return;
        }

        $this->json($result);
    }
}
