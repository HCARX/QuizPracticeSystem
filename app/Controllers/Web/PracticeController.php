<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class PracticeController extends Controller
{
    /**
     * Specialized practice setup page.
     */
    public function setup(): void
    {
        $subjects = $this->db->fetchAll(
            'SELECT id, name, alias FROM subjects WHERE status = 1 ORDER BY sort_order'
        );

        $types = [
            'single_choice'   => 'Single Choice',
            'multi_choice'    => 'Multi Choice',
            'fill_blank'      => 'Fill in Blank',
            'true_false'      => 'True / False',
            'translation'     => 'Translation',
            'writing'         => 'Writing',
        ];

        $this->view('web.practice.setup', [
            'pageTitle' => 'Specialized Practice',
            'subjects'  => $subjects,
            'types'     => $types,
        ], 'app');
    }

    /**
     * Create a specialized practice session based on filters.
     */
    public function start(): void
    {
        $this->validateCsrf();

        $subjectId  = (int) $this->request->post('subject_id', '0');
        $rawTypes   = $this->request->post('types', []);
        $types      = is_array($rawTypes) ? array_values(array_filter($rawTypes, fn($t) => is_string($t) && $t !== '')) : [];
        $difficulty = (int) $this->request->post('difficulty', '0');
        $count      = max(1, min(100, (int) $this->request->post('count', '10')));
        $source     = (string) $this->request->post('source', 'all'); // all | mistakes | favorites
        $shuffle    = (string) $this->request->post('shuffle', '1') === '1';

        $where  = "q.status = 'published' AND q.parent_id IS NULL";
        $params = [];

        if ($subjectId > 0) {
            $where .= ' AND p.subject_id = ?';
            $params[] = $subjectId;
        }
        if (!empty($types)) {
            $placeholders = implode(',', array_fill(0, count($types), '?'));
            $where .= " AND q.type IN ({$placeholders})";
            foreach ($types as $t) $params[] = $t;
        }
        if ($difficulty > 0) {
            $where .= ' AND q.difficulty = ?';
            $params[] = $difficulty;
        }

        if ($source === 'mistakes') {
            $where .= ' AND q.id IN (SELECT question_id FROM user_mistakes WHERE user_id = ?)';
            $params[] = Auth::id();
        } elseif ($source === 'favorites') {
            $where .= ' AND q.id IN (SELECT question_id FROM user_favorites WHERE user_id = ?)';
            $params[] = Auth::id();
        }

        $sql = "SELECT q.id, q.paper_id, q.score FROM questions q
                JOIN papers p ON q.paper_id = p.id
                WHERE {$where}
                ORDER BY " . ($shuffle ? 'RANDOM()' : 'q.id') . "
                LIMIT ?";
        $params[] = $count;

        $rows = $this->db->fetchAll($sql, $params);

        if (empty($rows)) {
            $_SESSION['flash_error'] = 'No questions match your filters. Adjust and try again.';
            Response::redirect('/practice');
            return;
        }

        $questionIds = array_map(fn($r) => (int) $r['id'], $rows);
        $maxScore    = array_sum(array_map(fn($r) => (float) $r['score'], $rows));
        $paperId     = (int) $rows[0]['paper_id']; // representative paper; view loads via settings

        $sessionId = $this->db->insert('practice_sessions', [
            'user_id'           => Auth::id(),
            'paper_id'          => $paperId,
            'mode'              => 'practice',
            'status'            => 'ongoing',
            'max_score'         => $maxScore,
            'unanswered_count'  => count($questionIds),
            'answers_json'      => '{}',
            'settings_json'     => json_encode([
                'question_ids' => $questionIds,
                'filters'      => [
                    'subject_id' => $subjectId,
                    'types'      => $types,
                    'difficulty' => $difficulty,
                    'source'     => $source,
                    'shuffle'    => $shuffle,
                ],
            ], JSON_UNESCAPED_UNICODE),
            'started_at'        => date('Y-m-d H:i:s'),
        ]);

        Response::redirect("/quiz/{$sessionId}");
    }
}
