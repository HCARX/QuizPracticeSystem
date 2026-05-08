<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;

class LogController extends Controller
{
    public function index(): void
    {
        $tab = $this->request->get('tab', 'operations');
        $page = max(1, (int) $this->request->get('page', '1'));

        $operationLogs = [];
        $aiLogs = [];
        $opPagination = null;
        $aiPagination = null;

        if ($tab === 'operations') {
            $sql = "SELECT ol.*, u.username FROM operation_logs ol LEFT JOIN users u ON ol.user_id = u.id ORDER BY ol.created_at DESC";
            $opPagination = $this->db->paginate($sql, [], $page, 30);
            $operationLogs = $opPagination['items'];
        } else {
            $sql = "SELECT al.*, u.username FROM ai_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC";
            $aiPagination = $this->db->paginate($sql, [], $page, 30);
            $aiLogs = $aiPagination['items'];
        }

        $aiStats = $this->db->fetch(
            "SELECT COUNT(*) as total, COALESCE(SUM(total_tokens),0) as tokens,
                    SUM(CASE WHEN status='error' THEN 1 ELSE 0 END) as errors,
                    COALESCE(AVG(response_time),0) as avg_time
             FROM ai_logs"
        );

        $this->view('admin.logs.index', [
            'pageTitle' => 'System Logs',
            'currentNav' => 'logs',
            'tab' => $tab,
            'operationLogs' => $operationLogs,
            'aiLogs' => $aiLogs,
            'opPagination' => $opPagination,
            'aiPagination' => $aiPagination,
            'aiStats' => $aiStats,
        ], 'admin');
    }
}
