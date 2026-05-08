<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class QuizController extends Controller
{
    public function start(string $id): void
    {
        $paper = $this->db->fetch(
            "SELECT p.*, s.name as subject_name FROM papers p
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE p.id = ? AND p.status = 'published'",
            [(int) $id]
        );

        if (!$paper) {
            Response::error(404, 'Paper not found.');
            return;
        }

        $existing = $this->db->fetch(
            "SELECT id FROM practice_sessions WHERE user_id = ? AND paper_id = ? AND status = 'ongoing'",
            [Auth::id(), (int) $id]
        );

        if ($existing) {
            Response::redirect("/quiz/{$existing['id']}");
            return;
        }

        $questions = $this->db->fetchAll(
            'SELECT id FROM questions WHERE paper_id = ? AND parent_id IS NULL AND status = ? ORDER BY sort_order',
            [(int) $id, 'published']
        );

        $sessionId = $this->db->insert('practice_sessions', [
            'user_id' => Auth::id(),
            'paper_id' => (int) $id,
            'mode' => 'exam',
            'status' => 'ongoing',
            'max_score' => (float) $paper['total_score'],
            'unanswered_count' => count($questions),
            'answers_json' => '{}',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        Response::redirect("/quiz/{$sessionId}");
    }

    public function take(string $id): void
    {
        $session = $this->db->fetch(
            'SELECT ps.*, p.title as paper_title, p.duration, p.total_score,
                    s.name as subject_name
             FROM practice_sessions ps
             LEFT JOIN papers p ON ps.paper_id = p.id
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE ps.id = ? AND ps.user_id = ?',
            [(int) $id, Auth::id()]
        );

        if (!$session) {
            Response::error(404, 'Session not found.');
            return;
        }

        if ($session['status'] === 'completed') {
            Response::redirect("/quiz/{$id}/result");
            return;
        }

        $questions = $this->db->fetchAll(
            "SELECT * FROM questions WHERE paper_id = ? AND parent_id IS NULL AND status = 'published' ORDER BY sort_order",
            [$session['paper_id']]
        );

        // Specialized practice: restrict to selected question_ids
        if (($session['mode'] ?? '') === 'practice' && !empty($session['settings_json'])) {
            $settings = json_decode($session['settings_json'], true) ?: [];
            $qids = array_map('intval', $settings['question_ids'] ?? []);
            if (!empty($qids)) {
                $placeholders = implode(',', array_fill(0, count($qids), '?'));
                $questions = $this->db->fetchAll(
                    "SELECT * FROM questions WHERE id IN ({$placeholders}) AND status = 'published'",
                    $qids
                );
                // Preserve caller-provided order
                $order = array_flip($qids);
                usort($questions, fn($a, $b) => ($order[$a['id']] ?? 0) <=> ($order[$b['id']] ?? 0));
            }
        }

        $childMap = [];
        if ($questions) {
            $parentIds = array_column($questions, 'id');
            $placeholders = implode(',', array_fill(0, count($parentIds), '?'));
            $children = $this->db->fetchAll(
                "SELECT * FROM questions WHERE parent_id IN ({$placeholders}) ORDER BY sort_order",
                $parentIds
            );
            foreach ($children as $child) {
                $childMap[$child['parent_id']][] = $child;
            }
        }

        $answers = json_decode($session['answers_json'] ?: '{}', true);

        $this->view('web.quiz.take', [
            'pageTitle' => $session['paper_title'],
            'session' => $session,
            'questions' => $questions,
            'childMap' => $childMap,
            'answers' => $answers,
        ], 'app');
    }

    public function saveAnswer(string $id): void
    {
        $session = $this->db->fetch(
            "SELECT * FROM practice_sessions WHERE id = ? AND user_id = ? AND status = 'ongoing'",
            [(int) $id, Auth::id()]
        );

        if (!$session) {
            $this->json(['error' => 'Session not found or already completed.'], 404);
            return;
        }

        $questionId = $this->request->post('question_id');
        $answer = $this->request->post('answer');
        $timeSpent = (int) $this->request->post('time_spent', '0');

        $answers = json_decode($session['answers_json'] ?: '{}', true);
        $answers[(string) $questionId] = [
            'answer' => $answer,
            'time_spent' => $timeSpent,
            'saved_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->update('practice_sessions', [
            'answers_json' => json_encode($answers, JSON_UNESCAPED_UNICODE),
        ], 'id = ?', [(int) $id]);

        $this->json(['success' => true]);
    }

    public function submit(string $id): void
    {
        $this->validateCsrf();

        $session = $this->db->fetch(
            "SELECT * FROM practice_sessions WHERE id = ? AND user_id = ? AND status = 'ongoing'",
            [(int) $id, Auth::id()]
        );

        if (!$session) {
            $this->json(['error' => 'Session not found.'], 404);
            return;
        }

        $questions = $this->db->fetchAll(
            "SELECT * FROM questions WHERE paper_id = ? AND status = 'published' ORDER BY parent_id NULLS FIRST, sort_order",
            [$session['paper_id']]
        );

        if (($session['mode'] ?? '') === 'practice' && !empty($session['settings_json'])) {
            $settings = json_decode($session['settings_json'], true) ?: [];
            $qids = array_map('intval', $settings['question_ids'] ?? []);
            if (!empty($qids)) {
                $placeholders = implode(',', array_fill(0, count($qids), '?'));
                $questions = $this->db->fetchAll(
                    "SELECT * FROM questions WHERE id IN ({$placeholders}) AND status = 'published'",
                    $qids
                );
            }
        }

        $answers = json_decode($session['answers_json'] ?: '{}', true);

        $totalScore = 0;
        $correctCount = 0;
        $wrongCount = 0;
        $unanswered = 0;
        $totalTime = 0;
        $resultDetails = [];

        foreach ($questions as $q) {
            $qid = (string) $q['id'];
            $userAnswer = $answers[$qid]['answer'] ?? null;
            $timeOnQ = (int) ($answers[$qid]['time_spent'] ?? 0);
            $totalTime += $timeOnQ;
            $correctAnswer = $q['answer_json'];

            if ($userAnswer === null || $userAnswer === '') {
                $unanswered++;
                $isCorrect = false;
                $score = 0;
            } else {
                $isCorrect = $this->checkAnswer($q['type'], $userAnswer, $correctAnswer);
                $score = $isCorrect ? (float) $q['score'] : 0;
            }

            if ($userAnswer !== null && $userAnswer !== '') {
                if ($isCorrect) {
                    $correctCount++;
                } else {
                    $wrongCount++;
                    $this->recordMistake((int) $q['id'], $userAnswer);
                }
            }

            $totalScore += $score;

            $this->db->insert('user_answers', [
                'session_id' => (int) $id,
                'question_id' => (int) $q['id'],
                'user_answer' => is_array($userAnswer) ? json_encode($userAnswer) : (string) ($userAnswer ?? ''),
                'is_correct' => $isCorrect ? 1 : 0,
                'score' => $score,
                'time_spent' => $timeOnQ,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $resultDetails[$qid] = [
                'is_correct' => $isCorrect,
                'score' => $score,
                'correct_answer' => $correctAnswer,
            ];
        }

        $answeredCount = $correctCount + $wrongCount;
        $accuracy = $answeredCount > 0 ? round(($correctCount / $answeredCount) * 100, 1) : 0;

        $this->db->update('practice_sessions', [
            'status' => 'completed',
            'total_score' => $totalScore,
            'correct_count' => $correctCount,
            'wrong_count' => $wrongCount,
            'unanswered_count' => $unanswered,
            'accuracy' => $accuracy,
            'time_spent' => $totalTime,
            'result_json' => json_encode($resultDetails, JSON_UNESCAPED_UNICODE),
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [(int) $id]);

        $this->json(['success' => true, 'redirect' => "/quiz/{$id}/result"]);
    }

    public function result(string $id): void
    {
        $session = $this->db->fetch(
            "SELECT ps.*, p.title as paper_title, p.total_score as paper_total_score,
                    s.name as subject_name
             FROM practice_sessions ps
             LEFT JOIN papers p ON ps.paper_id = p.id
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE ps.id = ? AND ps.user_id = ?",
            [(int) $id, Auth::id()]
        );

        if (!$session) {
            Response::error(404, 'Session not found.');
            return;
        }

        $userAnswers = $this->db->fetchAll(
            'SELECT ua.*, q.type, q.content_json, q.answer_json as correct_answer, q.analysis_json, q.parent_id
             FROM user_answers ua
             LEFT JOIN questions q ON ua.question_id = q.id
             WHERE ua.session_id = ?
             ORDER BY q.sort_order',
            [(int) $id]
        );

        $this->view('web.quiz.result', [
            'pageTitle' => 'Results - ' . $session['paper_title'],
            'session' => $session,
            'userAnswers' => $userAnswers,
        ], 'app');
    }

    private function checkAnswer(string $type, mixed $userAnswer, ?string $correctAnswer): bool
    {
        if ($correctAnswer === null) return false;

        $correct = strtolower(trim($correctAnswer));
        $user = is_array($userAnswer) ? array_map(fn($a) => strtolower(trim($a)), $userAnswer) : strtolower(trim((string) $userAnswer));

        return match ($type) {
            'single_choice', 'true_false' => $user === $correct,
            'multi_choice' => is_array($user) && (sort($user) || true) && $user === (function() use ($correct) {
                $arr = array_map('trim', explode(',', $correct));
                sort($arr);
                return $arr;
            })(),
            'fill_blank' => str_contains($correct, '|')
                ? in_array($user, array_map('trim', explode('|', $correct)))
                : $user === $correct,
            default => $user === $correct,
        };
    }

    private function recordMistake(int $questionId, mixed $userAnswer): void
    {
        $existing = $this->db->fetch(
            'SELECT id, wrong_count FROM user_mistakes WHERE user_id = ? AND question_id = ?',
            [Auth::id(), $questionId]
        );

        $answerStr = is_array($userAnswer) ? json_encode($userAnswer) : (string) $userAnswer;

        if ($existing) {
            $this->db->update('user_mistakes', [
                'wrong_count' => $existing['wrong_count'] + 1,
                'last_wrong_answer' => $answerStr,
                'last_practiced_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('user_mistakes', [
                'user_id' => Auth::id(),
                'question_id' => $questionId,
                'wrong_count' => 1,
                'last_wrong_answer' => $answerStr,
                'last_practiced_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
