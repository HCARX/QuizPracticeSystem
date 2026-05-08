<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/papers" class="text-sm text-slate-500 hover:text-primary-600">← <?= $t('common.back') ?></a>
            <h1 class="text-xl font-semibold text-slate-900 mt-1"><?= $t('admin.fill.pick_template') ?></h1>
            <p class="text-sm text-slate-500 mt-1"><?= htmlspecialchars($paper['title']) ?><?php if (!empty($paper['subject_name'])): ?> · <?= htmlspecialchars($paper['subject_name']) ?><?php endif; ?></p>
        </div>
    </div>

    <?php if (empty($templates)): ?>
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm text-slate-500 mb-4"><?= $t('admin.fill.no_templates') ?></p>
        <a href="/admin/templates" class="btn-primary"><?= $t('common.continue') ?> →</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($templates as $tpl): ?>
        <div class="card p-5 flex flex-col">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($tpl['name']) ?></h3>
                <?php
                    $st = $tpl['status'] ?? 'draft';
                    $badge = $st === 'published' ? 'badge-success' : ($st === 'archived' ? 'badge-gray' : 'badge-warning');
                ?>
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($st) ?></span>
            </div>
            <p class="text-xs text-slate-400 mb-4">v<?= htmlspecialchars((string)($tpl['version'] ?? '1')) ?></p>
            <div class="grid grid-cols-3 gap-2 text-center mb-4">
                <div class="bg-slate-50 rounded-lg py-2">
                    <p class="text-xs text-slate-400"><?= $t('admin.fill.section') ?></p>
                    <p class="text-lg font-semibold text-slate-800"><?= (int)$tpl['_section_count'] ?></p>
                </div>
                <div class="bg-slate-50 rounded-lg py-2">
                    <p class="text-xs text-slate-400"><?= $t('admin.fill.block') ?></p>
                    <p class="text-lg font-semibold text-slate-800"><?= (int)$tpl['_block_count'] ?></p>
                </div>
                <div class="bg-primary-50 rounded-lg py-2">
                    <p class="text-xs text-primary-500">Q</p>
                    <p class="text-lg font-semibold text-primary-700"><?= (int)$tpl['_q_count'] ?></p>
                </div>
            </div>
            <a href="/admin/papers/<?= (int)$paper['id'] ?>/fill/<?= (int)$tpl['id'] ?>" class="btn-primary mt-auto justify-center"><?= $t('admin.fill.use_template') ?></a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
