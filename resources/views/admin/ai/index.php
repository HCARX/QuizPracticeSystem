<!-- AI Settings -->
<div class="space-y-6">
    <p class="text-sm text-slate-500"><?= $t('admin.ai.manage_desc') ?></p>

    <!-- Provider Config -->
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200 mb-5"><?= $t('admin.ai.provider_title') ?></h3>
        <form id="provider-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.ai.base_url') ?> <span class="text-red-500">*</span></label>
                    <input type="text" id="ai-base-url" class="form-input" value="<?= htmlspecialchars($provider['base_url'] ?? 'https://api.openai.com/v1') ?>" placeholder="https://api.openai.com/v1">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.api_key') ?></label>
                    <input type="password" id="ai-api-key" class="form-input" value="<?= $provider['api_key_encrypted'] ? '••••••••' : '' ?>" placeholder="sk-...">
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.ai.default_model') ?></label>
                    <input type="text" id="ai-model" class="form-input" value="<?= htmlspecialchars($provider['default_model'] ?? 'gpt-4o') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.timeout') ?></label>
                    <input type="number" id="ai-timeout" class="form-input" value="<?= $provider['timeout'] ?? 30 ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.temperature') ?></label>
                    <input type="number" id="ai-temp" class="form-input" step="0.1" min="0" max="2" value="<?= $provider['temperature'] ?? 0.7 ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.max_tokens') ?></label>
                    <input type="number" id="ai-tokens" class="form-input" value="<?= $provider['max_tokens'] ?? 2000 ?>">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="button" onclick="AiSettings.saveProvider()" class="btn-primary"><?= $t('admin.ai.save_config') ?></button>
            </div>
        </form>
    </div>

    <!-- Models -->
    <div class="card p-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
            <h3 class="text-sm font-semibold text-slate-900"><?= $t('admin.ai.models_title') ?></h3>
            <button onclick="AiSettings.openModelModal()" class="btn-primary text-xs">
                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <?= $t('admin.ai.add_model') ?>
            </button>
        </div>
        <div class="space-y-3">
            <?php foreach ($models as $m): ?>
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg" id="model-row-<?= $m['id'] ?>">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-2 h-2 rounded-full flex-shrink-0 <?= $m['status'] ? 'bg-emerald-500' : 'bg-slate-300' ?>"></span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate"><?= htmlspecialchars($m['name']) ?></p>
                        <p class="text-xs text-slate-400 font-mono truncate"><?= htmlspecialchars($m['model_id']) ?><?= $m['description'] ? ' · ' . htmlspecialchars($m['description']) : '' ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button onclick='AiSettings.openModelModal(<?= json_encode($m) ?>)' class="btn-ghost text-xs px-2 py-1" title="<?= $t('admin.common.edit') ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button onclick="AiSettings.toggleModel(<?= $m['id'] ?>)" class="btn-ghost text-xs px-2 py-1" title="<?= $t('admin.ai.toggle') ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </button>
                    <button onclick="AiSettings.deleteModel(<?= $m['id'] ?>, '<?= htmlspecialchars(addslashes($m['name'])) ?>')" class="btn-ghost text-red-400 hover:text-red-600 text-xs px-2 py-1" title="<?= $t('admin.common.delete') ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Model Modal -->
    <div id="model-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 id="model-modal-title" class="text-base font-semibold text-slate-900"><?= $t('admin.ai.add_model') ?></h3>
                <button onclick="document.getElementById('model-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="model-id" value="">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label"><?= $t('admin.ai.model_name') ?> <span class="text-red-500">*</span></label>
                        <input type="text" id="model-name" class="form-input" placeholder="GPT-4o">
                    </div>
                    <div>
                        <label class="form-label"><?= $t('admin.ai.model_id') ?> <span class="text-red-500">*</span></label>
                        <input type="text" id="model-model-id" class="form-input font-mono" placeholder="gpt-4o">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label"><?= $t('admin.ai.model_provider') ?></label>
                        <input type="text" id="model-provider" class="form-input" value="openai">
                    </div>
                    <div>
                        <label class="form-label"><?= $t('admin.ai.model_sort') ?></label>
                        <input type="number" id="model-sort" class="form-input" value="0">
                    </div>
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.model_desc') ?></label>
                    <input type="text" id="model-desc" class="form-input">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.ai.model_roles') ?></label>
                    <input type="text" id="model-roles" class="form-input font-mono" placeholder="super_admin,admin,user">
                    <p class="text-xs text-slate-400 mt-1"><?= $t('admin.ai.model_roles_hint') ?></p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <button onclick="document.getElementById('model-modal').classList.add('hidden')" class="btn-secondary"><?= $t('admin.common.cancel') ?></button>
                <button onclick="AiSettings.saveModel()" class="btn-primary"><?= $t('admin.common.save') ?></button>
            </div>
        </div>
    </div>

    <!-- Prompt Templates -->
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200 mb-4"><?= $t('admin.ai.prompts_title') ?></h3>
        <div class="space-y-4">
            <?php foreach ($prompts as $p): ?>
            <details class="bg-slate-50 rounded-lg overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-slate-100 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="badge <?= $p['status'] ? 'badge-success' : 'badge-gray' ?>"><?= $p['status'] ? $t('admin.ai.prompt_active') : $t('admin.ai.prompt_off') ?></span>
                        <div>
                            <p class="text-sm font-medium text-slate-800"><?= htmlspecialchars($p['name']) ?></p>
                            <p class="text-xs text-slate-400"><?= htmlspecialchars($p['scene']) ?></p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div class="p-4 border-t border-slate-200 space-y-4">
                    <div>
                        <label class="form-label"><?= $t('admin.ai.system_prompt') ?></label>
                        <textarea class="form-input text-xs font-mono" rows="3" id="prompt-sys-<?= $p['id'] ?>"><?= htmlspecialchars($p['system_prompt'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="form-label"><?= $t('admin.ai.user_prompt_template') ?></label>
                        <textarea class="form-input text-xs font-mono" rows="6" id="prompt-user-<?= $p['id'] ?>"><?= htmlspecialchars($p['user_prompt_template'] ?? '') ?></textarea>
                    </div>
                    <?php if ($p['variables_json']): ?>
                    <div>
                        <label class="form-label"><?= $t('admin.ai.variables') ?></label>
                        <div class="text-xs text-slate-500 bg-white p-2 rounded-lg border border-slate-200 font-mono"><?= htmlspecialchars($p['variables_json']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="prompt-status-<?= $p['id'] ?>" <?= $p['status'] ? 'checked' : '' ?> class="text-primary-600 rounded">
                            <span class="text-sm text-slate-600"><?= $t('admin.ai.enabled') ?></span>
                        </label>
                        <button onclick="AiSettings.savePrompt(<?= $p['id'] ?>)" class="btn-primary text-sm"><?= $t('admin.ai.save_prompt') ?></button>
                    </div>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="card p-6">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
            <h3 class="text-sm font-semibold text-slate-900"><?= $t('admin.ai.logs_title') ?></h3>
            <div class="flex gap-4 text-xs text-slate-500">
                <span><?= $t('admin.ai.logs_total', ['n' => number_format((int)($logStats['total'] ?? 0))]) ?></span>
                <span><?= $t('admin.ai.logs_tokens', ['n' => number_format((int)($logStats['tokens'] ?? 0))]) ?></span>
                <span class="text-red-500"><?= $t('admin.ai.logs_errors', ['n' => (int)($logStats['errors'] ?? 0)]) ?></span>
            </div>
        </div>
        <?php if (empty($recentLogs)): ?>
        <p class="text-sm text-slate-400 py-4 text-center"><?= $t('admin.ai.logs_empty') ?></p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-xs text-slate-500 uppercase">
                    <th class="text-left px-3 py-2"><?= $t('admin.ai.col_time') ?></th><th class="text-left px-3 py-2"><?= $t('admin.ai.col_user') ?></th>
                    <th class="text-left px-3 py-2"><?= $t('admin.ai.col_scene') ?></th><th class="text-left px-3 py-2"><?= $t('admin.ai.col_model') ?></th>
                    <th class="text-right px-3 py-2"><?= $t('admin.ai.col_tokens') ?></th><th class="text-right px-3 py-2"><?= $t('admin.ai.col_ms') ?></th>
                    <th class="text-center px-3 py-2"><?= $t('admin.ai.col_status') ?></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($recentLogs as $log): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-500 whitespace-nowrap"><?= date('m-d H:i', strtotime($log['created_at'])) ?></td>
                        <td class="px-3 py-2 text-slate-700"><?= htmlspecialchars($log['username'] ?? '—') ?></td>
                        <td class="px-3 py-2"><span class="badge badge-info"><?= htmlspecialchars($log['scene'] ?? '') ?></span></td>
                        <td class="px-3 py-2 text-slate-500 font-mono text-xs"><?= htmlspecialchars($log['model'] ?? '') ?></td>
                        <td class="px-3 py-2 text-right text-slate-600"><?= number_format($log['total_tokens']) ?></td>
                        <td class="px-3 py-2 text-right text-slate-600"><?= number_format($log['response_time']) ?></td>
                        <td class="px-3 py-2 text-center">
                            <span class="badge <?= $log['status'] === 'success' ? 'badge-success' : 'badge-danger' ?>"><?= $log['status'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const AI_I18N = {
    config_saved: <?= json_encode($t('admin.ai.config_saved')) ?>,
    prompt_saved: <?= json_encode($t('admin.ai.prompt_saved')) ?>,
    model_saved: <?= json_encode($t('admin.ai.model_saved')) ?>,
    model_deleted: <?= json_encode($t('admin.ai.model_deleted')) ?>,
    model_confirm_delete: <?= json_encode($t('admin.ai.model_confirm_delete')) ?>,
    model_required: <?= json_encode($t('admin.ai.model_required')) ?>,
    add_model: <?= json_encode($t('admin.ai.add_model')) ?>,
    edit_model: <?= json_encode($t('admin.ai.edit_model')) ?>
};
const AiSettings = {
    async saveProvider() {
        const data = {
            base_url: document.getElementById('ai-base-url').value.trim(),
            api_key: document.getElementById('ai-api-key').value,
            default_model: document.getElementById('ai-model').value.trim(),
            timeout: parseInt(document.getElementById('ai-timeout').value) || 30,
            temperature: parseFloat(document.getElementById('ai-temp').value) || 0.7,
            max_tokens: parseInt(document.getElementById('ai-tokens').value) || 2000,
        };
        try {
            await QS.fetch('/admin/ai/provider', { method: 'POST', body: data });
            QS.toast(AI_I18N.config_saved);
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async savePrompt(id) {
        const data = {
            system_prompt: document.getElementById(`prompt-sys-${id}`).value,
            user_prompt_template: document.getElementById(`prompt-user-${id}`).value,
            status: document.getElementById(`prompt-status-${id}`).checked ? 1 : 0,
        };
        try {
            await QS.fetch(`/admin/ai/prompts/${id}`, { method: 'POST', body: data });
            QS.toast(AI_I18N.prompt_saved);
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    openModelModal(m = null) {
        document.getElementById('model-modal-title').textContent = m ? AI_I18N.edit_model : AI_I18N.add_model;
        document.getElementById('model-id').value = m ? m.id : '';
        document.getElementById('model-name').value = m ? m.name : '';
        document.getElementById('model-model-id').value = m ? m.model_id : '';
        document.getElementById('model-provider').value = m ? (m.provider || 'openai') : 'openai';
        document.getElementById('model-sort').value = m ? (m.sort_order || 0) : 0;
        document.getElementById('model-desc').value = m ? (m.description || '') : '';
        let roles = '';
        if (m && m.allowed_roles) {
            try { const arr = JSON.parse(m.allowed_roles); roles = Array.isArray(arr) ? arr.join(',') : m.allowed_roles; }
            catch(e) { roles = m.allowed_roles; }
        }
        document.getElementById('model-roles').value = roles;
        document.getElementById('model-modal').classList.remove('hidden');
    },

    async saveModel() {
        const id = document.getElementById('model-id').value;
        const data = {
            name: document.getElementById('model-name').value.trim(),
            model_id: document.getElementById('model-model-id').value.trim(),
            provider: document.getElementById('model-provider').value.trim(),
            sort_order: parseInt(document.getElementById('model-sort').value) || 0,
            description: document.getElementById('model-desc').value.trim(),
            allowed_roles: document.getElementById('model-roles').value.trim(),
        };
        if (!data.name || !data.model_id) { QS.toast(AI_I18N.model_required, 'error'); return; }
        try {
            const url = id ? `/admin/ai/models/${id}/update` : '/admin/ai/models';
            await QS.fetch(url, { method: 'POST', body: data });
            QS.toast(AI_I18N.model_saved);
            location.reload();
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async toggleModel(id) {
        try {
            await QS.fetch(`/admin/ai/models/${id}/toggle`, { method: 'POST' });
            location.reload();
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async deleteModel(id, name) {
        if (!await QS.confirm(AI_I18N.model_confirm_delete.replace(':name', name))) return;
        try {
            await QS.fetch(`/admin/ai/models/${id}/delete`, { method: 'POST' });
            QS.toast(AI_I18N.model_deleted);
            document.getElementById(`model-row-${id}`)?.remove();
        } catch(e) { QS.toast(e.message, 'error'); }
    }
};
</script>
