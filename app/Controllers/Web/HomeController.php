<?php

declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Response;

class HomeController extends Controller
{
    public function index(): void
    {
        $selectedSubject = $this->request->get('subject', '');
        $search = $this->request->get('search', '');

        $subjects = $this->db->fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM papers WHERE subject_id = s.id AND status = 'published') as paper_count
             FROM subjects s WHERE s.status = 1 ORDER BY s.sort_order"
        );

        $where = "p.status = 'published'";
        $params = [];

        if ($selectedSubject !== '') {
            $where .= ' AND p.subject_id = ?';
            $params[] = (int) $selectedSubject;
        }
        if ($search !== '') {
            $where .= ' AND (p.title LIKE ? OR p.subtitle LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $papers = $this->db->fetchAll(
            "SELECT p.*, s.name as subject_name, s.cover_color as subject_color,
                    (SELECT COUNT(*) FROM questions WHERE paper_id = p.id AND parent_id IS NULL) as question_count
             FROM papers p
             LEFT JOIN subjects s ON p.subject_id = s.id
             WHERE {$where}
             ORDER BY p.year DESC, p.sort_order ASC, p.created_at DESC",
            $params
        );

        // Resume in-progress session for logged-in user
        $ongoing = null;
        $recentCompleted = [];
        if (Auth::check()) {
            $ongoing = $this->db->fetch(
                "SELECT ps.id, ps.paper_id, ps.started_at, p.title as paper_title, s.name as subject_name
                 FROM practice_sessions ps
                 JOIN papers p ON ps.paper_id = p.id
                 LEFT JOIN subjects s ON p.subject_id = s.id
                 WHERE ps.user_id = ? AND ps.status = 'ongoing'
                 ORDER BY ps.started_at DESC LIMIT 1",
                [Auth::id()]
            ) ?: null;

            $recentCompleted = $this->db->fetchAll(
                "SELECT ps.id, ps.paper_id, ps.accuracy, ps.completed_at, p.title as paper_title
                 FROM practice_sessions ps
                 JOIN papers p ON ps.paper_id = p.id
                 WHERE ps.user_id = ? AND ps.status = 'completed'
                 ORDER BY ps.completed_at DESC LIMIT 3",
                [Auth::id()]
            );
        }

        $this->view('web.home.index', [
            'pageTitle' => 'Quiz Practice System',
            'subjects' => $subjects,
            'papers' => $papers,
            'selectedSubject' => $selectedSubject,
            'search' => $search,
            'ongoing' => $ongoing,
            'recentCompleted' => $recentCompleted,
        ], 'app');
    }
}
