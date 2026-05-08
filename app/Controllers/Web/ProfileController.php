<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class ProfileController extends Controller
{
    public function index(): void
    {
        $userId = Auth::id();

        $totalSessions = $this->db->count('practice_sessions', 'user_id = ?', [$userId]);
        $completedSessions = $this->db->count('practice_sessions', "user_id = ? AND status = 'completed'", [$userId]);

        $avgAccuracy = $this->db->fetch(
            "SELECT COALESCE(AVG(accuracy), 0) as avg_acc FROM practice_sessions WHERE user_id = ? AND status = 'completed'",
            [$userId]
        );

        $totalTime = $this->db->fetch(
            'SELECT COALESCE(SUM(time_spent), 0) as total FROM practice_sessions WHERE user_id = ?',
            [$userId]
        );

        $totalQuestions = $this->db->fetch(
            'SELECT COUNT(*) as cnt FROM user_answers ua JOIN practice_sessions ps ON ua.session_id = ps.id WHERE ps.user_id = ?',
            [$userId]
        );

        $mistakeCount = $this->db->count('user_mistakes', 'user_id = ?', [$userId]);
        $vocabCount = $this->db->count('user_vocabularies', 'user_id = ?', [$userId]);
        $favoriteCount = $this->db->count('user_favorites', 'user_id = ?', [$userId]);

        $recentSessions = $this->db->fetchAll(
            "SELECT ps.*, p.title as paper_title, s.name as subject_name
             FROM practice_sessions ps
             LEFT JOIN papers p ON ps.paper_id = p.id
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE ps.user_id = ?
             ORDER BY ps.started_at DESC LIMIT 10",
            [$userId]
        );

        $trendRows = $this->db->fetchAll(
            "SELECT accuracy, completed_at FROM practice_sessions
             WHERE user_id = ? AND status = 'completed' AND completed_at IS NOT NULL
             ORDER BY completed_at DESC LIMIT 14",
            [$userId]
        );
        $trend = array_reverse(array_map(fn($r) => (float) $r['accuracy'], $trendRows));

        $typeStats = $this->db->fetchAll(
            "SELECT q.type, COUNT(*) as total, SUM(ua.is_correct) as correct
             FROM user_answers ua
             JOIN questions q ON ua.question_id = q.id
             JOIN practice_sessions ps ON ua.session_id = ps.id
             WHERE ps.user_id = ?
             GROUP BY q.type",
            [$userId]
        );

        $this->view('web.profile.index', [
            'pageTitle' => 'My Progress',
            'stats' => [
                'total_sessions' => $totalSessions,
                'completed' => $completedSessions,
                'avg_accuracy' => round((float) ($avgAccuracy['avg_acc'] ?? 0), 1),
                'total_time' => (int) ($totalTime['total'] ?? 0),
                'total_questions' => (int) ($totalQuestions['cnt'] ?? 0),
                'mistakes' => $mistakeCount,
                'vocab' => $vocabCount,
                'favorites' => $favoriteCount,
            ],
            'recentSessions' => $recentSessions,
            'typeStats' => $typeStats,
            'trend' => $trend,
        ], 'app');
    }

    public function vocabulary(): void
    {
        $userId = Auth::id();
        $status = $this->request->get('status', '');
        $search = $this->request->get('search', '');

        $where = 'user_id = ?';
        $params = [$userId];

        if ($status !== '') {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        if ($search !== '') {
            $where .= ' AND word LIKE ?';
            $params[] = "%{$search}%";
        }

        $words = $this->db->fetchAll(
            "SELECT * FROM user_vocabularies WHERE {$where} ORDER BY created_at DESC",
            $params
        );

        $counts = [
            'all' => $this->db->count('user_vocabularies', 'user_id = ?', [$userId]),
            'unseen' => $this->db->count('user_vocabularies', "user_id = ? AND status = 'unseen'", [$userId]),
            'fuzzy' => $this->db->count('user_vocabularies', "user_id = ? AND status = 'fuzzy'", [$userId]),
            'mastered' => $this->db->count('user_vocabularies', "user_id = ? AND status = 'mastered'", [$userId]),
        ];

        $this->view('web.profile.vocabulary', [
            'pageTitle' => 'Vocabulary',
            'words' => $words,
            'counts' => $counts,
            'filters' => compact('status', 'search'),
        ], 'app');
    }

    public function addWord(): void
    {
        $this->validateCsrf();

        $word = trim($this->request->post('word', ''));
        $sentence = trim($this->request->post('sentence', ''));
        $source = trim($this->request->post('source', ''));
        $meaning = trim($this->request->post('meaning', ''));

        if ($word === '') {
            $this->json(['error' => 'Word is required.'], 422);
            return;
        }

        $existing = $this->db->fetch(
            'SELECT id FROM user_vocabularies WHERE user_id = ? AND word = ?',
            [Auth::id(), $word]
        );

        if ($existing) {
            $this->json(['error' => 'Word already in vocabulary.'], 422);
            return;
        }

        $id = $this->db->insert('user_vocabularies', [
            'user_id' => Auth::id(),
            'word' => $word,
            'context_sentence' => $sentence,
            'context_source' => $source,
            'meaning_json' => $meaning ? json_encode(['definition' => $meaning]) : null,
            'status' => 'unseen',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'id' => $id]);
    }

    public function updateWord(string $id): void
    {
        $this->validateCsrf();

        $status = $this->request->post('status', '');
        if (!in_array($status, ['unseen', 'fuzzy', 'mastered'])) {
            $this->json(['error' => 'Invalid status.'], 422);
            return;
        }

        $this->db->update('user_vocabularies', [
            'status' => $status,
            'review_count' => (int) $this->db->fetch('SELECT review_count FROM user_vocabularies WHERE id = ?', [(int) $id])['review_count'] + 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND user_id = ?', [(int) $id, Auth::id()]);

        $this->json(['success' => true]);
    }

    public function deleteWord(string $id): void
    {
        $this->validateCsrf();
        $this->db->delete('user_vocabularies', 'id = ? AND user_id = ?', [(int) $id, Auth::id()]);
        $this->json(['success' => true]);
    }

    public function mistakes(): void
    {
        $userId = Auth::id();

        $mistakes = $this->db->fetchAll(
            "SELECT um.*, q.content_json, q.answer_json, q.type, q.analysis_json,
                    p.title as paper_title
             FROM user_mistakes um
             JOIN questions q ON um.question_id = q.id
             LEFT JOIN papers p ON q.paper_id = p.id
             WHERE um.user_id = ? AND um.mastered = 0
             ORDER BY um.wrong_count DESC, um.last_practiced_at DESC",
            [$userId]
        );

        $this->view('web.profile.mistakes', [
            'pageTitle' => 'Mistake Book',
            'mistakes' => $mistakes,
        ], 'app');
    }

    public function favorites(): void
    {
        $userId = Auth::id();

        $favorites = $this->db->fetchAll(
            "SELECT uf.*, q.content_json, q.answer_json, q.type, q.analysis_json,
                    p.title as paper_title
             FROM user_favorites uf
             JOIN questions q ON uf.question_id = q.id
             LEFT JOIN papers p ON q.paper_id = p.id
             WHERE uf.user_id = ?
             ORDER BY uf.created_at DESC",
            [$userId]
        );

        $this->view('web.profile.favorites', [
            'pageTitle' => 'Favorites',
            'favorites' => $favorites,
        ], 'app');
    }

    public function toggleFavorite(): void
    {
        $this->validateCsrf();

        $questionId = (int) $this->request->post('question_id', '0');
        if (!$questionId) {
            $this->json(['error' => 'Question ID required.'], 422);
            return;
        }

        $existing = $this->db->fetch(
            'SELECT id FROM user_favorites WHERE user_id = ? AND question_id = ?',
            [Auth::id(), $questionId]
        );

        if ($existing) {
            $this->db->delete('user_favorites', 'id = ?', [$existing['id']]);
            $this->json(['success' => true, 'favorited' => false]);
        } else {
            $this->db->insert('user_favorites', [
                'user_id' => Auth::id(),
                'question_id' => $questionId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->json(['success' => true, 'favorited' => true]);
        }
    }

    public function toggleMistakeMastered(string $id): void
    {
        $this->validateCsrf();
        $row = $this->db->fetch('SELECT id, mastered FROM user_mistakes WHERE id = ? AND user_id = ?', [(int) $id, Auth::id()]);
        if (!$row) {
            $this->json(['error' => 'Not found.'], 404);
            return;
        }
        $newVal = (int) $row['mastered'] ? 0 : 1;
        $this->db->update('user_mistakes', ['mastered' => $newVal, 'last_practiced_at' => date('Y-m-d H:i:s')], 'id = ?', [$row['id']]);
        $this->json(['success' => true, 'mastered' => (bool) $newVal]);
    }

    public function settings(): void
    {
        $user = $this->db->fetch('SELECT * FROM users WHERE id = ?', [Auth::id()]);
        $this->view('web.profile.settings', [
            'pageTitle' => 'Settings',
            'user' => $user,
        ], 'app');
    }

    public function updateSettings(): void
    {
        $this->validateCsrf();

        $displayName = trim((string) $this->request->post('display_name', ''));
        $email = trim((string) $this->request->post('email', ''));
        $avatar = trim((string) $this->request->post('avatar', ''));

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Invalid email address.';
            $this->redirect('/settings');
            return;
        }

        if ($email !== '') {
            $exists = $this->db->fetch('SELECT id FROM users WHERE email = ? AND id != ?', [$email, Auth::id()]);
            if ($exists) {
                $_SESSION['flash_error'] = 'Email already in use.';
                $this->redirect('/settings');
                return;
            }
        }

        if ($avatar !== '' && mb_strlen($avatar) > 8) {
            $_SESSION['flash_error'] = 'Avatar must be a single emoji.';
            $this->redirect('/settings');
            return;
        }

        $this->db->update('users', [
            'display_name' => $displayName !== '' ? $displayName : null,
            'email' => $email !== '' ? $email : null,
            'avatar' => $avatar !== '' ? $avatar : null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [Auth::id()]);

        Auth::refresh();
        $_SESSION['flash_success'] = 'Profile updated successfully.';
        $this->redirect('/settings');
    }

    public function updatePassword(): void
    {
        $this->validateCsrf();

        $current = (string) $this->request->post('current_password', '');
        $new = (string) $this->request->post('new_password', '');
        $confirm = (string) $this->request->post('confirm_password', '');

        if (strlen($new) < 6) {
            $_SESSION['flash_error'] = 'New password must be at least 6 characters.';
            $this->redirect('/settings');
            return;
        }
        if ($new !== $confirm) {
            $_SESSION['flash_error'] = 'Password confirmation does not match.';
            $this->redirect('/settings');
            return;
        }

        $user = $this->db->fetch('SELECT password_hash FROM users WHERE id = ?', [Auth::id()]);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Current password is incorrect.';
            $this->redirect('/settings');
            return;
        }

        $this->db->update('users', [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [Auth::id()]);

        $_SESSION['flash_success'] = 'Password updated successfully.';
        $this->redirect('/settings');
    }
}
