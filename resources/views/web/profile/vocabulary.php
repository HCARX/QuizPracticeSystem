<!-- Vocabulary Page -->
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?= $t('vocab.title') ?></h1>
            <p class="text-sm text-slate-500 mt-1"><?= $t('vocab.subtitle') ?></p>
        </div>
        <button onclick="VocabPage.openAddModal()" class="btn-primary">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <?= $t('vocab.add_word') ?>
        </button>
    </div>

    <!-- Status Tabs -->
    <div class="flex gap-2">
        <?php
        $tabs = [
            '' => $t('vocab.tab_all') . " ({$counts['all']})",
            'unseen' => $t('vocab.tab_unseen') . " ({$counts['unseen']})",
            'fuzzy' => $t('vocab.tab_fuzzy') . " ({$counts['fuzzy']})",
            'mastered' => $t('vocab.tab_mastered') . " ({$counts['mastered']})"
        ];
        foreach ($tabs as $sv => $sl): ?>
        <a href="?status=<?= $sv ?>" class="px-4 py-2 rounded-full text-sm font-medium transition-all <?= $filters['status'] === $sv ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-300' ?>"><?= $sl ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Word Cards -->
    <?php if (empty($words)): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('vocab.empty_title') ?></h3>
        <p class="text-sm text-slate-400"><?= $t('vocab.empty_desc') ?></p>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 gap-4">
        <?php foreach ($words as $w):
            $meaning = json_decode($w['meaning_json'] ?? '{}', true);
            $statusColors = ['unseen' => 'bg-slate-100 text-slate-600', 'fuzzy' => 'bg-amber-100 text-amber-700', 'mastered' => 'bg-emerald-100 text-emerald-700'];
        ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-sm transition-shadow" id="word-<?= $w['id'] ?>">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($w['word']) ?></h3>
                    <?php if ($w['phonetic']): ?>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($w['phonetic']) ?></p>
                    <?php endif; ?>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColors[$w['status']] ?? $statusColors['unseen'] ?>"><?= $t('vocab.status.' . $w['status']) ?></span>
            </div>

            <?php if (!empty($meaning['definition'])): ?>
            <p class="text-sm text-slate-700 mb-2"><?= htmlspecialchars($meaning['definition']) ?></p>
            <?php endif; ?>

            <?php if ($w['context_sentence']): ?>
            <p class="text-xs text-slate-500 italic mb-3">"<?= htmlspecialchars(mb_substr($w['context_sentence'], 0, 120)) ?>"</p>
            <?php endif; ?>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="flex gap-1">
                    <?php foreach (['unseen','fuzzy','mastered'] as $st): ?>
                    <button onclick="VocabPage.updateStatus(<?= $w['id'] ?>, '<?= $st ?>')"
                            class="px-2 py-1 text-xs rounded-md transition-colors <?= $w['status'] === $st ? 'bg-primary-100 text-primary-700 font-medium' : 'text-slate-400 hover:bg-slate-100' ?>"><?= $t('vocab.status.' . $st) ?></button>
                    <?php endforeach; ?>
                </div>
                <button onclick="VocabPage.deleteWord(<?= $w['id'] ?>)" class="text-slate-300 hover:text-red-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Add Word Modal -->
<div id="add-word-modal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900"><?= $t('vocab.add_word') ?></h3>
            <button onclick="document.getElementById('add-word-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <div><label class="form-label"><?= $t('vocab.word') ?> <span class="text-red-500">*</span></label><input type="text" id="new-word" class="form-input" placeholder="<?= $t('vocab.word_ph') ?>"></div>
            <div><label class="form-label"><?= $t('vocab.meaning') ?></label><input type="text" id="new-meaning" class="form-input" placeholder="<?= $t('vocab.meaning_ph') ?>"></div>
            <div><label class="form-label"><?= $t('vocab.context') ?></label><textarea id="new-sentence" class="form-input" rows="2" placeholder="<?= $t('vocab.context_ph') ?>"></textarea></div>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
            <button onclick="document.getElementById('add-word-modal').classList.add('hidden')" class="btn-secondary"><?= $t('common.cancel') ?></button>
            <button onclick="VocabPage.saveWord()" class="btn-primary"><?= $t('vocab.save') ?></button>
        </div>
    </div>
</div>

<script>
const VOCAB_I18N = {
    word_required: <?= json_encode($t('vocab.word_required')) ?>,
    added: <?= json_encode($t('vocab.added')) ?>,
    marked_as: <?= json_encode($t('vocab.marked_as')) ?>,
    confirm_remove: <?= json_encode($t('vocab.confirm_remove')) ?>,
    removed: <?= json_encode($t('vocab.removed')) ?>,
    status: {
        unseen: <?= json_encode($t('vocab.status.unseen')) ?>,
        fuzzy: <?= json_encode($t('vocab.status.fuzzy')) ?>,
        mastered: <?= json_encode($t('vocab.status.mastered')) ?>
    }
};
const VocabPage = {
    openAddModal() { document.getElementById('add-word-modal').classList.remove('hidden'); document.getElementById('new-word').focus(); },

    async saveWord() {
        const word = document.getElementById('new-word').value.trim();
        if (!word) { QS.toast(VOCAB_I18N.word_required, 'error'); return; }
        try {
            await QS.fetch('/vocabulary', { method: 'POST', body: {
                word, meaning: document.getElementById('new-meaning').value.trim(),
                sentence: document.getElementById('new-sentence').value.trim()
            }});
            QS.toast(VOCAB_I18N.added);
            setTimeout(() => location.reload(), 500);
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async updateStatus(id, status) {
        try {
            await QS.fetch(`/vocabulary/${id}/update`, { method: 'POST', body: { status } });
            QS.toast(VOCAB_I18N.marked_as.replace(':status', VOCAB_I18N.status[status] || status));
            setTimeout(() => location.reload(), 400);
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    async deleteWord(id) {
        if (!await QS.confirm(VOCAB_I18N.confirm_remove)) return;
        try {
            await QS.fetch(`/vocabulary/${id}/delete`, { method: 'POST' });
            document.getElementById(`word-${id}`)?.remove();
            QS.toast(VOCAB_I18N.removed);
        } catch(e) { QS.toast(e.message, 'error'); }
    }
};
</script>
