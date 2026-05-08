<!-- Subjects Management -->
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-slate-500 mt-1"><?= $t('admin.subjects.manage_desc') ?></p>
        </div>
        <button onclick="SubjectManager.openCreateModal()" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('admin.subjects.add') ?>
        </button>
    </div>

    <!-- Search -->
    <div class="card p-4">
        <form method="GET" class="flex gap-3">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= $t('admin.subjects.search_ph') ?>" class="form-input pl-10">
            </div>
            <button type="submit" class="btn-secondary"><?= $t('admin.common.search') ?></button>
            <?php if ($search): ?>
                <a href="/admin/subjects" class="btn-ghost"><?= $t('admin.subjects.clear') ?></a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Subject List -->
    <?php if (empty($subjects)): ?>
    <div class="card p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('admin.subjects.empty_title') ?></h3>
        <p class="text-sm text-slate-400 mb-4"><?= $t('admin.subjects.empty_desc') ?></p>
        <button onclick="SubjectManager.openCreateModal()" class="btn-primary"><?= $t('admin.subjects.create') ?></button>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.subjects.subject') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.subjects.alias') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.subjects.papers') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.subjects.order') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.common.status') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider px-6 py-3"><?= $t('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($subjects as $subject): ?>
                <tr class="hover:bg-slate-50/50 transition-colors" id="subject-row-<?= $subject['id'] ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?= htmlspecialchars($subject['cover_color']) ?>"></div>
                            <span class="text-sm font-medium text-slate-900"><?= htmlspecialchars($subject['name']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($subject['alias'] ?? '—') ?></td>
                    <td class="px-6 py-4 text-center">
                        <span class="badge badge-info"><?= $subject['paper_count'] ?></span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500"><?= $subject['sort_order'] ?></td>
                    <td class="px-6 py-4 text-center">
                        <button onclick="SubjectManager.toggleStatus(<?= $subject['id'] ?>)" class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none <?= $subject['status'] ? 'bg-primary-600' : 'bg-slate-200' ?>" id="toggle-<?= $subject['id'] ?>">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200 ease-in-out <?= $subject['status'] ? 'translate-x-4' : 'translate-x-0' ?>" id="toggle-dot-<?= $subject['id'] ?>"></span>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick='SubjectManager.openEditModal(<?= json_encode($subject) ?>)' class="btn-ghost" title="<?= $t('admin.common.edit') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button onclick="SubjectManager.deleteSubject(<?= $subject['id'] ?>, '<?= htmlspecialchars(addslashes($subject['name'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 hover:bg-red-50" title="<?= $t('admin.common.delete') ?>">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal: Create/Edit Subject -->
<div id="subject-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 id="modal-title" class="text-base font-semibold text-slate-900"><?= $t('admin.subjects.add') ?></h3>
            <button onclick="SubjectManager.closeModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="subject-form" class="p-6 space-y-4">
            <input type="hidden" id="subject-id" value="">

            <div>
                <label class="form-label" for="subject-name"><?= $t('admin.common.name') ?> <span class="text-red-500">*</span></label>
                <input type="text" id="subject-name" class="form-input" placeholder="<?= $t('admin.subjects.name_ph') ?>" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="subject-alias"><?= $t('admin.subjects.alias') ?></label>
                    <input type="text" id="subject-alias" class="form-input" placeholder="<?= $t('admin.subjects.alias_ph') ?>">
                </div>
                <div>
                    <label class="form-label" for="subject-sort"><?= $t('admin.subjects.order') ?></label>
                    <input type="number" id="subject-sort" class="form-input" placeholder="0" value="0">
                </div>
            </div>

            <div>
                <label class="form-label" for="subject-color"><?= $t('admin.subjects.theme_color') ?></label>
                <div class="flex items-center gap-3">
                    <input type="color" id="subject-color" value="#4F46E5" class="w-10 h-10 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                    <input type="text" id="subject-color-text" value="#4F46E5" class="form-input w-28 font-mono text-sm" maxlength="7">
                </div>
            </div>

            <div>
                <label class="form-label" for="subject-desc"><?= $t('admin.subjects.description') ?></label>
                <textarea id="subject-desc" class="form-input" rows="2" placeholder="<?= $t('admin.subjects.desc_ph') ?>"></textarea>
            </div>
        </form>
        <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
            <button onclick="SubjectManager.closeModal()" class="btn-secondary"><?= $t('admin.common.cancel') ?></button>
            <button onclick="SubjectManager.save()" class="btn-primary" id="modal-save-btn"><?= $t('admin.common.save') ?></button>
        </div>
    </div>
</div>

<script>
const SUBJECT_I18N = {
    add: <?= json_encode($t('admin.subjects.add')) ?>,
    edit: <?= json_encode($t('admin.subjects.edit_title')) ?>,
    name_required: <?= json_encode($t('admin.subjects.name_required')) ?>,
    saving: <?= json_encode($t('admin.subjects.saving')) ?>,
    save: <?= json_encode($t('admin.common.save')) ?>,
    updated: <?= json_encode($t('admin.subjects.updated')) ?>,
    created: <?= json_encode($t('admin.subjects.created')) ?>,
    deleted: <?= json_encode($t('admin.subjects.deleted')) ?>,
    confirm_delete: <?= json_encode($t('admin.subjects.confirm_delete')) ?>
};
const SubjectManager = {
    openCreateModal() {
        document.getElementById('modal-title').textContent = SUBJECT_I18N.add;
        document.getElementById('subject-id').value = '';
        document.getElementById('subject-name').value = '';
        document.getElementById('subject-alias').value = '';
        document.getElementById('subject-sort').value = '0';
        document.getElementById('subject-color').value = '#4F46E5';
        document.getElementById('subject-color-text').value = '#4F46E5';
        document.getElementById('subject-desc').value = '';
        document.getElementById('subject-modal').classList.remove('hidden');
        document.getElementById('subject-name').focus();
    },

    openEditModal(subject) {
        document.getElementById('modal-title').textContent = SUBJECT_I18N.edit;
        document.getElementById('subject-id').value = subject.id;
        document.getElementById('subject-name').value = subject.name;
        document.getElementById('subject-alias').value = subject.alias || '';
        document.getElementById('subject-sort').value = subject.sort_order;
        document.getElementById('subject-color').value = subject.cover_color || '#4F46E5';
        document.getElementById('subject-color-text').value = subject.cover_color || '#4F46E5';
        document.getElementById('subject-desc').value = subject.description || '';
        document.getElementById('subject-modal').classList.remove('hidden');
        document.getElementById('subject-name').focus();
    },

    closeModal() {
        document.getElementById('subject-modal').classList.add('hidden');
    },

    async save() {
        const id = document.getElementById('subject-id').value;
        const data = {
            name: document.getElementById('subject-name').value.trim(),
            alias: document.getElementById('subject-alias').value.trim(),
            sort_order: parseInt(document.getElementById('subject-sort').value) || 0,
            cover_color: document.getElementById('subject-color').value,
            description: document.getElementById('subject-desc').value.trim(),
        };

        if (!data.name) { QS.toast(SUBJECT_I18N.name_required, 'error'); return; }

        const btn = document.getElementById('modal-save-btn');
        btn.disabled = true;
        btn.textContent = SUBJECT_I18N.saving;

        try {
            const url = id ? `/admin/subjects/${id}/update` : '/admin/subjects';
            await QS.fetch(url, { method: 'POST', body: data });
            QS.toast(id ? SUBJECT_I18N.updated : SUBJECT_I18N.created);
            setTimeout(() => location.reload(), 500);
        } catch (e) {
            QS.toast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = SUBJECT_I18N.save;
        }
    },

    async toggleStatus(id) {
        try {
            const res = await QS.fetch(`/admin/subjects/${id}/toggle`, { method: 'POST' });
            const toggle = document.getElementById(`toggle-${id}`);
            const dot = document.getElementById(`toggle-dot-${id}`);
            if (res.status) {
                toggle.classList.replace('bg-slate-200', 'bg-primary-600');
                dot.classList.replace('translate-x-0', 'translate-x-4');
            } else {
                toggle.classList.replace('bg-primary-600', 'bg-slate-200');
                dot.classList.replace('translate-x-4', 'translate-x-0');
            }
        } catch (e) {
            QS.toast(e.message, 'error');
        }
    },

    async deleteSubject(id, name) {
        const confirmed = await QS.confirm(SUBJECT_I18N.confirm_delete.replace(':name', name));
        if (!confirmed) return;
        try {
            await QS.fetch(`/admin/subjects/${id}/delete`, { method: 'POST' });
            QS.toast(SUBJECT_I18N.deleted);
            document.getElementById(`subject-row-${id}`)?.remove();
        } catch (e) {
            QS.toast(e.message, 'error');
        }
    }
};

document.getElementById('subject-color').addEventListener('input', e => {
    document.getElementById('subject-color-text').value = e.target.value;
});
document.getElementById('subject-color-text').addEventListener('input', e => {
    if (/^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
        document.getElementById('subject-color').value = e.target.value;
    }
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') SubjectManager.closeModal();
});
</script>
