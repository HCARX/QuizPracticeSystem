<!-- Templates Management -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500"><?= $t('admin.templates.manage_desc') ?></p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-slate-200">
        <a href="?tab=blueprints" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $tab === 'blueprints' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' ?>"><?= $t('admin.templates.tab_blueprints') ?></a>
        <a href="?tab=modules" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?= $tab === 'modules' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700' ?>"><?= $t('admin.templates.tab_modules') ?></a>
    </div>

    <?php if ($tab === 'blueprints'): ?>
    <!-- Blueprints -->
    <div class="flex justify-end">
        <button onclick="TplMgr.openBlueprintModal()" class="btn-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('admin.templates.new_blueprint') ?>
        </button>
    </div>

    <?php if (empty($blueprints)): ?>
    <div class="card p-12 text-center">
        <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        <p class="text-sm text-slate-500 mb-1"><?= $t('admin.templates.bp_empty_title') ?></p>
        <p class="text-xs text-slate-400"><?= $t('admin.templates.bp_empty_desc') ?></p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($blueprints as $bp):
            $statusColors = ['draft'=>'badge-warning','active'=>'badge-success','archived'=>'badge-gray'];
            $statusLabel = [
                'draft' => $t('admin.templates.status_draft'),
                'active' => $t('admin.templates.status_active'),
                'archived' => $t('admin.templates.status_archived'),
            ][$bp['status']] ?? $bp['status'];
            $blueprint = json_decode($bp['blueprint_json'] ?? '{}', true);
            $sectionCount = count($blueprint['sections'] ?? []);
            $blockCount = 0;
            foreach ($blueprint['sections'] ?? [] as $sec) $blockCount += count($sec['blocks'] ?? []);
        ?>
        <div class="card p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="min-w-0">
                    <h4 class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars($bp['name']) ?></h4>
                    <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($bp['paper_title'] ?? $t('admin.templates.unlinked')) ?></p>
                </div>
                <span class="badge <?= $statusColors[$bp['status']] ?? 'badge-gray' ?> flex-shrink-0"><?= $statusLabel ?></span>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-500 mb-4">
                <span><?= $t('admin.templates.sections', ['n' => $sectionCount]) ?></span>
                <span><?= $t('admin.templates.blocks', ['n' => $blockCount]) ?></span>
                <span>v<?= $bp['version'] ?></span>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-4">
                <span><?= $t('admin.templates.by', ['name' => htmlspecialchars($bp['creator'] ?? '—')]) ?></span>
                <span>&middot;</span>
                <span><?= date('m/d H:i', strtotime($bp['updated_at'])) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/templates/<?= $bp['id'] ?>/editor" class="btn-primary text-xs px-3 py-1.5">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <?= $t('admin.common.edit') ?>
                </a>
                <button onclick="TplMgr.deleteBlueprint(<?= $bp['id'] ?>, '<?= htmlspecialchars(addslashes($bp['name'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 text-xs px-3 py-1.5"><?= $t('admin.common.delete') ?></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Modules -->
    <div class="flex items-center justify-between">
        <div class="flex flex-wrap gap-2">
            <?php foreach ($moduleCategories as $mc): ?>
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600"><?= htmlspecialchars($mc['category']) ?>: <?= $mc['cnt'] ?></span>
            <?php endforeach; ?>
        </div>
        <button onclick="TplMgr.openModuleModal()" class="btn-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('admin.templates.new_module') ?>
        </button>
    </div>

    <?php if (empty($modules)): ?>
    <div class="card p-12 text-center">
        <p class="text-sm text-slate-400"><?= $t('admin.templates.modules_empty') ?></p>
    </div>
    <?php else: ?>
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.templates.col_name') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.templates.col_category') ?></th>
                    <th class="text-left text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.templates.col_preview') ?></th>
                    <th class="text-center text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.templates.col_usage') ?></th>
                    <th class="text-right text-xs font-semibold text-slate-500 uppercase px-5 py-3"><?= $t('admin.common.actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($modules as $m):
                    $catColors = ['title'=>'badge-info','instruction'=>'bg-purple-50 text-purple-700','passage'=>'badge-success','audio_instruction'=>'badge-warning','writing_prompt'=>'bg-pink-50 text-pink-700','section_header'=>'badge-gray'];
                ?>
                <tr class="hover:bg-slate-50/50" id="module-row-<?= $m['id'] ?>">
                    <td class="px-5 py-3">
                        <p class="font-medium text-slate-900"><?= htmlspecialchars($m['name']) ?></p>
                        <p class="text-xs text-slate-400"><?= htmlspecialchars($m['content_format']) ?></p>
                    </td>
                    <td class="px-5 py-3">
                        <span class="badge <?= $catColors[$m['category']] ?? 'badge-gray' ?>"><?= htmlspecialchars($m['category']) ?></span>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs max-w-xs truncate"><?= htmlspecialchars(mb_substr($m['content'], 0, 80)) ?><?= mb_strlen($m['content']) > 80 ? '...' : '' ?></td>
                    <td class="px-5 py-3 text-center text-slate-600"><?= $m['usage_count'] ?></td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button onclick='TplMgr.openModuleModal(<?= json_encode($m) ?>)' class="btn-ghost" title="<?= $t('admin.common.edit') ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                            <button onclick="TplMgr.deleteModule(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['name'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 hover:bg-red-50" title="<?= $t('admin.common.delete') ?>"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Blueprint Modal -->
<div id="bp-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-base font-semibold text-slate-900"><?= $t('admin.templates.bp_modal_new') ?></h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="form-label"><?= $t('admin.common.name') ?> <span class="text-red-500">*</span></label>
                <input type="text" id="bp-name" class="form-input" placeholder="<?= $t('admin.templates.bp_name_ph') ?>">
            </div>
            <div>
                <label class="form-label"><?= $t('admin.templates.paper') ?> <span class="text-red-500">*</span></label>
                <select id="bp-paper" class="form-input">
                    <option value=""><?= $t('admin.templates.select_paper_ph') ?></option>
                    <?php foreach ($papers as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['subject_name'] ?? '') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button onclick="document.getElementById('bp-modal').classList.add('hidden')" class="btn-secondary"><?= $t('admin.common.cancel') ?></button>
            <button onclick="TplMgr.createBlueprint()" class="btn-primary"><?= $t('admin.templates.create') ?></button>
        </div>
    </div>
</div>

<!-- Module Modal -->
<div id="mod-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 id="mod-modal-title" class="text-base font-semibold text-slate-900"><?= $t('admin.templates.add_module') ?></h3>
            <button onclick="document.getElementById('mod-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="mod-id" value="">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.common.name') ?> <span class="text-red-500">*</span></label>
                    <input type="text" id="mod-name" class="form-input" placeholder="<?= $t('admin.templates.module_name_ph') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.templates.col_category') ?> <span class="text-red-500">*</span></label>
                    <select id="mod-category" class="form-input">
                        <option value="title"><?= $t('admin.templates.cat_title') ?></option>
                        <option value="instruction"><?= $t('admin.templates.cat_instruction') ?></option>
                        <option value="passage"><?= $t('admin.templates.cat_passage') ?></option>
                        <option value="audio_instruction"><?= $t('admin.templates.cat_audio_instruction') ?></option>
                        <option value="writing_prompt"><?= $t('admin.templates.cat_writing_prompt') ?></option>
                        <option value="section_header"><?= $t('admin.templates.cat_section_header') ?></option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.templates.content') ?> <span class="text-red-500">*</span></label>
                <textarea id="mod-content" class="form-input text-xs font-mono" rows="6" placeholder="<?= $t('admin.templates.content_ph') ?>"></textarea>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.templates.format') ?></label>
                <select id="mod-format" class="form-input">
                    <option value="text"><?= $t('admin.templates.format_text') ?></option>
                    <option value="html"><?= $t('admin.templates.format_html') ?></option>
                    <option value="markdown"><?= $t('admin.templates.format_markdown') ?></option>
                </select>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button onclick="document.getElementById('mod-modal').classList.add('hidden')" class="btn-secondary"><?= $t('admin.common.cancel') ?></button>
            <button onclick="TplMgr.saveModule()" class="btn-primary"><?= $t('admin.common.save') ?></button>
        </div>
    </div>
</div>

<script>
const TPL_I18N = {
    bp_required: <?= json_encode($t('admin.templates.bp_required')) ?>,
    bp_created: <?= json_encode($t('admin.templates.bp_created')) ?>,
    bp_confirm_delete: <?= json_encode($t('admin.templates.bp_confirm_delete')) ?>,
    bp_deleted: <?= json_encode($t('admin.templates.bp_deleted')) ?>,
    mod_required: <?= json_encode($t('admin.templates.mod_required')) ?>,
    mod_updated: <?= json_encode($t('admin.templates.mod_updated')) ?>,
    mod_created: <?= json_encode($t('admin.templates.mod_created')) ?>,
    mod_confirm_delete: <?= json_encode($t('admin.templates.mod_confirm_delete')) ?>,
    mod_deleted: <?= json_encode($t('admin.templates.mod_deleted')) ?>,
    add_module: <?= json_encode($t('admin.templates.add_module')) ?>,
    edit_module: <?= json_encode($t('admin.templates.edit_module')) ?>
};
const TplMgr = {
    openBlueprintModal() {
        document.getElementById('bp-name').value = '';
        document.getElementById('bp-paper').value = '';
        document.getElementById('bp-modal').classList.remove('hidden');
    },

    async createBlueprint() {
        const name = document.getElementById('bp-name').value.trim();
        const paperId = document.getElementById('bp-paper').value;
        if (!name || !paperId) { QS.toast(TPL_I18N.bp_required, 'error'); return; }
        try {
            const res = await QS.fetch('/admin/templates', { method: 'POST', body: { name, paper_id: paperId } });
            QS.toast(TPL_I18N.bp_created);
            window.location.href = `/admin/templates/${res.id}/editor`;
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async deleteBlueprint(id, name) {
        if (!await QS.confirm(TPL_I18N.bp_confirm_delete.replace(':name', name))) return;
        try {
            await QS.fetch(`/admin/templates/${id}/delete`, { method: 'POST' });
            QS.toast(TPL_I18N.bp_deleted);
            location.reload();
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    openModuleModal(m = null) {
        document.getElementById('mod-modal-title').textContent = m ? TPL_I18N.edit_module : TPL_I18N.add_module;
        document.getElementById('mod-id').value = m ? m.id : '';
        document.getElementById('mod-name').value = m ? m.name : '';
        document.getElementById('mod-category').value = m ? m.category : 'title';
        document.getElementById('mod-content').value = m ? m.content : '';
        document.getElementById('mod-format').value = m ? m.content_format : 'text';
        document.getElementById('mod-modal').classList.remove('hidden');
    },

    async saveModule() {
        const id = document.getElementById('mod-id').value;
        const data = {
            name: document.getElementById('mod-name').value.trim(),
            category: document.getElementById('mod-category').value,
            content: document.getElementById('mod-content').value,
            content_format: document.getElementById('mod-format').value,
        };
        if (!data.name || !data.content) { QS.toast(TPL_I18N.mod_required, 'error'); return; }
        try {
            const url = id ? `/admin/templates/modules/${id}/update` : '/admin/templates/modules';
            await QS.fetch(url, { method: 'POST', body: data });
            QS.toast(id ? TPL_I18N.mod_updated : TPL_I18N.mod_created);
            location.reload();
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async deleteModule(id, name) {
        if (!await QS.confirm(TPL_I18N.mod_confirm_delete.replace(':name', name))) return;
        try {
            await QS.fetch(`/admin/templates/modules/${id}/delete`, { method: 'POST' });
            QS.toast(TPL_I18N.mod_deleted);
            document.getElementById(`module-row-${id}`)?.remove();
        } catch(e) { QS.toast(e.message, 'error'); }
    }
};
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('bp-modal').classList.add('hidden');
        document.getElementById('mod-modal').classList.add('hidden');
    }
});
</script>
