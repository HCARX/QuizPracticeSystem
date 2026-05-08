<!-- Questions: Paper Browser -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900"><?= $t('admin.questions.idx_title') ?></h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= $t('admin.questions.idx_subtitle') ?></p>
        </div>
        <a href="/admin/papers" class="btn-secondary text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <?= $t('admin.questions.idx_manage_papers') ?>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-56">
                <label class="form-label"><?= $t('admin.questions.idx_subject') ?></label>
                <select name="subject_id" class="form-input" onchange="this.form.submit()">
                    <option value=""><?= $t('admin.papers.all_subjects') ?></option>
                    <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (string)$filters['subjectId'] === (string)$s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px] max-w-sm">
                <label class="form-label"><?= $t('admin.questions.idx_search_papers') ?></label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="form-input" placeholder="<?= $t('admin.questions.idx_search_ph') ?>">
            </div>
            <button type="submit" class="btn-secondary"><?= $t('admin.common.filter') ?></button>
        </form>
    </div>

    <?php if (empty($papers)): ?>
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('admin.papers.empty_title') ?></h3>
        <p class="text-sm text-slate-400"><?= $t('admin.questions.idx_empty_hint_prefix') ?><a href="/admin/papers/create" class="text-primary-600 underline"><?= $t('admin.questions.idx_empty_hint_link') ?></a><?= $t('admin.questions.idx_empty_hint_suffix') ?></p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php foreach ($papers as $p): ?>
        <div class="card p-5 hover:shadow-md transition">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-slate-900 truncate"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($p['subject_name'] ?? '—') ?></p>
                </div>
                <span class="badge <?= ($p['status'] ?? '') === 'published' ? 'badge-success' : 'badge-warning' ?> text-xs ml-2 flex-shrink-0">
                    <?= htmlspecialchars($p['status'] ?? 'draft') ?>
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?= (int)$p['q_count'] ?> <?= $t('admin.questions.idx_q_unit') ?>
                </span>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    <?= (int)$p['bp_count'] ?> <?= $t('admin.questions.idx_bp_unit') ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <?php if ((int)$p['bp_count'] > 0): ?>
                <a href="/admin/questions/fill?paper_id=<?= $p['id'] ?>" class="btn-primary text-xs flex-1 justify-center"><?= $t('admin.questions.idx_fill_by_tpl') ?></a>
                <?php else: ?>
                <a href="/admin/templates" class="btn-secondary text-xs flex-1 justify-center"><?= $t('admin.questions.idx_goto_create_bp') ?></a>
                <?php endif; ?>
                <a href="/admin/papers/<?= $p['id'] ?>/edit" class="btn-ghost text-xs" title="<?= $t('admin.questions.idx_edit_paper') ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
