<!-- Users Management -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.users.manage_desc') ?></p>
        <button onclick="UserMgr.openCreateModal()" class="btn-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('admin.users.add') ?>
        </button>
    </div>

    <!-- Filters -->
    <div class="card p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="w-40">
                <label class="form-label"><?= $t('admin.users.col_role') ?></label>
                <select name="role" class="form-input">
                    <option value=""><?= $t('admin.users.all_roles') ?></option>
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>" <?= $filters['role'] === $r ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1 min-w-[200px] max-w-sm">
                <label class="form-label"><?= $t('admin.common.search') ?></label>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="form-input" placeholder="<?= $t('admin.users.search_ph') ?>">
            </div>
            <button type="submit" class="btn-secondary"><?= $t('admin.common.filter') ?></button>
            <?php if ($filters['role'] || $filters['search']): ?><a href="/admin/users" class="btn-ghost"><?= $t('admin.users.clear') ?></a><?php endif; ?>
        </form>
    </div>

    <!-- Role Summary -->
    <div class="flex flex-wrap gap-2">
        <?php foreach ($roleCounts as $rc):
            $colors = ['super_admin'=>'bg-red-50 text-red-700','admin'=>'bg-purple-50 text-purple-700','editor'=>'bg-blue-50 text-blue-700','vip'=>'bg-amber-50 text-amber-700','user'=>'bg-slate-100 text-slate-600','reviewer'=>'bg-teal-50 text-teal-700'];
        ?>
        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $colors[$rc['role']] ?? 'bg-slate-100 text-slate-600' ?>">
            <?= ucwords(str_replace('_',' ',$rc['role'])) ?>: <?= $rc['cnt'] ?>
        </span>
        <?php endforeach; ?>
    </div>

    <!-- Users Table -->
    <?php if (empty($users)): ?>
    <div class="card p-12 text-center">
        <p class="text-sm text-slate-400"><?= $t('admin.users.empty_title') ?></p>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_user') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_role') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_sessions') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_status') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_last_login') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.users.col_actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($users as $u):
                    $roleColors = ['super_admin'=>'badge-danger','admin'=>'bg-purple-50 text-purple-700','editor'=>'badge-info','vip'=>'badge-warning','user'=>'badge-gray','reviewer'=>'bg-teal-50 text-teal-700'];
                ?>
                <tr class="hover:bg-slate-50/50" id="user-row-<?= $u['id'] ?>">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-primary-700"><?= strtoupper(mb_substr($u['username'], 0, 1)) ?></span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($u['username']) ?></p>
                                <p class="text-xs text-slate-400"><?= htmlspecialchars($u['email'] ?? '') ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3"><span class="badge <?= $roleColors[$u['role']] ?? 'badge-gray' ?>"><?= ucwords(str_replace('_',' ',$u['role'])) ?></span></td>
                    <td class="px-5 py-3 text-center text-sm text-slate-600"><?= $u['completed_count'] ?>/<?= $u['session_count'] ?></td>
                    <td class="px-5 py-3 text-center">
                        <button onclick="UserMgr.toggleStatus(<?= $u['id'] ?>)"
                                class="relative inline-flex h-5 w-9 rounded-full border-2 border-transparent cursor-pointer transition-colors <?= $u['status'] ? 'bg-primary-600' : 'bg-slate-200' ?>"
                                id="toggle-<?= $u['id'] ?>">
                            <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition <?= $u['status'] ? 'translate-x-4' : 'translate-x-0' ?>" id="toggle-dot-<?= $u['id'] ?>"></span>
                        </button>
                    </td>
                    <td class="px-5 py-3 text-sm text-slate-500"><?= $u['last_login_at'] ? date('m/d H:i', strtotime($u['last_login_at'])) : '—' ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick='UserMgr.openEditModal(<?= json_encode($u) ?>)' class="btn-ghost" title="<?= $t('admin.common.edit') ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <?php if ($u['role'] !== 'super_admin'): ?>
                            <button onclick="UserMgr.deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['username'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 hover:bg-red-50" title="<?= $t('admin.common.delete') ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination['total_pages'] > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.users.page_of', ['page' => $pagination['page'], 'total' => $pagination['total_pages']]) ?></p>
        <div class="flex gap-1">
            <?php for ($i = 1; $i <= min($pagination['total_pages'], 10); $i++): ?>
            <a href="?page=<?= $i ?>&role=<?= $filters['role'] ?>&search=<?= urlencode($filters['search']) ?>"
               class="px-3 py-1.5 text-sm rounded-lg <?= $i === $pagination['page'] ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Create/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 id="user-modal-title" class="text-base font-semibold text-slate-900"><?= $t('admin.users.modal_add') ?></h3>
            <button onclick="UserMgr.closeModal()" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="user-id" value="">
            <div>
                <label class="form-label"><?= $t('admin.users.username') ?> <span class="text-red-500">*</span></label>
                <input type="text" id="user-username" class="form-input" placeholder="<?= $t('admin.users.username_ph') ?>" required>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.users.password') ?> <span id="pw-hint" class="text-xs text-slate-400 font-normal"><?= $t('admin.users.pw_required') ?></span></label>
                <input type="password" id="user-password" class="form-input" placeholder="<?= $t('admin.users.pw_ph') ?>">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.users.email') ?></label>
                    <input type="email" id="user-email" class="form-input" placeholder="<?= $t('admin.users.email_ph') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.users.display_name') ?></label>
                    <input type="text" id="user-display" class="form-input" placeholder="<?= $t('admin.users.display_ph') ?>">
                </div>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.users.role') ?> <span class="text-red-500">*</span></label>
                <select id="user-role" class="form-input">
                    <?php foreach ($roles as $r): ?>
                    <option value="<?= $r ?>"><?= ucwords(str_replace('_',' ',$r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button onclick="UserMgr.closeModal()" class="btn-secondary"><?= $t('admin.users.cancel') ?></button>
            <button onclick="UserMgr.save()" class="btn-primary" id="user-save-btn"><?= $t('admin.users.save') ?></button>
        </div>
    </div>
</div>

<script>
const USER_I18N = {
    modal_add: <?= json_encode($t('admin.users.modal_add')) ?>,
    modal_edit: <?= json_encode($t('admin.users.modal_edit')) ?>,
    pw_required: <?= json_encode($t('admin.users.pw_required')) ?>,
    pw_keep: <?= json_encode($t('admin.users.pw_keep')) ?>,
    username_required: <?= json_encode($t('admin.users.username_required')) ?>,
    password_required: <?= json_encode($t('admin.users.password_required')) ?>,
    save: <?= json_encode($t('admin.users.save')) ?>,
    saving: <?= json_encode($t('admin.users.saving')) ?>,
    updated: <?= json_encode($t('admin.users.updated')) ?>,
    created: <?= json_encode($t('admin.users.created')) ?>,
    deleted: <?= json_encode($t('admin.users.deleted')) ?>,
    confirm_delete: <?= json_encode($t('admin.users.confirm_delete')) ?>
};
const UserMgr = {
    openCreateModal() {
        document.getElementById('user-modal-title').textContent = USER_I18N.modal_add;
        document.getElementById('user-id').value = '';
        document.getElementById('user-username').value = '';
        document.getElementById('user-username').disabled = false;
        document.getElementById('user-password').value = '';
        document.getElementById('pw-hint').textContent = USER_I18N.pw_required;
        document.getElementById('user-email').value = '';
        document.getElementById('user-display').value = '';
        document.getElementById('user-role').value = 'user';
        document.getElementById('user-modal').classList.remove('hidden');
    },

    openEditModal(u) {
        document.getElementById('user-modal-title').textContent = USER_I18N.modal_edit;
        document.getElementById('user-id').value = u.id;
        document.getElementById('user-username').value = u.username;
        document.getElementById('user-username').disabled = true;
        document.getElementById('user-password').value = '';
        document.getElementById('pw-hint').textContent = USER_I18N.pw_keep;
        document.getElementById('user-email').value = u.email || '';
        document.getElementById('user-display').value = u.display_name || '';
        document.getElementById('user-role').value = u.role;
        document.getElementById('user-modal').classList.remove('hidden');
    },

    closeModal() { document.getElementById('user-modal').classList.add('hidden'); },

    async save() {
        const id = document.getElementById('user-id').value;
        const data = {
            username: document.getElementById('user-username').value.trim(),
            password: document.getElementById('user-password').value,
            email: document.getElementById('user-email').value.trim(),
            display_name: document.getElementById('user-display').value.trim(),
            role: document.getElementById('user-role').value,
        };
        if (!data.username) { QS.toast(USER_I18N.username_required, 'error'); return; }
        if (!id && !data.password) { QS.toast(USER_I18N.password_required, 'error'); return; }

        const btn = document.getElementById('user-save-btn');
        btn.disabled = true; btn.textContent = USER_I18N.saving;
        try {
            const url = id ? `/admin/users/${id}/update` : '/admin/users';
            await QS.fetch(url, { method: 'POST', body: data });
            QS.toast(id ? USER_I18N.updated : USER_I18N.created);
            setTimeout(() => location.reload(), 500);
        } catch(e) { QS.toast(e.message, 'error'); }
        finally { btn.disabled = false; btn.textContent = USER_I18N.save; }
    },

    async toggleStatus(id) {
        try {
            const res = await QS.fetch(`/admin/users/${id}/toggle`, { method: 'POST' });
            const t = document.getElementById(`toggle-${id}`), d = document.getElementById(`toggle-dot-${id}`);
            if (res.status) { t.classList.replace('bg-slate-200','bg-primary-600'); d.classList.replace('translate-x-0','translate-x-4'); }
            else { t.classList.replace('bg-primary-600','bg-slate-200'); d.classList.replace('translate-x-4','translate-x-0'); }
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async deleteUser(id, name) {
        if (!await QS.confirm(USER_I18N.confirm_delete.replace(':name', name))) return;
        try {
            await QS.fetch(`/admin/users/${id}/delete`, { method: 'POST' });
            QS.toast(USER_I18N.deleted);
            document.getElementById(`user-row-${id}`)?.remove();
        } catch(e) { QS.toast(e.message, 'error'); }
    }
};
document.addEventListener('keydown', e => { if (e.key === 'Escape') UserMgr.closeModal(); });
</script>
