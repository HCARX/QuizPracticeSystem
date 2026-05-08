<!-- System Logs -->
<div class="space-y-6">
    <p class="text-sm text-slate-500"><?= $t('admin.logs.manage_desc') ?></p>

    <!-- AI Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-slate-900"><?= number_format((int)($aiStats['total'] ?? 0)) ?></p>
            <p class="text-xs text-slate-500 mt-1"><?= $t('admin.logs.stat_total_ai') ?></p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-primary-600"><?= number_format((int)($aiStats['tokens'] ?? 0)) ?></p>
            <p class="text-xs text-slate-500 mt-1"><?= $t('admin.logs.stat_total_tokens') ?></p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-red-600"><?= (int)($aiStats['errors'] ?? 0) ?></p>
            <p class="text-xs text-slate-500 mt-1"><?= $t('admin.logs.stat_errors') ?></p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-amber-600"><?= number_format((float)($aiStats['avg_time'] ?? 0), 0) ?><span class="text-sm font-normal text-slate-400">ms</span></p>
            <p class="text-xs text-slate-500 mt-1"><?= $t('admin.logs.stat_avg_time') ?></p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-slate-200">
        <a href="?tab=operations" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $tab === 'operations' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' ?>"><?= $t('admin.logs.tab_operations') ?></a>
        <a href="?tab=ai" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $tab === 'ai' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' ?>"><?= $t('admin.logs.tab_ai') ?></a>
    </div>

    <?php if ($tab === 'operations'): ?>
    <!-- Operation Logs -->
    <?php if (empty($operationLogs)): ?>
    <div class="card p-12 text-center">
        <p class="text-sm text-slate-400"><?= $t('admin.logs.ops_empty') ?></p>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_time') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_user') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_action') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_target') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_details') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_ip') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($operationLogs as $log):
                    $actionColors = ['create'=>'badge-success','update'=>'badge-info','delete'=>'badge-danger','login'=>'bg-purple-50 text-purple-700','toggle'=>'badge-warning'];
                ?>
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?= date('m-d H:i:s', strtotime($log['created_at'])) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($log['username'] ?? '—') ?></td>
                    <td class="px-4 py-3"><span class="badge <?= $actionColors[$log['action'] ?? ''] ?? 'badge-gray' ?>"><?= htmlspecialchars($log['action'] ?? '') ?></span></td>
                    <td class="px-4 py-3 text-slate-600"><?= htmlspecialchars($log['target_type'] ?? '') ?> <?= $log['target_id'] ? '#'.$log['target_id'] : '' ?></td>
                    <td class="px-4 py-3 text-slate-500 text-xs max-w-xs truncate"><?= htmlspecialchars($log['details'] ?? '') ?></td>
                    <td class="px-4 py-3 text-slate-400 text-xs font-mono"><?= htmlspecialchars($log['ip_address'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($opPagination && $opPagination['total_pages'] > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.logs.page_of', ['page' => $opPagination['page'], 'total' => $opPagination['total_pages']]) ?></p>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= min($opPagination['total_pages'], 10); $i++): ?>
            <a href="?tab=operations&page=<?= $i ?>" class="px-3 py-1.5 text-sm rounded-lg <?= $i === $opPagination['page'] ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php else: ?>
    <!-- AI Logs -->
    <?php if (empty($aiLogs)): ?>
    <div class="card p-12 text-center">
        <p class="text-sm text-slate-400"><?= $t('admin.logs.ai_empty') ?></p>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_time') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_user') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_scene') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_model') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_tokens') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_ms') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_status') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-4 py-3"><?= $t('admin.logs.col_error') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($aiLogs as $log): ?>
                <tr class="hover:bg-slate-50/50">
                    <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?= date('m-d H:i:s', strtotime($log['created_at'])) ?></td>
                    <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($log['username'] ?? '—') ?></td>
                    <td class="px-4 py-3"><span class="badge badge-info"><?= htmlspecialchars($log['scene'] ?? '') ?></span></td>
                    <td class="px-4 py-3 text-slate-500 font-mono text-xs"><?= htmlspecialchars($log['model'] ?? '') ?></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= number_format((int)($log['total_tokens'] ?? 0)) ?></td>
                    <td class="px-4 py-3 text-right text-slate-600"><?= number_format((int)($log['response_time'] ?? 0)) ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="badge <?= ($log['status'] ?? '') === 'success' ? 'badge-success' : 'badge-danger' ?>"><?= htmlspecialchars($log['status'] ?? '') ?></span>
                    </td>
                    <td class="px-4 py-3 text-xs text-red-500 max-w-xs truncate"><?= htmlspecialchars($log['error_message'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($aiPagination && $aiPagination['total_pages'] > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.logs.page_of', ['page' => $aiPagination['page'], 'total' => $aiPagination['total_pages']]) ?></p>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= min($aiPagination['total_pages'], 10); $i++): ?>
            <a href="?tab=ai&page=<?= $i ?>" class="px-3 py-1.5 text-sm rounded-lg <?= $i === $aiPagination['page'] ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>
