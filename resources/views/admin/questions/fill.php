<!-- Fill Questions from Template -->
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900"><?= $t('admin.questions.fl_title') ?></h2>
            <p class="text-sm text-slate-500 mt-0.5"><?= $t('admin.questions.fl_subtitle') ?></p>
        </div>
        <a href="/admin/questions" class="btn-secondary text-sm"><?= $t('admin.questions.fl_back_list') ?></a>
    </div>

    <!-- Step 1: Paper & Blueprint Selection -->
    <div class="card p-5">
        <form method="GET" action="/admin/questions/fill" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[240px]">
                <label class="form-label"><?= $t('admin.questions.fl_step1') ?></label>
                <select name="paper_id" class="form-input" onchange="this.form.submit()">
                    <option value=""><?= $t('admin.questions.fl_pick_paper') ?></option>
                    <?php foreach ($papers as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($paper && $paper['id'] == $p['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['subject_name'] ?? '') ?>) · <?= $t('admin.questions.fl_bp_count', ['n' => $p['bp_count']]) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($paper): ?>
            <div class="flex-1 min-w-[240px]">
                <label class="form-label"><?= $t('admin.questions.fl_step2') ?></label>
                <select name="blueprint_id" class="form-input" onchange="this.form.submit()">
                    <option value=""><?= $t('admin.questions.fl_pick_bp') ?></option>
                    <?php foreach ($blueprints as $bp): ?>
                    <option value="<?= $bp['id'] ?>" <?= ($blueprint && $blueprint['id'] == $bp['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($bp['name']) ?> (<?= $bp['status'] ?> · v<?= $bp['version'] ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($blueprints)): ?>
                <p class="text-xs text-amber-600 mt-1"><?= $t('admin.questions.fl_no_bp_prefix') ?> <a href="/admin/templates" class="underline"><?= $t('admin.questions.fl_no_bp_link') ?></a> <?= $t('admin.questions.fl_no_bp_suffix') ?></p>
                <?php endif; ?>
            </div>
            <input type="hidden" name="paper_id" value="<?= $paper['id'] ?>">
            <?php endif; ?>
        </form>
    </div>

    <?php if ($blueprint && $annotatedSchema): ?>
    <?php $bpData = json_decode($blueprint['blueprint_json'], true) ?: ['sections' => []]; ?>

    <!-- Tabs -->
    <div class="flex gap-1 border-b border-slate-200">
        <button onclick="Fill.showTab('form')" id="tab-form" class="px-4 py-2.5 text-sm font-medium border-b-2 border-primary-600 text-primary-600"><?= $t('admin.questions.fl_tab_form') ?></button>
        <button onclick="Fill.showTab('schema')" id="tab-schema" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700"><?= $t('admin.questions.fl_tab_schema') ?></button>
        <button onclick="Fill.showTab('paste')" id="tab-paste" class="px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700"><?= $t('admin.questions.fl_tab_paste') ?></button>
    </div>

    <!-- Tab: Form -->
    <div id="pane-form" class="space-y-4">
        <?php foreach ($bpData['sections'] ?? [] as $sIdx => $sec): ?>
        <div class="card p-5 border-l-4 border-primary-500">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-slate-900"><?= htmlspecialchars($sec['title'] ?? 'Section') ?></h3>
                    <p class="text-xs text-slate-400 mt-0.5">type: <?= htmlspecialchars($sec['type'] ?? 'default') ?> · id: <?= htmlspecialchars($sec['id'] ?? '') ?></p>
                    <?php if (!empty($sec['instructions'])): ?>
                    <p class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($sec['instructions']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php foreach ($sec['blocks'] ?? [] as $bIdx => $block):
                $bt = $block['type'] ?? 'single_choice';
                $count = in_array($bt, ['description','media']) ? 1 : max(1, (int)($block['count'] ?? 1));
            ?>
            <div class="bg-slate-50 rounded-lg p-4 mb-3" data-section="<?= $sIdx ?>" data-block="<?= $bIdx ?>" data-type="<?= $bt ?>" data-section-id="<?= htmlspecialchars($sec['id'] ?? '') ?>" data-block-id="<?= htmlspecialchars($block['id'] ?? '') ?>">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-info"><?= htmlspecialchars($bt) ?></span>
                        <span class="text-xs text-slate-500"><?= $t('admin.questions.fl_q_per_score', ['n' => $count, 'score' => $block['score'] ?? 0]) ?></span>
                    </div>
                </div>
                <div class="space-y-3 slot-container">
                    <?php for ($i = 0; $i < $count; $i++): ?>
                    <div class="bg-white border border-slate-200 rounded-lg p-3 slot-item">
                        <div class="text-xs font-semibold text-slate-500 mb-2">Q<?= $i + 1 ?></div>
                        <?php if ($bt === 'description'): ?>
                            <textarea class="form-input text-xs" rows="5" data-field="content" placeholder="<?= $t('admin.questions.fl_desc_ph') ?>"><?= htmlspecialchars($block['content'] ?? '') ?></textarea>
                        <?php elseif ($bt === 'media'): ?>
                            <select class="form-input text-xs mb-2" data-field="media_type">
                                <option value="image" <?= ($block['media_type'] ?? '')==='image'?'selected':'' ?>><?= $t('admin.questions.fl_media_image') ?></option>
                                <option value="audio" <?= ($block['media_type'] ?? '')==='audio'?'selected':'' ?>><?= $t('admin.questions.fl_media_audio') ?></option>
                            </select>
                            <input type="text" class="form-input text-xs mb-1" data-field="url" value="<?= htmlspecialchars($block['url'] ?? '') ?>" placeholder="<?= $t('admin.questions.fl_url_ph') ?>">
                            <p class="text-xs text-slate-400 mb-2"><?= $t('admin.questions.fl_upload_tbd') ?></p>
                            <input type="text" class="form-input text-xs mb-1" data-field="caption" value="<?= htmlspecialchars($block['caption'] ?? '') ?>" placeholder="<?= $t('admin.questions.fl_caption_ph') ?>">
                            <input type="text" class="form-input text-xs" data-field="link_url" value="<?= htmlspecialchars($block['link_url'] ?? '') ?>" placeholder="<?= $t('admin.questions.fl_linkurl_ph') ?>">
                        <?php elseif (in_array($bt, ['reading_material','listening_material'])): ?>
                            <textarea class="form-input text-xs mb-2" rows="4" data-field="passage" placeholder="<?= $t('admin.questions.fl_passage_ph') ?>"></textarea>
                            <input type="text" class="form-input text-xs mb-2" data-field="audio_url" placeholder="<?= $t('admin.questions.fl_audio_ph') ?>">
                            <p class="text-xs text-slate-400"><?= $t('admin.questions.fl_subq_hint') ?></p>
                        <?php elseif ($bt === 'writing'): ?>
                            <textarea class="form-input text-xs mb-2" rows="3" data-field="stem" placeholder="<?= $t('admin.questions.fl_writing_stem_ph') ?>"></textarea>
                            <input type="number" class="form-input text-xs mb-2" data-field="word_count" placeholder="<?= $t('admin.questions.fl_wordcount_ph') ?>">
                            <textarea class="form-input text-xs mb-2" rows="3" data-field="answer" placeholder="<?= $t('admin.questions.fl_writing_answer_ph') ?>"></textarea>
                            <textarea class="form-input text-xs" rows="2" data-field="analysis" placeholder="<?= $t('admin.questions.fl_writing_analysis_ph') ?>"></textarea>
                        <?php elseif ($bt === 'translation'): ?>
                            <textarea class="form-input text-xs mb-2" rows="3" data-field="stem" placeholder="<?= $t('admin.questions.fl_translation_stem_ph') ?>"></textarea>
                            <textarea class="form-input text-xs mb-2" rows="3" data-field="answer" placeholder="<?= $t('admin.questions.fl_translation_answer_ph') ?>"></textarea>
                            <textarea class="form-input text-xs" rows="2" data-field="analysis" placeholder="<?= $t('admin.questions.fl_translation_analysis_ph') ?>"></textarea>
                        <?php elseif ($bt === 'true_false'): ?>
                            <textarea class="form-input text-xs mb-2" rows="2" data-field="stem" placeholder="<?= $t('admin.questions.fl_tf_stem_ph') ?>"></textarea>
                            <select class="form-input text-xs mb-2" data-field="answer">
                                <option value="true">True</option><option value="false">False</option>
                            </select>
                            <textarea class="form-input text-xs" rows="2" data-field="analysis" placeholder="<?= $t('admin.questions.fl_analysis_ph') ?>"></textarea>
                        <?php elseif (in_array($bt, ['fill_blank','short_answer'])): ?>
                            <textarea class="form-input text-xs mb-2" rows="2" data-field="stem" placeholder="<?= $t('admin.questions.fl_fill_stem_ph') ?>"></textarea>
                            <input type="text" class="form-input text-xs mb-2" data-field="answer" placeholder="<?= $t('admin.questions.fl_fill_answer_ph') ?>">
                            <textarea class="form-input text-xs" rows="2" data-field="analysis" placeholder="<?= $t('admin.questions.fl_analysis_ph') ?>"></textarea>
                        <?php else: /* choice types */ ?>
                            <textarea class="form-input text-xs mb-2" rows="2" data-field="stem" placeholder="<?= $t('admin.questions.fl_choice_stem_ph') ?>"></textarea>
                            <input type="text" class="form-input text-xs mb-1" data-field="opt_A" placeholder="A. ...">
                            <input type="text" class="form-input text-xs mb-1" data-field="opt_B" placeholder="B. ...">
                            <input type="text" class="form-input text-xs mb-1" data-field="opt_C" placeholder="C. ...">
                            <input type="text" class="form-input text-xs mb-2" data-field="opt_D" placeholder="D. ...">
                            <input type="text" class="form-input text-xs mb-2" data-field="answer" placeholder="<?= $t('admin.questions.fl_choice_answer_ph') ?>">
                            <textarea class="form-input text-xs" rows="2" data-field="analysis" placeholder="<?= $t('admin.questions.fl_analysis_ph') ?>"></textarea>
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div class="flex justify-end gap-3">
            <button onclick="Fill.submit()" class="btn-primary"><?= $t('admin.questions.fl_save_all') ?></button>
        </div>
    </div>

    <!-- Tab: Annotated Schema -->
    <div id="pane-schema" class="hidden space-y-3">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900"><?= $t('admin.questions.fl_schema_title') ?></h3>
                    <p class="text-xs text-slate-500 mt-0.5"><?= $t('admin.questions.fl_schema_desc') ?></p>
                </div>
                <button onclick="Fill.copySchema()" class="btn-secondary text-xs"><?= $t('admin.questions.fl_copy_all') ?></button>
            </div>
            <textarea id="schema-json" class="form-input font-mono text-xs" rows="24" readonly></textarea>
        </div>
    </div>

    <!-- Tab: Paste JSON -->
    <div id="pane-paste" class="hidden space-y-3">
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-2"><?= $t('admin.questions.fl_paste_title') ?></h3>
            <p class="text-xs text-slate-500 mb-3"><?= $t('admin.questions.fl_paste_desc_html') ?></p>
            <textarea id="paste-json" class="form-input font-mono text-xs" rows="20" placeholder='{"sections":[...]}'></textarea>
            <div class="flex justify-end gap-3 mt-3">
                <button onclick="Fill.loadIntoForm()" class="btn-secondary text-xs"><?= $t('admin.questions.fl_load_to_form') ?></button>
                <button onclick="Fill.submitPasted()" class="btn-primary text-xs"><?= $t('admin.questions.fl_direct_import') ?></button>
            </div>
        </div>
    </div>

    <script>
    const ANNOTATED_SCHEMA = <?= json_encode($annotatedSchema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>;
    const PAPER_ID = <?= (int)$paper['id'] ?>;
    const BLUEPRINT_ID = <?= (int)$blueprint['id'] ?>;
    const PAPER_TITLE = <?= json_encode($paper['title'] ?? '') ?>;
    const FILL_I18N = {
        ai_prompt: <?= json_encode($t('admin.questions.fl_ai_prompt', ['title' => $paper['title'] ?? ''])) ?>,
        ai_prompt_suffix: <?= json_encode($t('admin.questions.fl_ai_prompt_suffix')) ?>,
        copied: <?= json_encode($t('admin.questions.fl_copied')) ?>,
        confirm_import: <?= json_encode($t('admin.questions.fl_confirm_import')) ?>,
        json_err: <?= json_encode($t('admin.questions.fl_json_err')) ?>,
        no_sections: <?= json_encode($t('admin.questions.fl_no_sections')) ?>,
        json_err_simple: <?= json_encode($t('admin.questions.fl_json_err_simple')) ?>,
        loaded: <?= json_encode($t('admin.questions.fl_loaded')) ?>
    };

    const Fill = {
        init() {
            const prompt = FILL_I18N.ai_prompt + JSON.stringify(ANNOTATED_SCHEMA, null, 2) + FILL_I18N.ai_prompt_suffix;
            document.getElementById('schema-json').value = prompt;
        },
        showTab(name) {
            ['form','schema','paste'].forEach(n => {
                document.getElementById('pane-'+n).classList.toggle('hidden', n !== name);
                const t = document.getElementById('tab-'+n);
                t.classList.toggle('border-primary-600', n === name);
                t.classList.toggle('text-primary-600', n === name);
                t.classList.toggle('border-transparent', n !== name);
                t.classList.toggle('text-slate-500', n !== name);
            });
        },
        copySchema() {
            const ta = document.getElementById('schema-json');
            ta.select();
            navigator.clipboard.writeText(ta.value).then(() => QS.toast(FILL_I18N.copied));
        },
        collectForm() {
            const sections = {};
            document.querySelectorAll('[data-section][data-block]').forEach(blockEl => {
                const sid = blockEl.dataset.sectionId;
                const bid = blockEl.dataset.blockId;
                const type = blockEl.dataset.type;
                if (!sections[sid]) sections[sid] = { id: sid, blocks: [] };
                const instances = [];
                blockEl.querySelectorAll('.slot-item').forEach(slot => {
                    const inst = { type };
                    const get = f => slot.querySelector(`[data-field="${f}"]`)?.value?.trim() || '';
                    const content = get('content');
                    if (content) inst.content = content;
                    const media_type = get('media_type');
                    if (media_type) inst.media_type = media_type;
                    const url = get('url');
                    if (url) inst.url = url;
                    const caption = get('caption');
                    if (caption) inst.caption = caption;
                    const link_url = get('link_url');
                    if (link_url) inst.link_url = link_url;
                    const stem = get('stem');
                    if (stem) inst.stem = stem;
                    const passage = get('passage');
                    if (passage) inst.passage = passage;
                    const audio = get('audio_url');
                    if (audio) inst.audio_url = audio;
                    const wc = get('word_count');
                    if (wc) inst.word_count = parseInt(wc);
                    const opts = [];
                    ['A','B','C','D'].forEach(k => {
                        const v = get('opt_'+k);
                        if (v) opts.push(k + '. ' + v.replace(/^[A-D][.、]\s*/, ''));
                    });
                    if (opts.length) inst.options = opts;
                    let ans = get('answer');
                    if (ans) {
                        if (type === 'true_false') ans = ans === 'true';
                        else if (type === 'multi_choice') ans = ans.split(/[,，]/).map(s=>s.trim()).filter(Boolean);
                        else if (type === 'fill_blank') ans = ans.split('|').map(s=>s.trim());
                        inst.answer = ans;
                    }
                    const analysis = get('analysis');
                    if (analysis) inst.analysis = analysis;
                    instances.push(inst);
                });
                sections[sid].blocks.push({ id: bid, type, instances });
            });
            return Object.values(sections);
        },
        async submit() {
            const sections = this.collectForm();
            if (!await QS.confirm(FILL_I18N.confirm_import)) return;
            try {
                const res = await QS.fetch('/admin/questions/fill', {
                    method: 'POST',
                    body: { paper_id: PAPER_ID, blueprint_id: BLUEPRINT_ID, sections }
                });
                QS.toast(res.message);
                setTimeout(() => window.location.href = '/admin/questions?paper_id='+PAPER_ID, 800);
            } catch(e) { QS.toast(e.message, 'error'); }
        },
        async submitPasted() {
            let data;
            try { data = JSON.parse(document.getElementById('paste-json').value); }
            catch(e) { return QS.toast(FILL_I18N.json_err.replace(':msg', e.message), 'error'); }
            if (!data.sections) return QS.toast(FILL_I18N.no_sections, 'error');
            try {
                const res = await QS.fetch('/admin/questions/fill', {
                    method: 'POST',
                    body: { paper_id: PAPER_ID, blueprint_id: BLUEPRINT_ID, sections: data.sections }
                });
                QS.toast(res.message);
                setTimeout(() => window.location.href = '/admin/questions?paper_id='+PAPER_ID, 800);
            } catch(e) { QS.toast(e.message, 'error'); }
        },
        loadIntoForm() {
            let data;
            try { data = JSON.parse(document.getElementById('paste-json').value); }
            catch(e) { return QS.toast(FILL_I18N.json_err_simple, 'error'); }
            (data.sections || []).forEach(sec => {
                (sec.blocks || []).forEach(block => {
                    const blockEl = document.querySelector(`[data-section-id="${sec.id}"][data-block-id="${block.id}"]`);
                    if (!blockEl) return;
                    const slots = blockEl.querySelectorAll('.slot-item');
                    (block.instances || []).forEach((inst, i) => {
                        const slot = slots[i]; if (!slot) return;
                        const set = (f, v) => { const el = slot.querySelector(`[data-field="${f}"]`); if (el && v !== undefined) el.value = v; };
                        set('stem', inst.stem);
                        set('content', inst.content);
                        set('media_type', inst.media_type);
                        set('url', inst.url);
                        set('caption', inst.caption);
                        set('link_url', inst.link_url);
                        set('passage', inst.passage);
                        set('audio_url', inst.audio_url);
                        set('word_count', inst.word_count);
                        set('analysis', inst.analysis);
                        if (Array.isArray(inst.options)) {
                            inst.options.forEach(o => {
                                const m = o.match(/^([A-D])[.、]?\s*(.*)$/);
                                if (m) set('opt_'+m[1], m[2]);
                            });
                        }
                        let ans = inst.answer;
                        if (typeof ans === 'boolean') ans = ans ? 'true' : 'false';
                        else if (Array.isArray(ans)) ans = ans.join(',');
                        set('answer', ans);
                    });
                });
            });
            this.showTab('form');
            QS.toast(FILL_I18N.loaded);
        }
    };
    Fill.init();
    </script>
    <?php endif; ?>
</div>
