<!-- Question Create/Edit Form -->
<?php
    $isEdit = !empty($question);
    $content = $isEdit ? (json_decode($question['content_json'], true) ?: []) : [];
    $answer = $isEdit ? ($question['answer_json'] ?? '') : '';
    $analysis = $isEdit ? ($question['analysis_json'] ?? '') : '';
    $childrenData = !empty($children) ? $children : [];
?>
<div class="max-w-4xl">
    <div class="mb-6">
        <a href="/admin/questions<?= $paper ? '?paper_id='.$paper['id'] : '' ?>" class="text-sm text-slate-500 hover:text-primary-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <?= $t('admin.questions.back') ?>
        </a>
    </div>

    <form id="question-form" class="space-y-6" onsubmit="return QuestionForm.save(event)">
        <!-- Paper & Type Selection -->
        <div class="card p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200"><?= $t('admin.questions.basic_settings') ?></h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.questions.subject') ?></label>
                    <select id="q-subject" class="form-input" onchange="QuestionForm.loadPapers(this.value)">
                        <option value=""><?= $t('admin.questions.select_ph') ?></option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($paper && $paper['subject_id'] == $s['id']) ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.questions.paper') ?> <span class="text-red-500">*</span></label>
                    <select id="q-paper" class="form-input" required>
                        <option value=""><?= $t('admin.questions.select_ph') ?></option>
                        <?php foreach ($papers as $p): ?>
                        <option value="<?= $p['id'] ?>" data-subject="<?= $p['subject_id'] ?>" <?= ($paper && $paper['id'] == $p['id']) ? 'selected' : '' ?>><?= htmlspecialchars($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.questions.q_type') ?> <span class="text-red-500">*</span></label>
                    <select id="q-type" class="form-input" required onchange="QuestionForm.onTypeChange(this.value)">
                        <option value=""><?= $t('admin.questions.select_ph') ?></option>
                        <?php foreach ($questionTypes as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($question['type'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.questions.score') ?></label>
                    <input type="number" id="q-score" class="form-input" step="0.5" value="<?= $question['score'] ?? 2 ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.questions.difficulty') ?></label>
                    <select id="q-difficulty" class="form-input">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($question['difficulty'] ?? 3) == $i ? 'selected' : '' ?>><?= $i ?> - <?= $t('admin.questions.diff.'.$i) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.common.status') ?></label>
                <div class="flex gap-4 mt-1">
                    <?php foreach (['draft'=>$t('admin.questions.status_draft'),'published'=>$t('admin.questions.status_published')] as $sv => $sl): ?>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="<?= $sv ?>" <?= ($question['status'] ?? 'draft') === $sv ? 'checked' : '' ?> class="text-primary-600">
                        <span class="text-sm text-slate-600"><?= $sl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Question Content (dynamic by type) -->
        <div class="card p-6 space-y-5" id="content-section">
            <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200"><?= $t('admin.questions.content_section') ?></h3>

            <!-- Stem / Passage / Instructions -->
            <div id="stem-group">
                <label class="form-label" id="stem-label"><?= $t('admin.questions.stem') ?> <span class="text-red-500">*</span></label>
                <textarea id="q-stem" class="form-input" rows="3" placeholder="<?= $t('admin.questions.stem_ph') ?>"><?= htmlspecialchars($content['stem'] ?? $content['passage'] ?? $content['instructions'] ?? '') ?></textarea>
            </div>

            <!-- Options (for choice types) -->
            <div id="options-group" class="<?= in_array($question['type'] ?? '', ['single_choice','multi_choice','cloze']) ? '' : 'hidden' ?>">
                <label class="form-label"><?= $t('admin.questions.options_label') ?></label>
                <div id="options-list" class="space-y-2">
                    <?php
                    $opts = $content['options'] ?? ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
                    foreach ($opts as $key => $val): ?>
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 bg-slate-100 rounded-md flex items-center justify-center text-xs font-semibold text-slate-600"><?= htmlspecialchars($key) ?></span>
                        <input type="text" class="form-input flex-1 option-input" data-key="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>" placeholder="<?= $t('admin.questions.option_ph') ?> <?= htmlspecialchars($key) ?>">
                        <button type="button" onclick="this.closest('.flex').remove()" class="text-slate-300 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" onclick="QuestionForm.addOption()" class="mt-2 text-sm text-primary-600 hover:text-primary-700 font-medium"><?= $t('admin.questions.add_option_btn') ?></button>
            </div>

            <!-- Audio (for listening) -->
            <div id="audio-group" class="<?= ($question['type'] ?? '') === 'listening_material' ? '' : 'hidden' ?>">
                <label class="form-label"><?= $t('admin.questions.audio_url') ?></label>
                <input type="text" id="q-audio" class="form-input" placeholder="<?= $t('admin.questions.audio_ph') ?>" value="<?= htmlspecialchars($content['audio_url'] ?? $question['audio_url'] ?? '') ?>">
            </div>
        </div>

        <!-- Answer -->
        <div class="card p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200"><?= $t('admin.questions.answer_section') ?></h3>
            <div>
                <label class="form-label"><?= $t('admin.questions.correct_answer') ?></label>
                <textarea id="q-answer" class="form-input" rows="2" placeholder="<?= $t('admin.questions.answer_ph') ?>"><?= htmlspecialchars(is_string($answer) ? $answer : json_encode($answer)) ?></textarea>
            </div>
            <div>
                <label class="form-label"><?= $t('admin.questions.analysis_label') ?></label>
                <textarea id="q-analysis" class="form-input" rows="4" placeholder="<?= $t('admin.questions.analysis_ph') ?>"><?= htmlspecialchars(is_string($analysis) ? $analysis : json_encode($analysis)) ?></textarea>
            </div>
        </div>

        <!-- Sub-questions (for reading/listening materials) -->
        <div id="children-section" class="<?= in_array($question['type'] ?? '', ['reading_material','listening_material']) ? '' : 'hidden' ?>">
            <div class="card p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-sm font-semibold text-slate-900"><?= $t('admin.questions.subq_title') ?></h3>
                    <button type="button" onclick="QuestionForm.addChild()" class="btn-secondary text-xs">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        <?= $t('admin.questions.add_subq') ?>
                    </button>
                </div>
                <div id="children-list" class="space-y-4">
                    <?php foreach ($childrenData as $i => $child):
                        $cc = json_decode($child['content_json'], true) ?: [];
                    ?>
                    <div class="child-item p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-500"><?= $t('admin.questions.subq_n', ['n' => $i + 1]) ?></span>
                            <button type="button" onclick="this.closest('.child-item').remove()" class="text-slate-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.type') ?></label>
                                <select class="form-input text-sm child-type">
                                    <option value="single_choice" <?= $child['type'] === 'single_choice' ? 'selected' : '' ?>><?= $t('admin.questions.type_single') ?></option>
                                    <option value="multi_choice" <?= $child['type'] === 'multi_choice' ? 'selected' : '' ?>><?= $t('admin.questions.type_multi') ?></option>
                                    <option value="fill_blank" <?= $child['type'] === 'fill_blank' ? 'selected' : '' ?>><?= $t('admin.questions.type_fill') ?></option>
                                    <option value="true_false" <?= $child['type'] === 'true_false' ? 'selected' : '' ?>><?= $t('admin.questions.type_tf') ?></option>
                                    <option value="short_answer" <?= $child['type'] === 'short_answer' ? 'selected' : '' ?>><?= $t('admin.questions.type_short') ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.score') ?></label>
                                <input type="number" class="form-input text-sm child-score" step="0.5" value="<?= $child['score'] ?? 2 ?>">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.stem') ?></label>
                            <textarea class="form-input text-sm child-stem" rows="2"><?= htmlspecialchars($cc['stem'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.subq_options_label') ?></label>
                            <textarea class="form-input text-sm child-options font-mono" rows="2" placeholder="<?= htmlspecialchars($t('admin.questions.subq_options_ph')) ?>"><?php
                                $co = $cc['options'] ?? [];
                                echo htmlspecialchars(implode("\n", array_map(fn($k,$v) => "$k=$v", array_keys($co), array_values($co))));
                            ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.answer') ?></label>
                                <input type="text" class="form-input text-sm child-answer" value="<?= htmlspecialchars($child['answer_json'] ?? '') ?>">
                            </div>
                            <div>
                                <label class="text-xs text-slate-500 mb-1 block"><?= $t('admin.questions.analysis') ?></label>
                                <input type="text" class="form-input text-sm child-analysis" value="<?= htmlspecialchars($child['analysis_json'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-between">
            <a href="/admin/questions<?= $paper ? '?paper_id='.$paper['id'] : '' ?>" class="btn-secondary"><?= $t('admin.common.cancel') ?></a>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary" id="save-btn"><?= $isEdit ? $t('admin.questions.update_btn') : $t('admin.questions.save_btn') ?></button>
            </div>
        </div>
    </form>
</div>

<script>
const QFORM_I18N = {
    stem: <?= json_encode($t('admin.questions.stem')) ?>,
    stem_reading: <?= json_encode($t('admin.questions.stem_reading')) ?>,
    stem_listening: <?= json_encode($t('admin.questions.stem_listening')) ?>,
    stem_writing: <?= json_encode($t('admin.questions.stem_writing')) ?>,
    stem_translation: <?= json_encode($t('admin.questions.stem_translation')) ?>,
    select_ph: <?= json_encode($t('admin.questions.select_ph')) ?>,
    option_ph: <?= json_encode($t('admin.questions.option_ph')) ?>,
    subq_n_prefix: <?= json_encode(str_replace(':n', '', $t('admin.questions.subq_n'))) ?>,
    type_label: <?= json_encode($t('admin.questions.type')) ?>,
    score_label: <?= json_encode($t('admin.questions.score')) ?>,
    stem_label: <?= json_encode($t('admin.questions.stem')) ?>,
    options_label: <?= json_encode($t('admin.questions.subq_options_label')) ?>,
    options_ph: <?= json_encode($t('admin.questions.subq_options_ph')) ?>,
    answer_label: <?= json_encode($t('admin.questions.answer')) ?>,
    analysis_label: <?= json_encode($t('admin.questions.analysis')) ?>,
    type_single: <?= json_encode($t('admin.questions.type_single')) ?>,
    type_multi: <?= json_encode($t('admin.questions.type_multi')) ?>,
    type_fill: <?= json_encode($t('admin.questions.type_fill')) ?>,
    type_tf: <?= json_encode($t('admin.questions.type_tf')) ?>,
    type_short: <?= json_encode($t('admin.questions.type_short')) ?>,
    paper_type_required: <?= json_encode($t('admin.questions.paper_type_required')) ?>,
    saving: <?= json_encode($t('admin.questions.saving')) ?>,
    updated: <?= json_encode($t('admin.questions.updated')) ?>,
    created: <?= json_encode($t('admin.questions.created')) ?>,
    update_btn: <?= json_encode($t('admin.questions.update_btn')) ?>,
    save_btn: <?= json_encode($t('admin.questions.save_btn')) ?>
};
const QuestionForm = {
    isEdit: <?= $isEdit ? 'true' : 'false' ?>,
    questionId: <?= $question['id'] ?? 'null' ?>,

    onTypeChange(type) {
        const choiceTypes = ['single_choice','multi_choice','cloze'];
        const materialTypes = ['reading_material','listening_material'];
        document.getElementById('options-group').classList.toggle('hidden', !choiceTypes.includes(type));
        document.getElementById('audio-group').classList.toggle('hidden', type !== 'listening_material');
        document.getElementById('children-section').classList.toggle('hidden', !materialTypes.includes(type));

        const labels = {
            reading_material: QFORM_I18N.stem_reading,
            listening_material: QFORM_I18N.stem_listening,
            writing: QFORM_I18N.stem_writing,
            translation: QFORM_I18N.stem_translation,
        };
        document.getElementById('stem-label').textContent = (labels[type] || QFORM_I18N.stem) + ' *';
    },

    addOption() {
        const list = document.getElementById('options-list');
        const count = list.children.length;
        const key = String.fromCharCode(65 + count);
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <span class="w-7 h-7 bg-slate-100 rounded-md flex items-center justify-center text-xs font-semibold text-slate-600">${key}</span>
            <input type="text" class="form-input flex-1 option-input" data-key="${key}" placeholder="${QFORM_I18N.option_ph} ${key}">
            <button type="button" onclick="this.closest('.flex').remove()" class="text-slate-300 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>`;
        list.appendChild(div);
    },

    addChild() {
        const list = document.getElementById('children-list');
        const num = list.children.length + 1;
        const div = document.createElement('div');
        div.className = 'child-item p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-3';
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">${QFORM_I18N.subq_n_prefix}${num}</span>
                <button type="button" onclick="this.closest('.child-item').remove()" class="text-slate-400 hover:text-red-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.type_label}</label>
                    <select class="form-input text-sm child-type"><option value="single_choice">${QFORM_I18N.type_single}</option><option value="multi_choice">${QFORM_I18N.type_multi}</option><option value="fill_blank">${QFORM_I18N.type_fill}</option><option value="true_false">${QFORM_I18N.type_tf}</option><option value="short_answer">${QFORM_I18N.type_short}</option></select></div>
                <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.score_label}</label>
                    <input type="number" class="form-input text-sm child-score" step="0.5" value="2"></div>
            </div>
            <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.stem_label}</label><textarea class="form-input text-sm child-stem" rows="2"></textarea></div>
            <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.options_label}</label><textarea class="form-input text-sm child-options font-mono" rows="2" placeholder="${QFORM_I18N.options_ph.replace(/\n/g,'\\n')}"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.answer_label}</label><input type="text" class="form-input text-sm child-answer"></div>
                <div><label class="text-xs text-slate-500 mb-1 block">${QFORM_I18N.analysis_label}</label><input type="text" class="form-input text-sm child-analysis"></div>
            </div>`;
        list.appendChild(div);
        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    collectChildren() {
        return [...document.querySelectorAll('.child-item')].map(el => {
            const optsRaw = el.querySelector('.child-options')?.value || '';
            const options = {};
            optsRaw.split('\n').filter(l => l.includes('=')).forEach(l => {
                const [k, ...v] = l.split('=');
                options[k.trim()] = v.join('=').trim();
            });
            return {
                type: el.querySelector('.child-type')?.value || 'single_choice',
                score: parseFloat(el.querySelector('.child-score')?.value) || 0,
                stem: el.querySelector('.child-stem')?.value || '',
                options: Object.keys(options).length ? options : undefined,
                answer: el.querySelector('.child-answer')?.value || '',
                analysis: el.querySelector('.child-analysis')?.value || '',
            };
        });
    },

    async save(e) {
        e.preventDefault();
        const type = document.getElementById('q-type').value;
        const paperId = document.getElementById('q-paper').value;
        if (!paperId || !type) { QS.toast(QFORM_I18N.paper_type_required, 'error'); return false; }

        const options = {};
        document.querySelectorAll('.option-input').forEach(inp => { options[inp.dataset.key] = inp.value; });

        const contentJson = {};
        const stemKey = ['reading_material','listening_material'].includes(type) ? 'passage' : (type === 'writing' ? 'instructions' : 'stem');
        contentJson[stemKey] = document.getElementById('q-stem').value;
        if (['single_choice','multi_choice','cloze'].includes(type) && Object.keys(options).length) {
            contentJson.options = options;
        }
        const audioUrl = document.getElementById('q-audio').value;
        if (audioUrl) contentJson.audio_url = audioUrl;

        const data = {
            paper_id: parseInt(paperId),
            type,
            content_json: contentJson,
            answer: document.getElementById('q-answer').value,
            analysis: document.getElementById('q-analysis').value,
            score: parseFloat(document.getElementById('q-score').value) || 0,
            difficulty: parseInt(document.getElementById('q-difficulty').value) || 3,
            status: document.querySelector('input[name="status"]:checked')?.value || 'draft',
        };

        if (['reading_material','listening_material'].includes(type)) {
            data.children = this.collectChildren();
        }

        const btn = document.getElementById('save-btn');
        btn.disabled = true; btn.textContent = QFORM_I18N.saving;

        try {
            const url = this.isEdit ? `/admin/questions/${this.questionId}/update` : '/admin/questions';
            await QS.fetch(url, { method: 'POST', body: data });
            QS.toast(this.isEdit ? QFORM_I18N.updated : QFORM_I18N.created);
            setTimeout(() => window.location.href = `/admin/questions?paper_id=${paperId}`, 600);
        } catch(e) {
            QS.toast(e.message, 'error');
        } finally {
            btn.disabled = false; btn.textContent = this.isEdit ? QFORM_I18N.update_btn : QFORM_I18N.save_btn;
        }
        return false;
    },

    async loadPapers(subjectId) {
        const sel = document.getElementById('q-paper');
        sel.innerHTML = `<option value="">${QFORM_I18N.select_ph}</option>`;
        if (!subjectId) return;
        try {
            const papers = await QS.fetch(`/admin/api/subjects/${subjectId}/papers`);
            papers.forEach(p => { sel.innerHTML += `<option value="${p.id}">${p.title}</option>`; });
        } catch(e) {}
    }
};

// Filter paper dropdown by selected subject
document.getElementById('q-subject')?.addEventListener('change', function() {
    const sid = this.value;
    document.querySelectorAll('#q-paper option[data-subject]').forEach(opt => {
        opt.hidden = sid && opt.dataset.subject !== sid;
    });
});

// Init type-specific sections
const initType = document.getElementById('q-type')?.value;
if (initType) QuestionForm.onTypeChange(initType);
</script>
