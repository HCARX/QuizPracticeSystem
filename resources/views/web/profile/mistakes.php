<!-- Mistakes Page -->
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900"><?= $t('mistakes.title') ?></h1>
            <p class="text-sm text-slate-500 mt-1"><?= $t('mistakes.count', ['n' => count($mistakes)]) ?></p>
        </div>
        <?php if (!empty($mistakes)): ?>
        <form method="POST" action="/practice/start" class="flex items-center gap-2">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <input type="hidden" name="source" value="mistakes">
            <input type="hidden" name="count" value="<?= min(20, count($mistakes)) ?>">
            <input type="hidden" name="shuffle" value="1">
            <button type="submit" class="btn-primary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <?= $t('mistakes.practice_these') ?>
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if (empty($mistakes)): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
        <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3 class="text-base font-semibold text-slate-600 mb-1"><?= $t('mistakes.empty_title') ?></h3>
        <p class="text-sm text-slate-400"><?= $t('mistakes.empty_desc') ?></p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($mistakes as $m):
            $content = json_decode($m['content_json'], true) ?: [];
            $stem = $content['stem'] ?? $content['passage'] ?? '';
        ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5" id="mistake-<?= $m['id'] ?>">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="badge badge-info text-xs"><?= ucwords(str_replace('_',' ',$m['type'])) ?></span>
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($m['paper_title'] ?? '') ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-danger"><?= $t('mistakes.wrong_times', ['n' => $m['wrong_count']]) ?></span>
                    <button onclick="MistakesPage.toggleMastered(<?= $m['id'] ?>, this)" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <?= $t('mistakes.mark_mastered') ?>
                    </button>
                </div>
            </div>

            <p class="text-sm text-slate-800 mb-3"><?= htmlspecialchars(mb_substr($stem, 0, 200)) ?></p>

            <?php if (!empty($content['options'])): ?>
            <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($content['options'] as $k => $v): ?>
                <span class="text-xs px-2 py-1 rounded-md <?= $k === $m['answer_json'] ? 'bg-emerald-50 text-emerald-700 font-medium' : 'bg-slate-50 text-slate-500' ?>">
                    <?= htmlspecialchars($k) ?>. <?= htmlspecialchars(mb_substr($v, 0, 40)) ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="flex items-center gap-4 text-sm pt-3 border-t border-slate-100">
                <div><span class="text-slate-500"><?= $t('mistakes.your_last') ?>: </span><span class="text-red-600 font-medium"><?= htmlspecialchars($m['last_wrong_answer'] ?? '') ?></span></div>
                <?php if ($m['answer_json']): ?>
                <div><span class="text-slate-500"><?= $t('mistakes.correct') ?>: </span><span class="text-emerald-600 font-medium"><?= htmlspecialchars($m['answer_json']) ?></span></div>
                <?php endif; ?>
            </div>

            <?php if ($m['analysis_json']): ?>
            <details class="mt-3">
                <summary class="text-xs font-medium text-primary-600 cursor-pointer"><?= $t('mistakes.view_explanation') ?></summary>
                <div class="mt-2 p-3 bg-slate-50 rounded-lg text-sm text-slate-700"><?= nl2br(htmlspecialchars(is_string($m['analysis_json']) ? $m['analysis_json'] : '')) ?></div>
            </details>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
const MistakesPage = {
    async toggleMastered(id, btn) {
        try {
            const res = await QS.fetch(`/mistakes/${id}/mastered`, { method: 'POST' });
            if (res.mastered) {
                const card = document.getElementById(`mistake-${id}`);
                card.style.transition = 'opacity .25s';
                card.style.opacity = '0';
                setTimeout(() => card.remove(), 250);
                QS.toast(<?= json_encode($t('mistakes.mastered_toast')) ?>);
            }
        } catch (e) { QS.toast(e.message, 'error'); }
    }
};
</script>
