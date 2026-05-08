<!-- Quiz Results Page -->
<?php
    $pct = $session['accuracy'] ?? 0;
    $pctColor = $pct >= 80 ? 'text-emerald-600' : ($pct >= 60 ? 'text-amber-600' : 'text-red-600');
    $timeMin = floor(($session['time_spent'] ?? 0) / 60);
    $timeSec = ($session['time_spent'] ?? 0) % 60;
?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Score Card -->
    <div class="card p-8 text-center mb-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-1"><?= htmlspecialchars($session['paper_title']) ?></h2>
        <p class="text-sm text-slate-500 mb-6"><?= htmlspecialchars($session['subject_name'] ?? '') ?></p>

        <div class="flex items-center justify-center gap-12 mb-8">
            <div>
                <p class="text-5xl font-bold <?= $pctColor ?>"><?= number_format($session['total_score'], 1) ?></p>
                <p class="text-sm text-slate-400 mt-1"><?= $t('result.score_label', ['total' => number_format((float)$session['paper_total_score'], 1)]) ?></p>
            </div>
            <div class="w-px h-16 bg-slate-200"></div>
            <div>
                <p class="text-5xl font-bold <?= $pctColor ?>"><?= $pct ?>%</p>
                <p class="text-sm text-slate-400 mt-1"><?= $t('result.accuracy') ?></p>
            </div>
        </div>

        <div class="flex items-center justify-center gap-8 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-slate-600"><?= $t('result.correct') ?>: <strong><?= $session['correct_count'] ?></strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-slate-600"><?= $t('result.wrong') ?>: <strong><?= $session['wrong_count'] ?></strong></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-slate-300"></span>
                <span class="text-slate-600"><?= $t('result.unanswered') ?>: <strong><?= $session['unanswered_count'] ?></strong></span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-slate-600"><?= $timeMin ?>m <?= $timeSec ?>s</span>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-4">
            <a href="/" class="btn-secondary"><?= $t('result.back_home') ?></a>
            <a href="/quiz/<?= $session['paper_id'] ?>/start" class="btn-primary"><?= $t('common.retake') ?></a>
        </div>
    </div>

    <!-- Answer Review -->
    <div class="space-y-4">
        <h3 class="text-base font-semibold text-slate-900"><?= $t('result.review') ?></h3>

        <?php foreach ($userAnswers as $ua):
            if ($ua['parent_id']) continue;
            $content = json_decode($ua['content_json'], true) ?: [];
            $stem = $content['stem'] ?? $content['passage'] ?? $content['instructions'] ?? '';
            $isCorrect = (bool) $ua['is_correct'];
            $border = $isCorrect ? 'border-emerald-200' : ($ua['user_answer'] === '' ? 'border-slate-200' : 'border-red-200');
            $bg = $isCorrect ? 'bg-emerald-50/50' : ($ua['user_answer'] === '' ? '' : 'bg-red-50/30');
        ?>
        <div class="card p-5 <?= $border ?> <?= $bg ?>">
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 <?= $isCorrect ? 'bg-emerald-100' : 'bg-red-100' ?>">
                    <?php if ($isCorrect): ?>
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <?php else: ?>
                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-info text-xs"><?= ucwords(str_replace('_',' ',$ua['type'])) ?></span>
                        <span class="text-xs text-slate-400"><?= $t('result.pts', ['n' => $ua['score']]) ?></span>
                    </div>
                    <p class="text-sm text-slate-800 mb-3"><?= htmlspecialchars(mb_substr($stem, 0, 200)) ?><?= mb_strlen($stem) > 200 ? '...' : '' ?></p>

                    <div class="flex flex-wrap gap-4 text-sm">
                        <div>
                            <span class="text-slate-500"><?= $t('result.your_answer') ?>: </span>
                            <span class="font-medium <?= $isCorrect ? 'text-emerald-700' : 'text-red-700' ?>"><?= htmlspecialchars($ua['user_answer'] ?: $t('result.empty_answer')) ?></span>
                        </div>
                        <?php if (!$isCorrect && $ua['correct_answer']): ?>
                        <div>
                            <span class="text-slate-500"><?= $t('result.correct_answer') ?>: </span>
                            <span class="font-medium text-emerald-700"><?= htmlspecialchars($ua['correct_answer']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php
                        $analysisData = json_decode($ua['analysis_json'] ?? '', true);
                        $analysisText = is_array($analysisData) ? ($analysisData['text'] ?? json_encode($analysisData, JSON_UNESCAPED_UNICODE)) : ($ua['analysis_json'] ?? '');
                        if ($analysisText):
                    ?>
                    <details class="mt-3">
                        <summary class="text-xs font-medium text-primary-600 cursor-pointer hover:text-primary-700"><?= $t('result.view_explanation') ?></summary>
                        <div class="mt-2 p-3 bg-slate-50 rounded-lg text-sm text-slate-700 leading-relaxed"><?= nl2br(htmlspecialchars($analysisText)) ?></div>
                    </details>
                    <?php endif; ?>

                    <!-- AI Analysis Button -->
                    <div class="mt-3 flex items-center gap-4">
                        <button onclick="ResultPage.aiAnalyze(<?= $ua['question_id'] ?>, this)"
                                class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <?= $t('result.ai_analysis') ?>
                        </button>
                        <button onclick="ResultPage.toggleFav(<?= $ua['question_id'] ?>, this)"
                                class="fav-btn text-xs text-slate-500 hover:text-amber-600 font-medium flex items-center gap-1"
                                data-qid="<?= $ua['question_id'] ?>">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            <span><?= $t('result.favorite') ?></span>
                        </button>
                        <div id="ai-result-<?= $ua['question_id'] ?>" class="hidden"></div>
                    </div>
                    <div id="ai-panel-<?= $ua['question_id'] ?>" class="hidden mt-2 p-4 bg-primary-50 rounded-lg text-sm text-slate-700 leading-relaxed"></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
const RESULT_I18N = {
    analyzing: <?= json_encode($t('result.analyzing')) ?>,
    no_analysis: <?= json_encode($t('result.no_analysis')) ?>,
    favorite: <?= json_encode($t('result.favorite')) ?>,
    favorited: <?= json_encode($t('result.favorited')) ?>
};
const ResultPage = {
    async aiAnalyze(questionId, btn) {
        const container = document.getElementById(`ai-panel-${questionId}`);
        if (!container.classList.contains('hidden')) {
            container.classList.add('hidden');
            return;
        }
        const originalHtml = btn.innerHTML;
        btn.textContent = RESULT_I18N.analyzing;
        btn.disabled = true;
        try {
            const res = await QS.fetch('/api/ai/analyze-question', {
                method: 'POST',
                body: { question_id: questionId }
            });
            container.innerHTML = res.analysis ? res.analysis.replace(/\n/g, '<br>') : RESULT_I18N.no_analysis;
            container.classList.remove('hidden');
        } catch (e) {
            QS.toast(e.message, 'error');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    },

    async toggleFav(questionId, btn) {
        try {
            const res = await QS.fetch('/favorites/toggle', {
                method: 'POST',
                body: { question_id: questionId }
            });
            const svg = btn.querySelector('svg');
            const label = btn.querySelector('span');
            if (res.favorited) {
                svg.setAttribute('fill', 'currentColor');
                btn.classList.add('text-amber-600');
                btn.classList.remove('text-slate-500');
                label.textContent = RESULT_I18N.favorited;
            } else {
                svg.setAttribute('fill', 'none');
                btn.classList.remove('text-amber-600');
                btn.classList.add('text-slate-500');
                label.textContent = RESULT_I18N.favorite;
            }
        } catch (e) { QS.toast(e.message, 'error'); }
    }
};
</script>
