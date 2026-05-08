<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

class DashboardController extends Controller
{
    public function index(): void
    {
        $stats = [
            'subjects' => $this->db->count('subjects'),
            'papers' => $this->db->count('papers'),
            'questions' => $this->db->count('questions'),
            'users' => $this->db->count('users'),
            'published_papers' => $this->db->count('papers', "status = 'published'"),
            'practice_sessions' => $this->db->count('practice_sessions'),
        ];

        $recentPapers = $this->db->fetchAll(
            "SELECT p.*, s.name as subject_name FROM papers p
             LEFT JOIN subjects s ON p.subject_id = s.id
             ORDER BY p.updated_at DESC LIMIT 5"
        );

        $this->view('admin.dashboard', [
            'pageTitle' => 'Dashboard',
            'currentNav' => 'dashboard',
            'stats' => $stats,
            'recentPapers' => $recentPapers,
        ], 'admin');
    }
}
