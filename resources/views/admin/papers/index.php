<!-- Papers Management -->
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.papers.manage_desc') ?></p>
        <a href="/admin/papers/create" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('admin.papers.create') ?>
        </a>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-48">
                <label class="form-label"><?= $t('admin.papers.subject') ?></label>
                <select name="subject_id" class="form-input">
                    <option value=""><?= $t('admin.papers.all_subjects') ?></option>
                    <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $filters['subject_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="w-36">
                <label class="form-label"><?= $t('admin.common.status') ?></label>
                <select name="status" class="form-input">
                    <option value=""><?= $t('admin.common.all') ?></option>
                    <option value="draft" <?= $filters['status'] === 'draft' ? 'selected' : '' ?>><?= $t('admin.status.draft') ?></option>
                    <option value="published" <?= $filters['status'] === 'published' ? 'selected' : '' ?>><?= $t('admin.status.published') ?></option>
                    <option value="archived" <?= $filters['status'] === 'archived' ? 'selected' : '' ?>><?= $t('admin.status.archived') ?></option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px] max-w-sm">
                <label class="form-label"><?= $t('admin.common.search') ?></label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="form-input" placeholder="<?= $t('admin.papers.search_ph') ?>">
            </div>
            <button type="submit" class="btn-secondary"><?= $t('admin.common.filter') ?></button>
            <?php if ($filters['subject_id'] || $filters['status'] || $filters['search']): ?>
                <a href="/admin/papers" class="btn-ghost"><?= $t('admin.subjects.clear') ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Paper List -->
    <?php if (empty($papers)): ?>
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('admin.papers.empty_title') ?></h3>
        <p class="text-sm text-slate-400 mb-4"><?= $t('admin.papers.empty_desc') ?></p>
        <a href="/admin/papers/create" class="btn-primary"><?= $t('admin.papers.create') ?></a>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.papers.paper') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.papers.subject') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.papers.year') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.papers.difficulty') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.papers.duration_col') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.common.status') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($papers as $paper): ?>
                <tr class="hover:bg-slate-50/50 transition-colors" id="paper-row-<?= $paper['id'] ?>">
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($paper['title']) ?></p>
                            <?php if ($paper['subtitle']): ?>
                            <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($paper['subtitle']) ?></p>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:<?= htmlspecialchars($paper['subject_color'] ?? '#3B82F6') ?>"></div>
                            <span class="text-sm text-slate-600"><?= htmlspecialchars($paper['subject_name'] ?? '') ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500"><?= htmlspecialchars($paper['year'] ?? '—') ?></td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-3.5 h-3.5 <?= $i <= ($paper['difficulty'] ?? 3) ? 'text-amber-400' : 'text-slate-200' ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <?php endfor; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500"><?= $paper['duration'] ?><?= $t('admin.papers.min') ?></td>
                    <td class="px-6 py-4 text-center">
                        <?php
                        $statusBadge = match($paper['status']) {
                            'published' => 'badge-success',
                            'draft' => 'badge-warning',
                            'archived' => 'badge-gray',
                            default => 'badge-gray',
                        };
                        $statusLabel = [
                            'published' => $t('admin.status.published'),
                            'draft' => $t('admin.status.draft'),
                            'archived' => $t('admin.status.archived'),
                        ][$paper['status']] ?? $paper['status'];
                        ?>
                        <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="/admin/papers/<?= $paper['id'] ?>/fill" class="btn-ghost text-primary-600 hover:bg-primary-50" title="<?= $t('admin.papers.fill_questions') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </a>
                            <a href="/admin/papers/<?= $paper['id'] ?>/edit" class="btn-ghost" title="<?= $t('admin.common.edit') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button onclick="PaperManager.deletePaper(<?= $paper['id'] ?>, '<?= htmlspecialchars(addslashes($paper['title'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 hover:bg-red-50" title="<?= $t('admin.common.delete') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.papers.showing', ['n' => count($papers), 'total' => $pagination['total']]) ?></p>
        <div class="flex items-center gap-1">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
            <a href="?page=<?= $i ?>&subject_id=<?= $filters['subject_id'] ?>&status=<?= $filters['status'] ?>&search=<?= urlencode($filters['search']) ?>"
               class="px-3 py-1.5 text-sm rounded-lg <?= $i === $pagination['page'] ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const PAPER_I18N = {
    confirm_delete: <?= json_encode($t('admin.papers.confirm_delete')) ?>,
    deleted: <?= json_encode($t('admin.papers.deleted')) ?>
};
const PaperManager = {
    async deletePaper(id, title) {
        const confirmed = await QS.confirm(PAPER_I18N.confirm_delete.replace(':title', title));
        if (!confirmed) return;
        try {
            await QS.fetch(`/admin/papers/${id}/delete`, { method: 'POST' });
            QS.toast(PAPER_I18N.deleted);
            document.getElementById(`paper-row-${id}`)?.remove();
        } catch (e) {
            QS.toast(e.message, 'error');
        }
    }
};
</script>
