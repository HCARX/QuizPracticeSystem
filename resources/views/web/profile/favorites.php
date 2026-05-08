<!-- Favorites Page -->
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900"><?= $t('fav.title') ?></h1>
        <p class="text-sm text-slate-500 mt-1"><?= $t('fav.count', ['n' => count($favorites)]) ?></p>
    </div>

    <?php if (empty($favorites)): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('fav.empty_title') ?></h3>
        <p class="text-sm text-slate-400"><?= $t('fav.empty_desc') ?></p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($favorites as $f):
            $content = json_decode($f['content_json'], true) ?: [];
            $stem = $content['stem'] ?? $content['passage'] ?? '';
        ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5" id="fav-<?= $f['id'] ?>">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="badge badge-info text-xs"><?= ucwords(str_replace('_',' ',$f['type'])) ?></span>
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($f['paper_title'] ?? '') ?></span>
                </div>
                <button onclick="FavPage.remove(<?= $f['question_id'] ?>, <?= $f['id'] ?>)" class="text-amber-400 hover:text-slate-400 transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                </button>
            </div>
            <p class="text-sm text-slate-800"><?= htmlspecialchars(mb_substr($stem, 0, 300)) ?></p>

            <?php if ($f['answer_json']): ?>
            <div class="mt-3 text-sm"><span class="text-slate-500"><?= $t('fav.answer') ?>: </span><span class="text-emerald-700 font-medium"><?= htmlspecialchars($f['answer_json']) ?></span></div>
            <?php endif; ?>

            <?php if ($f['analysis_json']): ?>
            <details class="mt-3">
                <summary class="text-xs font-medium text-primary-600 cursor-pointer"><?= $t('fav.view_explanation') ?></summary>
                <div class="mt-2 p-3 bg-slate-50 rounded-lg text-sm text-slate-700"><?= nl2br(htmlspecialchars(is_string($f['analysis_json']) ? $f['analysis_json'] : '')) ?></div>
            </details>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
const FavPage = {
    async remove(questionId, rowId) {
        try {
            await QS.fetch('/favorites/toggle', { method: 'POST', body: { question_id: questionId } });
            document.getElementById(`fav-${rowId}`)?.remove();
            QS.toast(<?= json_encode($t('fav.removed_toast')) ?>);
        } catch(e) { QS.toast(e.message, 'error'); }
    }
};
</script>
