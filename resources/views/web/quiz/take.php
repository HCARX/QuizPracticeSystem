<!-- Quiz Taking Engine -->
<?php
    $totalQ = 0;
    foreach ($questions as $q) {
        $ch = $childMap[$q['id']] ?? [];
        $totalQ += $ch ? count($ch) : 1;
    }
    $answeredCount = count($answers);
?>
<div class="min-h-screen bg-slate-50">
    <!-- Top Bar -->
    <div class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <a href="/" class="text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></a>
                <div class="min-w-0">
                    <h1 class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars($session['paper_title']) ?></h1>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($session['subject_name'] ?? '') ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="timer">00:00:00</span>
                </div>
                <div class="hidden sm:flex items-center gap-2 text-sm">
                    <span class="text-slate-500"><?= $t('quiz.progress_label') ?></span>
                    <span id="progress-text" class="font-medium text-primary-600">0/<?= $totalQ ?></span>
                </div>
                <button onclick="QuizEngine.confirmSubmit()" class="btn-primary text-sm px-4 py-1.5"><?= $t('quiz.submit') ?></button>
            </div>
        </div>
        <!-- Progress bar -->
        <div class="h-0.5 bg-slate-100"><div id="progress-bar" class="h-full bg-primary-500 transition-all duration-300" style="width:0%"></div></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 flex gap-6">
        <!-- Navigator Sidebar -->
        <aside class="hidden lg:block w-56 flex-shrink-0">
            <div class="sticky top-24 bg-white rounded-xl border border-slate-200 p-4">
                <h3 class="text-xs font-semibold text-slate-500 uppercase mb-3"><?= $t('quiz.questions') ?></h3>
                <div id="nav-grid" class="grid grid-cols-5 gap-1.5">
                    <?php
                    $qNum = 0;
                    foreach ($questions as $q) {
                        $ch = $childMap[$q['id']] ?? [];
                        $items = $ch ?: [$q];
                        foreach ($items as $item) {
                            $qNum++;
                            $qid = $item['id'];
                            $answered = isset($answers[(string) $qid]);
                            $cls = $answered ? 'bg-primary-100 text-primary-700 border-primary-200' : 'bg-white text-slate-500 border-slate-200';
                    ?>
                    <button onclick="QuizEngine.goTo(<?= $qid ?>)"
                            class="nav-btn w-8 h-8 text-xs font-medium rounded-lg border <?= $cls ?> hover:border-primary-400 transition-colors flex items-center justify-center"
                            data-qid="<?= $qid ?>"><?= $qNum ?></button>
                    <?php } } ?>
                </div>
                <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-primary-100 border border-primary-200"></span> <?= $t('quiz.answered') ?></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-white border border-slate-200"></span> <?= $t('quiz.unanswered_label') ?></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-100 border border-amber-300"></span> <?= $t('quiz.flagged') ?></div>
                </div>
            </div>
        </aside>

        <!-- Question Area -->
        <main class="flex-1 min-w-0 space-y-6">
            <?php
            $qNum = 0;
            foreach ($questions as $qi => $q):
                $qContent = json_decode($q['content_json'], true) ?: [];
                $ch = $childMap[$q['id']] ?? [];
                $isMaterial = in_array($q['type'], ['reading_material', 'listening_material']);
            ?>

            <?php if ($isMaterial): ?>
            <!-- Material + Sub-Questions Block -->
            <div class="card overflow-hidden" id="material-<?= $q['id'] ?>">
                <!-- Material Header & Content -->
                <div class="p-6 bg-slate-50 border-b border-slate-200">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="badge badge-info"><?= $q['type'] === 'reading_material' ? $t('quiz.reading') : $t('quiz.listening') ?></span>
                        <?php if (!empty($qContent['audio_url'])): ?>
                        <audio controls class="h-8 ml-2"><source src="<?= htmlspecialchars($qContent['audio_url']) ?>"></audio>
                        <?php endif; ?>
                    </div>
                    <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed quiz-text">
                        <?= nl2br(htmlspecialchars($qContent['passage'] ?? $qContent['stem'] ?? '')) ?>
                    </div>
                </div>
                <!-- Sub Questions -->
                <div class="divide-y divide-slate-100">
                    <?php foreach ($ch as $child):
                        $qNum++;
                        $ccontent = json_decode($child['content_json'], true) ?: [];
                        $existingAns = $answers[(string)$child['id']]['answer'] ?? null;
                    ?>
                    <div class="p-6 question-block" id="question-<?= $child['id'] ?>" data-qid="<?= $child['id'] ?>">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="w-7 h-7 bg-primary-50 text-primary-700 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $qNum ?></span>
                            <p class="text-sm text-slate-800 leading-relaxed quiz-text"><?= htmlspecialchars($ccontent['stem'] ?? '') ?></p>
                        </div>
                        <?php if (!empty($ccontent['options'])): ?>
                        <div class="ml-10 space-y-2">
                            <?php foreach ($ccontent['options'] as $key => $val): ?>
                            <label class="option-label flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all <?= $existingAns === $key ? 'border-primary-400 bg-primary-50' : '' ?>"
                                   data-qid="<?= $child['id'] ?>" data-key="<?= htmlspecialchars($key) ?>">
                                <input type="<?= $child['type'] === 'multi_choice' ? 'checkbox' : 'radio' ?>"
                                       name="q_<?= $child['id'] ?>" value="<?= htmlspecialchars($key) ?>"
                                       class="mt-0.5 text-primary-600" <?= $existingAns === $key ? 'checked' : '' ?>
                                       onchange="QuizEngine.onAnswer(<?= $child['id'] ?>, '<?= htmlspecialchars($key) ?>', '<?= $child['type'] ?>')">
                                <span class="text-sm"><strong class="text-slate-500 mr-1"><?= htmlspecialchars($key) ?>.</strong> <span class="text-slate-700 quiz-text"><?= htmlspecialchars($val) ?></span></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <?php elseif (in_array($child['type'], ['fill_blank','short_answer'])): ?>
                        <div class="ml-10">
                            <input type="text" class="form-input" placeholder="<?= $t('quiz.type_answer') ?>"
                                   value="<?= htmlspecialchars($existingAns ?? '') ?>"
                                   onchange="QuizEngine.onAnswer(<?= $child['id'] ?>, this.value, '<?= $child['type'] ?>')">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php else: ?>
            <!-- Standalone Question -->
            <?php
                $qNum++;
                $existingAns = $answers[(string)$q['id']]['answer'] ?? null;
            ?>
            <div class="card p-6 question-block" id="question-<?= $q['id'] ?>" data-qid="<?= $q['id'] ?>">
                <div class="flex items-start gap-3 mb-4">
                    <span class="w-7 h-7 bg-primary-50 text-primary-700 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $qNum ?></span>
                    <div>
                        <span class="badge badge-gray text-xs mb-2"><?= ucwords(str_replace('_',' ',$q['type'])) ?></span>
                        <p class="text-sm text-slate-800 leading-relaxed quiz-text"><?= htmlspecialchars($qContent['stem'] ?? $qContent['instructions'] ?? '') ?></p>
                    </div>
                </div>

                <?php if (!empty($qContent['options'])): ?>
                <div class="ml-10 space-y-2">
                    <?php foreach ($qContent['options'] as $key => $val): ?>
                    <label class="option-label flex items-start gap-3 p-3 rounded-lg border border-slate-200 hover:border-primary-300 hover:bg-primary-50/30 cursor-pointer transition-all <?= $existingAns === $key ? 'border-primary-400 bg-primary-50' : '' ?>"
                           data-qid="<?= $q['id'] ?>" data-key="<?= htmlspecialchars($key) ?>">
                        <input type="<?= $q['type'] === 'multi_choice' ? 'checkbox' : 'radio' ?>"
                               name="q_<?= $q['id'] ?>" value="<?= htmlspecialchars($key) ?>"
                               class="mt-0.5 text-primary-600" <?= $existingAns === $key ? 'checked' : '' ?>
                               onchange="QuizEngine.onAnswer(<?= $q['id'] ?>, '<?= htmlspecialchars($key) ?>', '<?= $q['type'] ?>')">
                        <span class="text-sm"><strong class="text-slate-500 mr-1"><?= htmlspecialchars($key) ?>.</strong> <span class="text-slate-700 quiz-text"><?= htmlspecialchars($val) ?></span></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php elseif (in_array($q['type'], ['fill_blank','short_answer','translation'])): ?>
                <div class="ml-10">
                    <input type="text" class="form-input" placeholder="<?= $t('quiz.type_answer') ?>"
                           value="<?= htmlspecialchars($existingAns ?? '') ?>"
                           onchange="QuizEngine.onAnswer(<?= $q['id'] ?>, this.value, '<?= $q['type'] ?>')">
                </div>
                <?php elseif ($q['type'] === 'writing'): ?>
                <div class="ml-10">
                    <textarea class="form-input" rows="8" placeholder="<?= $t('quiz.write_essay') ?>"
                              onchange="QuizEngine.onAnswer(<?= $q['id'] ?>, this.value, 'writing')"><?= htmlspecialchars($existingAns ?? '') ?></textarea>
                    <p class="text-xs text-slate-400 mt-1 text-right"><?= $t('quiz.word_count') ?> <span id="wc-<?= $q['id'] ?>">0</span></p>
                </div>
                <?php elseif ($q['type'] === 'true_false'): ?>
                <div class="ml-10 flex gap-3">
                    <?php foreach (['true' => $t('quiz.true'), 'false' => $t('quiz.false')] as $tv => $tl): ?>
                    <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border cursor-pointer transition-all <?= $existingAns === $tv ? 'border-primary-400 bg-primary-50' : 'border-slate-200 hover:border-primary-300' ?>">
                        <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $tv ?>" class="text-primary-600"
                               <?= $existingAns === $tv ? 'checked' : '' ?>
                               onchange="QuizEngine.onAnswer(<?= $q['id'] ?>, '<?= $tv ?>', 'true_false')">
                        <span class="text-sm font-medium text-slate-700"><?= $tl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>

            <!-- Submit -->
            <div class="text-center py-8">
                <button onclick="QuizEngine.confirmSubmit()" class="btn-primary px-8 py-3 text-base"><?= $t('quiz.submit_exam') ?></button>
            </div>
        </main>
    </div>
</div>

<style>
.quiz-text { word-break: break-word; }
.option-label.selected { border-color: #818CF8; background: #EEF2FF; }
.prose { max-width: 65ch; }
</style>

<script>
const QuizEngine = {
    sessionId: <?= $session['id'] ?>,
    totalQ: <?= $totalQ ?>,
    answeredSet: new Set(<?= json_encode(array_keys($answers)) ?>),
    startTime: new Date('<?= $session['started_at'] ?>').getTime(),
    duration: <?= (int)($session['duration'] ?? 0) ?> * 60,
    saveQueue: {},
    saveTimer: null,

    init() {
        this.updateProgress();
        this.startTimer();
        this.setupAutoSave();
    },

    onAnswer(qid, value, type) {
        this.answeredSet.add(String(qid));
        this.updateProgress();
        this.updateNavBtn(qid, true);
        this.highlightSelected(qid, value, type);
        this.queueSave(qid, value);
    },

    highlightSelected(qid, value, type) {
        if (type === 'multi_choice') return;
        document.querySelectorAll(`.option-label[data-qid="${qid}"]`).forEach(el => {
            el.classList.toggle('border-primary-400', el.dataset.key === value);
            el.classList.toggle('bg-primary-50', el.dataset.key === value);
            el.classList.toggle('border-slate-200', el.dataset.key !== value);
        });
    },

    queueSave(qid, value) {
        this.saveQueue[qid] = value;
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => this.flushSaves(), 800);
    },

    async flushSaves() {
        const queue = { ...this.saveQueue };
        this.saveQueue = {};
        for (const [qid, answer] of Object.entries(queue)) {
            try {
                await QS.fetch(`/quiz/${this.sessionId}/save-answer`, {
                    method: 'POST',
                    body: { question_id: qid, answer }
                });
            } catch (e) { console.error('Save failed', e); }
        }
    },

    setupAutoSave() {
        setInterval(() => { if (Object.keys(this.saveQueue).length) this.flushSaves(); }, 5000);
    },

    updateProgress() {
        const count = this.answeredSet.size;
        document.getElementById('progress-text').textContent = `${count}/${this.totalQ}`;
        document.getElementById('progress-bar').style.width = `${(count / this.totalQ) * 100}%`;
    },

    updateNavBtn(qid, answered) {
        const btn = document.querySelector(`.nav-btn[data-qid="${qid}"]`);
        if (!btn) return;
        if (answered) {
            btn.classList.add('bg-primary-100', 'text-primary-700', 'border-primary-200');
            btn.classList.remove('bg-white', 'text-slate-500', 'border-slate-200');
        }
    },

    goTo(qid) {
        const el = document.getElementById(`question-${qid}`);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    startTimer() {
        const update = () => {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const el = document.getElementById('timer');
            if (this.duration > 0) {
                const remaining = Math.max(0, this.duration - elapsed);
                const h = String(Math.floor(remaining / 3600)).padStart(2, '0');
                const m = String(Math.floor((remaining % 3600) / 60)).padStart(2, '0');
                const s = String(remaining % 60).padStart(2, '0');
                el.textContent = `${h}:${m}:${s}`;
                if (remaining <= 300 && remaining > 0) el.classList.add('text-amber-600','font-semibold');
                if (remaining <= 60) { el.classList.remove('text-amber-600'); el.classList.add('text-red-600','animate-pulse'); }
                if (remaining === 0 && !this.autoSubmitted) {
                    this.autoSubmitted = true;
                    QS.toast(<?= json_encode($t('quiz.time_up')) ?>, 'info');
                    this.autoSubmit();
                }
            } else {
                const h = String(Math.floor(elapsed / 3600)).padStart(2, '0');
                const m = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
                const s = String(elapsed % 60).padStart(2, '0');
                el.textContent = `${h}:${m}:${s}`;
            }
        };
        update();
        setInterval(update, 1000);
    },

    async autoSubmit() {
        await this.flushSaves();
        try {
            const res = await QS.fetch(`/quiz/${this.sessionId}/submit`, { method: 'POST' });
            if (res.redirect) window.location.href = res.redirect;
        } catch (e) { QS.toast(e.message, 'error'); }
    },

    async confirmSubmit() {
        await this.flushSaves();
        const unanswered = this.totalQ - this.answeredSet.size;
        let msg = <?= json_encode($t('quiz.confirm_submit')) ?>;
        if (unanswered > 0) msg += '\n\n' + <?= json_encode($t('quiz.unanswered')) ?>.replace(':count', unanswered);
        if (!await QS.confirm(msg)) return;
        try {
            const res = await QS.fetch(`/quiz/${this.sessionId}/submit`, { method: 'POST' });
            if (res.redirect) window.location.href = res.redirect;
        } catch (e) { QS.toast(e.message, 'error'); }
    }
};

document.addEventListener('DOMContentLoaded', () => QuizEngine.init());
</script>
