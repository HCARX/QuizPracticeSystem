<?php
$total = count($slots);
// Pre-encode existing content for JS
$slotsForJs = array_map(function ($s) {
    return [
        'index' => $s['index'],
        'section_key' => $s['section_key'],
        'section_title' => $s['section_title'],
        'block_title' => $s['block_title'],
        'block_type' => $s['block_type'],
        'slot_type' => $s['slot_type'],
        'child_type' => $s['child_type'] ?? null,
        'parent_slot_index' => $s['parent_slot_index'] ?? null,
        'sort_order' => $s['sort_order'],
        'score' => $s['score'],
        'question_id' => $s['question_id'] ?? null,
        'content_json' => $s['content_json'] ?? null,
        'answer_json' => $s['answer_json'] ?? null,
        'analysis_json' => $s['analysis_json'] ?? null,
    ];
}, $slots);

// group by section for sidebar
$grouped = [];
foreach ($slots as $s) {
    $grouped[$s['section_title']][] = $s;
}
?>
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="/admin/papers/<?= (int)$paper['id'] ?>/fill" class="text-sm text-slate-500 hover:text-primary-600">← <?= $t('common.back') ?></a>
            <h1 class="text-xl font-semibold text-slate-900 mt-1"><?= $t('admin.fill.title') ?></h1>
            <p class="text-sm text-slate-500 mt-1">
                <?= htmlspecialchars($paper['title']) ?> · <span class="text-primary-600"><?= htmlspecialchars($template['name']) ?></span>
            </p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-400"><?= $t('admin.fill.progress') ?></p>
            <p class="text-sm font-semibold text-slate-800"><span id="fillProgress">0</span> / <?= $total ?></p>
            <div class="w-40 h-1.5 bg-slate-200 rounded-full mt-1 overflow-hidden">
                <div id="fillProgressBar" class="h-full bg-primary-600 transition-all" style="width:0%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4">
        <!-- Sidebar -->
        <aside class="col-span-12 md:col-span-3 card p-3 max-h-[70vh] overflow-y-auto">
            <?php foreach ($grouped as $secTitle => $items): ?>
            <div class="mb-3">
                <p class="text-xs font-semibold text-slate-500 uppercase mb-1"><?= htmlspecialchars($secTitle) ?></p>
                <div class="space-y-1">
                    <?php foreach ($items as $s): ?>
                    <button type="button" data-slot-btn="<?= $s['index'] ?>"
                        class="slot-link w-full flex items-center justify-between text-left px-2 py-1.5 rounded-lg text-sm hover:bg-slate-100">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="text-slate-400 w-6 text-right"><?= $s['index'] + 1 ?>.</span>
                            <span class="truncate text-slate-700"><?= htmlspecialchars($s['block_type']) ?><?= $s['slot_type'] === 'child' ? ' ·sub' : '' ?></span>
                        </span>
                        <span data-slot-dot="<?= $s['index'] ?>" class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </aside>

        <!-- Form -->
        <section class="col-span-12 md:col-span-9 card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs text-slate-400" id="slotBreadcrumb"></p>
                    <h2 class="text-lg font-semibold text-slate-900 mt-0.5" id="slotLabel"></h2>
                </div>
                <div id="slotExisting" class="hidden text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded"><?= $t('admin.fill.existing') ?></div>
            </div>

            <div id="slotForm" class="space-y-4"></div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-100">
                <button type="button" id="btnPrev" class="btn-secondary"><?= $t('admin.fill.prev') ?></button>
                <div class="flex gap-2">
                    <button type="button" id="btnSave" class="btn-ghost"><?= $t('admin.fill.save') ?></button>
                    <button type="button" id="btnNext" class="btn-primary"><?= $t('admin.fill.next') ?></button>
                    <a href="/admin/papers" id="btnFinish" class="btn-primary hidden"><?= $t('admin.fill.finish') ?></a>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
const FILL = {
    paperId: <?= (int)$paper['id'] ?>,
    templateId: <?= (int)$template['id'] ?>,
    slots: <?= json_encode($slotsForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
    initial: <?= (int)$initialSlot ?>,
    i18n: {
        saved: <?= json_encode($t('admin.fill.saved')) ?>,
        qLabel: <?= json_encode($t('admin.fill.question_label')) ?>,
        answer: <?= json_encode($t('admin.fill.answer')) ?>,
        stem: <?= json_encode($t('admin.fill.stem')) ?>,
        stemBlank: <?= json_encode($t('admin.fill.stem_blank')) ?>,
        optionsAnswer: <?= json_encode($t('admin.fill.options_answer')) ?>,
        analysis: <?= json_encode($t('admin.fill.analysis')) ?>,
        answersLines: <?= json_encode($t('admin.fill.answers_lines')) ?>,
        titleOpt: <?= json_encode($t('admin.fill.title_opt')) ?>,
        sourceOpt: <?= json_encode($t('admin.fill.source_opt')) ?>,
        passage: <?= json_encode($t('admin.fill.passage')) ?>,
        prompt: <?= json_encode($t('admin.fill.prompt')) ?>,
        wordCount: <?= json_encode($t('admin.fill.word_count')) ?>,
        sampleAnalysis: <?= json_encode($t('admin.fill.sample_analysis')) ?>,
        sourceText: <?= json_encode($t('admin.fill.source_text')) ?>,
        targetTranslation: <?= json_encode($t('admin.fill.target_translation')) ?>,
        addOption: <?= json_encode($t('admin.fill.add_option')) ?>,
        tTrue: <?= json_encode($t('admin.fill.true')) ?>,
        tFalse: <?= json_encode($t('admin.fill.false')) ?>,
    },
    current: 0,
};

function renderSidebar() {
    FILL.slots.forEach(s => {
        const dot = document.querySelector(`[data-slot-dot="${s.index}"]`);
        if (dot) dot.className = 'w-2 h-2 rounded-full flex-shrink-0 ' + (s.question_id ? 'bg-emerald-500' : 'bg-slate-300');
    });
    const saved = FILL.slots.filter(s => s.question_id).length;
    document.getElementById('fillProgress').textContent = saved;
    document.getElementById('fillProgressBar').style.width = (FILL.slots.length ? (saved * 100 / FILL.slots.length) : 0) + '%';

    document.querySelectorAll('.slot-link').forEach(b => {
        b.classList.toggle('bg-primary-50', parseInt(b.dataset.slotBtn) === FILL.current);
    });
}

function tpl(type, content, answer, analysis) {
    content = content || {};
    const esc = (v) => v == null ? '' : String(v).replace(/"/g, '&quot;');
    const ta = (name, label, val, rows) => `
        <div><label class="form-label">${label}</label>
        <textarea name="${name}" rows="${rows||3}" class="form-input w-full">${esc(val||'')}</textarea></div>`;
    const ti = (name, label, val) => `
        <div><label class="form-label">${label}</label>
        <input type="text" name="${name}" class="form-input w-full" value="${esc(val||'')}"></div>`;

    if (type === 'single_choice' || type === 'multi_choice') {
        const opts = Array.isArray(content.options) ? content.options : ['', '', '', ''];
        const letters = ['A','B','C','D','E','F','G','H'];
        const inputType = type === 'single_choice' ? 'radio' : 'checkbox';
        let ans = answer;
        try { if (typeof ans === 'string') ans = JSON.parse(ans); } catch(e) {}
        const ansArr = Array.isArray(ans) ? ans : (ans ? [ans] : []);
        let html = ta('stem', FILL.i18n.stem, content.stem, 3);
        html += '<div><label class="form-label">' + FILL.i18n.optionsAnswer + '</label><div id="optsWrap" class="space-y-2">';
        opts.forEach((o, i) => {
            const letter = letters[i];
            const checked = ansArr.includes(letter) ? 'checked' : '';
            html += `<div class="flex items-center gap-2">
                <label class="flex items-center gap-1 w-16"><input type="${inputType}" name="answer_letter" value="${letter}" ${checked}> ${letter}</label>
                <input type="text" name="opt_${letter}" class="form-input flex-1" value="${esc(o)}">
            </div>`;
        });
        html += '</div><button type="button" id="addOpt" class="btn-ghost mt-2 text-xs">' + FILL.i18n.addOption + '</button></div>';
        html += ta('analysis', FILL.i18n.analysis, analysis, 3);
        return html;
    }
    if (type === 'fill_blank') {
        let ansStr = answer;
        try { const a = JSON.parse(answer); if (Array.isArray(a)) ansStr = a.join('\n'); } catch(e) {}
        return ta('stem', FILL.i18n.stemBlank, content.stem, 4)
             + ta('answers_text', FILL.i18n.answersLines, ansStr, 3)
             + ta('analysis', FILL.i18n.analysis, analysis, 3);
    }
    if (type === 'true_false') {
        let a = answer; try { a = JSON.parse(answer); } catch(e) {}
        return ta('stem', FILL.i18n.stem, content.stem, 3)
             + `<div><label class="form-label">${FILL.i18n.answer}</label>
                <label class="inline-flex items-center mr-4"><input type="radio" name="answer_tf" value="true" ${a===true||a==='true'?'checked':''}> ${FILL.i18n.tTrue}</label>
                <label class="inline-flex items-center"><input type="radio" name="answer_tf" value="false" ${a===false||a==='false'?'checked':''}> ${FILL.i18n.tFalse}</label></div>`
             + ta('analysis', FILL.i18n.analysis, analysis, 3);
    }
    if (type === 'reading_material') {
        return ti('title', FILL.i18n.titleOpt, content.title)
             + ti('source', FILL.i18n.sourceOpt, content.source)
             + ta('passage', FILL.i18n.passage, content.passage, 8);
    }
    if (type === 'writing') {
        return ta('prompt', FILL.i18n.prompt, content.prompt || content.stem, 4)
             + ti('word_count', FILL.i18n.wordCount, content.word_count)
             + ta('analysis', FILL.i18n.sampleAnalysis, analysis, 5);
    }
    if (type === 'translation') {
        let a = answer; try { const j = JSON.parse(answer); if (typeof j === 'string') a = j; } catch(e) {}
        return ta('stem', FILL.i18n.sourceText, content.stem, 4)
             + ta('answer_text', FILL.i18n.targetTranslation, a, 4)
             + ta('analysis', FILL.i18n.analysis, analysis, 3);
    }
    // default
    let a = answer; try { const j = JSON.parse(answer); if (typeof j === 'string') a = j; } catch(e) {}
    return ta('stem', FILL.i18n.stem, content.stem, 4)
         + ta('answer_text', FILL.i18n.answer, a, 3)
         + ta('analysis', FILL.i18n.analysis, analysis, 3);
}

function loadSlot(i) {
    FILL.current = i;
    const s = FILL.slots[i];
    if (!s) return;
    const type = s.slot_type === 'child' ? (s.child_type || 'single_choice') : s.block_type;

    document.getElementById('slotBreadcrumb').textContent =
        s.section_title + ' · ' + s.block_title + ' · ' + type + (s.slot_type === 'child' ? ' (sub)' : '');
    document.getElementById('slotLabel').textContent =
        FILL.i18n.qLabel.replace(':n', i + 1).replace(':total', FILL.slots.length);
    document.getElementById('slotExisting').classList.toggle('hidden', !s.question_id);

    let content = {};
    try { content = JSON.parse(s.content_json || '{}'); } catch(e) {}
    document.getElementById('slotForm').innerHTML = tpl(type, content, s.answer_json, s.analysis_json);

    const addOpt = document.getElementById('addOpt');
    if (addOpt) {
        addOpt.onclick = () => {
            const wrap = document.getElementById('optsWrap');
            const letters = ['A','B','C','D','E','F','G','H'];
            const idx = wrap.children.length;
            if (idx >= 8) return;
            const letter = letters[idx];
            const inputType = type === 'single_choice' ? 'radio' : 'checkbox';
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2';
            div.innerHTML = `<label class="flex items-center gap-1 w-16"><input type="${inputType}" name="answer_letter" value="${letter}"> ${letter}</label>
                <input type="text" name="opt_${letter}" class="form-input flex-1" value="">`;
            wrap.appendChild(div);
        };
    }

    document.getElementById('btnPrev').disabled = i === 0;
    const isLast = i === FILL.slots.length - 1;
    document.getElementById('btnNext').classList.toggle('hidden', isLast);
    document.getElementById('btnFinish').classList.toggle('hidden', !isLast);
    renderSidebar();
}

function collect() {
    const form = document.getElementById('slotForm');
    const s = FILL.slots[FILL.current];
    const type = s.slot_type === 'child' ? (s.child_type || 'single_choice') : s.block_type;
    const getV = (name) => { const el = form.querySelector(`[name="${name}"]`); return el ? el.value : ''; };
    const content = {};
    let answer = null, analysis = getV('analysis') || null;

    if (type === 'single_choice' || type === 'multi_choice') {
        content.stem = getV('stem');
        const letters = ['A','B','C','D','E','F','G','H'];
        content.options = [];
        letters.forEach(L => {
            const el = form.querySelector(`[name="opt_${L}"]`);
            if (el) content.options.push(el.value);
        });
        const checked = Array.from(form.querySelectorAll('[name="answer_letter"]:checked')).map(e => e.value);
        answer = type === 'single_choice' ? (checked[0] || null) : checked;
    } else if (type === 'fill_blank') {
        content.stem = getV('stem');
        const lines = getV('answers_text').split('\n').map(x => x.trim()).filter(Boolean);
        answer = lines;
    } else if (type === 'true_false') {
        content.stem = getV('stem');
        const c = form.querySelector('[name="answer_tf"]:checked');
        answer = c ? (c.value === 'true') : null;
    } else if (type === 'reading_material') {
        content.title = getV('title');
        content.source = getV('source');
        content.passage = getV('passage');
    } else if (type === 'writing') {
        content.prompt = getV('prompt');
        content.word_count = getV('word_count');
    } else if (type === 'translation') {
        content.stem = getV('stem');
        answer = getV('answer_text');
    } else {
        content.stem = getV('stem');
        answer = getV('answer_text');
    }

    return {
        content: JSON.stringify(content),
        answer: answer == null ? null : (typeof answer === 'string' && !Array.isArray(answer) ? JSON.stringify(answer) : JSON.stringify(answer)),
        analysis: analysis ? JSON.stringify(analysis) : null,
    };
}

async function save(advance) {
    const payload = collect();
    try {
        const res = await QS.fetch(`/admin/papers/${FILL.paperId}/fill/${FILL.templateId}/slot/${FILL.current}`, {
            method: 'POST', body: payload
        });
        FILL.slots[FILL.current].question_id = res.question_id;
        // store submitted content back to slot
        FILL.slots[FILL.current].content_json = payload.content;
        FILL.slots[FILL.current].answer_json = payload.answer;
        FILL.slots[FILL.current].analysis_json = payload.analysis;
        QS.toast(FILL.i18n.saved);
        renderSidebar();
        if (advance && res.next_slot !== null && res.next_slot !== undefined) {
            loadSlot(res.next_slot);
        }
    } catch (e) {
        QS.toast(e.message, 'error');
    }
}

document.getElementById('btnPrev').addEventListener('click', () => {
    if (FILL.current > 0) loadSlot(FILL.current - 1);
});
document.getElementById('btnNext').addEventListener('click', () => save(true));
document.getElementById('btnSave').addEventListener('click', () => save(false));
document.querySelectorAll('.slot-link').forEach(b => {
    b.addEventListener('click', () => loadSlot(parseInt(b.dataset.slotBtn)));
});

loadSlot(FILL.initial);
</script>
