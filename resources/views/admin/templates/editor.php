<!-- Blueprint Editor -->
<?php
    $blueprint = json_decode($template['blueprint_json'] ?? '{}', true);
    $sections = $blueprint['sections'] ?? [];
?>
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3 min-w-0">
            <a href="/admin/templates" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0">
                <input type="text" id="bp-name" value="<?= htmlspecialchars($template['name']) ?>" class="text-lg font-semibold text-slate-900 bg-transparent border-none focus:outline-none focus:ring-0 p-0 w-full">
                <p class="text-xs text-slate-400"><?= htmlspecialchars($template['paper_title'] ?? '') ?> &middot; <?= htmlspecialchars($template['subject_name'] ?? '') ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <select id="bp-status" class="form-input w-auto text-sm" onchange="Editor.save()">
                <?php foreach (['draft','active','archived'] as $st): ?>
                <option value="<?= $st ?>" <?= $template['status'] === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
            <button onclick="Editor.exportSchema()" class="btn-ghost text-sm" title="<?= $t('admin.templates.ed_schema_title') ?>">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <?= $t('admin.templates.ed_schema') ?>
            </button>
            <button onclick="Editor.openImport()" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <?= $t('admin.templates.ed_import_ai') ?>
            </button>
            <button onclick="Editor.save()" class="btn-primary text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?= $t('admin.templates.ed_save') ?>
            </button>
        </div>
    </div>

    <div class="flex gap-4" style="min-height: calc(100vh - 220px);">
        <!-- Left: Module Palette -->
        <div class="w-56 flex-shrink-0">
            <div class="sticky top-24 bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase"><?= $t('admin.templates.ed_components') ?></h3>
                </div>
                <div class="p-2 space-y-1">
                    <!-- Section types -->
                    <p class="px-2 py-1.5 text-xs font-semibold text-slate-400 uppercase"><?= $t('admin.templates.ed_sections') ?></p>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'reading')" onclick="Editor.addSection('reading')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> <?= $t('admin.templates.ed_section_reading') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'listening')" onclick="Editor.addSection('listening')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> <?= $t('admin.templates.ed_section_listening') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'writing')" onclick="Editor.addSection('writing')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> <?= $t('admin.templates.ed_section_writing') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'translation')" onclick="Editor.addSection('translation')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span> <?= $t('admin.templates.ed_section_translation') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'part')" onclick="Editor.addSection('part')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> <?= $t('admin.templates.ed_section_part') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'section', 'default')" onclick="Editor.addSection('default')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> <?= $t('admin.templates.ed_section_general') ?>
                    </button>

                    <p class="px-2 py-1.5 text-xs font-semibold text-slate-400 uppercase mt-2"><?= $t('admin.templates.ed_question_blocks') ?></p>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'single_choice')" onclick="Editor.addBlockToTarget('single_choice')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span> <?= $t('admin.templates.ed_block_single_choice') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'multi_choice')" onclick="Editor.addBlockToTarget('multi_choice')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-primary-400"></span> <?= $t('admin.templates.ed_block_multi_choice') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'fill_blank')" onclick="Editor.addBlockToTarget('fill_blank')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-teal-500"></span> <?= $t('admin.templates.ed_block_fill_blank') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'true_false')" onclick="Editor.addBlockToTarget('true_false')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span> <?= $t('admin.templates.ed_block_true_false') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'reading_material')" onclick="Editor.addBlockToTarget('reading_material')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-cyan-500"></span> <?= $t('admin.templates.ed_block_reading_material') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'writing')" onclick="Editor.addBlockToTarget('writing')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> <?= $t('admin.templates.ed_block_writing') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'translation')" onclick="Editor.addBlockToTarget('translation')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-violet-500"></span> <?= $t('admin.templates.ed_block_translation') ?>
                    </button>

                    <p class="px-2 py-1.5 text-xs font-semibold text-slate-400 uppercase mt-2"><?= $t('admin.templates.ed_content_blocks') ?></p>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'description')" onclick="Editor.addBlockToTarget('description')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span> <?= $t('admin.templates.ed_block_description') ?>
                    </button>
                    <button draggable="true" ondragstart="Editor.dragStart(event, 'block', 'media')" onclick="Editor.addBlockToTarget('media')" class="palette-item w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-pink-500"></span> <?= $t('admin.templates.ed_block_media') ?>
                    </button>

                    <?php if (!empty($modulesByCategory)): ?>
                    <p class="px-2 py-1.5 text-xs font-semibold text-slate-400 uppercase mt-2"><?= $t('admin.templates.ed_saved_modules') ?></p>
                    <?php foreach ($modulesByCategory as $cat => $mods): ?>
                    <?php foreach ($mods as $m): ?>
                    <button draggable="true" ondragstart='Editor.dragStart(event, "module", <?= json_encode($m["id"]) ?>)' class="palette-item w-full text-left px-3 py-2 text-xs rounded-lg hover:bg-slate-100 flex items-center gap-2 cursor-grab truncate">
                        <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                        <span class="truncate"><?= htmlspecialchars($m['name']) ?></span>
                    </button>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Center: Canvas -->
        <div class="flex-1 min-w-0">
            <div id="canvas" class="space-y-4">
                <!-- Sections rendered by JS -->
            </div>
            <div class="mt-4 border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:border-primary-400 hover:bg-primary-50/20 transition-colors cursor-pointer"
                 ondragover="event.preventDefault(); this.classList.add('border-primary-400','bg-primary-50/20')"
                 ondragleave="this.classList.remove('border-primary-400','bg-primary-50/20')"
                 ondrop="Editor.dropSection(event); this.classList.remove('border-primary-400','bg-primary-50/20')"
                 onclick="Editor.addSection()">
                <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <p class="text-sm text-slate-400"><?= $t('admin.templates.ed_drop_section') ?></p>
            </div>
        </div>

        <!-- Right: Properties Panel -->
        <div class="w-64 flex-shrink-0">
            <div class="sticky top-24 bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-xs font-semibold text-slate-500 uppercase"><?= $t('admin.templates.ed_properties') ?></h3>
                </div>
                <div id="props-panel" class="p-4">
                    <p class="text-sm text-slate-400 text-center py-6"><?= $t('admin.templates.ed_select_item') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div id="import-modal" class="hidden fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4" onclick="if(event.target===this)Editor.closeImport()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-900"><?= $t('admin.templates.ed_import_title') ?></h3>
                <p class="text-xs text-slate-500 mt-0.5"><?= $t('admin.templates.ed_import_desc') ?></p>
            </div>
            <button onclick="Editor.closeImport()" class="p-1 text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-3">
            <textarea id="import-json" rows="14" class="form-input font-mono text-xs" placeholder='{"sections":[{"id":"...","type":"reading","title":"...","blocks":[...]}]}'></textarea>
            <div id="import-error" class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
            <div class="flex items-center justify-between">
                <button onclick="Editor.copySchema()" class="text-xs text-primary-600 hover:text-primary-700 font-medium"><?= $t('admin.templates.ed_copy_schema') ?></button>
                <div class="flex gap-2">
                    <button onclick="Editor.closeImport()" class="btn-secondary text-sm"><?= $t('admin.common.cancel') ?></button>
                    <button onclick="Editor.applyImport()" class="btn-primary text-sm"><?= $t('admin.templates.ed_apply_save') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.palette-item { transition: all 0.15s; }
.palette-item:active { transform: scale(0.95); opacity: 0.7; }
.section-card { transition: all 0.2s; }
.section-card.drag-over { outline: 2px dashed #3B82F6; outline-offset: 2px; }
.block-item { transition: all 0.15s; }
.block-item.drag-over { outline: 2px dashed #60A5FA; outline-offset: -2px; }
.selected-item { outline: 2px solid #3B82F6; outline-offset: 1px; }
</style>

<script>
const EDITOR_I18N = {
    part_marker: <?= json_encode($t('admin.templates.ed_section_part')) ?>,
    untitled_section: <?= json_encode($t('admin.templates.ed_untitled_section')) ?>,
    drop_blocks: <?= json_encode($t('admin.templates.ed_drop_blocks')) ?>,
    section_props: <?= json_encode($t('admin.templates.ed_section_props')) ?>,
    block_props: <?= json_encode($t('admin.templates.ed_block_props')) ?>,
    title_field: <?= json_encode($t('admin.templates.ed_title_field')) ?>,
    type_field: <?= json_encode($t('admin.templates.ed_type_field')) ?>,
    time_limit: <?= json_encode($t('admin.templates.ed_time_limit')) ?>,
    no_limit: <?= json_encode($t('admin.templates.ed_no_limit')) ?>,
    instructions: <?= json_encode($t('admin.templates.ed_instructions')) ?>,
    instructions_ph: <?= json_encode($t('admin.templates.ed_instructions_ph')) ?>,
    blocks_count_suffix: <?= json_encode($t('admin.templates.ed_blocks_count', ['n' => '__N__'])) ?>,
    label: <?= json_encode($t('admin.templates.ed_label')) ?>,
    count: <?= json_encode($t('admin.templates.ed_count')) ?>,
    score_each: <?= json_encode($t('admin.templates.ed_score_each')) ?>,
    difficulty: <?= json_encode($t('admin.templates.ed_difficulty')) ?>,
    notes: <?= json_encode($t('admin.templates.ed_notes')) ?>,
    notes_ph: <?= json_encode($t('admin.templates.ed_notes_ph')) ?>,
    total_pts: <?= json_encode($t('admin.templates.ed_total_pts', ['n' => '__N__'])) ?>,
    type_general: <?= json_encode($t('admin.templates.ed_type_general')) ?>,
    type_reading: <?= json_encode($t('admin.templates.ed_type_reading')) ?>,
    type_listening: <?= json_encode($t('admin.templates.ed_type_listening')) ?>,
    type_writing: <?= json_encode($t('admin.templates.ed_type_writing')) ?>,
    type_translation: <?= json_encode($t('admin.templates.ed_type_translation')) ?>,
    block_single_choice: <?= json_encode($t('admin.templates.ed_block_single_choice')) ?>,
    block_multi_choice: <?= json_encode($t('admin.templates.ed_block_multi_choice')) ?>,
    block_fill_blank: <?= json_encode($t('admin.templates.ed_block_fill_blank')) ?>,
    block_true_false: <?= json_encode($t('admin.templates.ed_block_true_false')) ?>,
    block_reading_material: <?= json_encode($t('admin.templates.ed_block_reading_material')) ?>,
    block_writing: <?= json_encode($t('admin.templates.ed_block_writing')) ?>,
    block_translation: <?= json_encode($t('admin.templates.ed_block_translation')) ?>,
    block_description: <?= json_encode($t('admin.templates.ed_block_description')) ?>,
    block_media: <?= json_encode($t('admin.templates.ed_block_media')) ?>,
    block_module: <?= json_encode($t('admin.templates.ed_block_module')) ?>,
    title_reading: <?= json_encode($t('admin.templates.ed_title_reading')) ?>,
    title_listening: <?= json_encode($t('admin.templates.ed_title_listening')) ?>,
    title_writing: <?= json_encode($t('admin.templates.ed_title_writing')) ?>,
    title_translation: <?= json_encode($t('admin.templates.ed_title_translation')) ?>,
    title_part: <?= json_encode($t('admin.templates.ed_title_part')) ?>,
    title_new_section: <?= json_encode($t('admin.templates.ed_title_new_section')) ?>,
    content_md: <?= json_encode($t('admin.templates.ed_content_md')) ?>,
    content_md_ph: <?= json_encode($t('admin.templates.ed_content_md_ph')) ?>,
    media_type: <?= json_encode($t('admin.templates.ed_media_type')) ?>,
    media_image: <?= json_encode($t('admin.templates.ed_media_image')) ?>,
    media_audio: <?= json_encode($t('admin.templates.ed_media_audio')) ?>,
    url: <?= json_encode($t('admin.templates.ed_url')) ?>,
    file_path: <?= json_encode($t('admin.templates.ed_file_path')) ?>,
    file_ph: <?= json_encode($t('admin.templates.ed_file_ph')) ?>,
    caption: <?= json_encode($t('admin.templates.ed_caption')) ?>,
    link_url: <?= json_encode($t('admin.templates.ed_link_url')) ?>,
    select_item: <?= json_encode($t('admin.templates.ed_select_item')) ?>,
    saved: <?= json_encode($t('admin.templates.ed_saved')) ?>,
    schema_downloaded: <?= json_encode($t('admin.templates.ed_schema_downloaded')) ?>,
    schema_copied: <?= json_encode($t('admin.templates.ed_schema_copied')) ?>,
    copy_failed: <?= json_encode($t('admin.templates.ed_copy_failed')) ?>,
    err_root: <?= json_encode($t('admin.templates.ed_err_root')) ?>,
    err_sections: <?= json_encode($t('admin.templates.ed_err_sections')) ?>,
    err_section_invalid: <?= json_encode($t('admin.templates.ed_err_section_invalid', ['i' => '__I__'])) ?>,
    err_section_type: <?= json_encode($t('admin.templates.ed_err_section_type', ['i' => '__I__', 't' => '__T__'])) ?>,
    err_section_title: <?= json_encode($t('admin.templates.ed_err_section_title', ['i' => '__I__'])) ?>,
    err_section_blocks: <?= json_encode($t('admin.templates.ed_err_section_blocks', ['i' => '__I__'])) ?>,
    err_block_invalid: <?= json_encode($t('admin.templates.ed_err_block_invalid', ['i' => '__I__', 'j' => '__J__'])) ?>,
    err_block_type: <?= json_encode($t('admin.templates.ed_err_block_type', ['i' => '__I__', 'j' => '__J__', 't' => '__T__'])) ?>,
    err_paste_first: <?= json_encode($t('admin.templates.ed_err_paste_first')) ?>,
    err_invalid_json: <?= json_encode($t('admin.templates.ed_err_invalid_json', ['msg' => '__MSG__'])) ?>
};
const Editor = {
    templateId: <?= $template['id'] ?>,
    blueprint: <?= !empty($template['blueprint_json']) ? $template['blueprint_json'] : '{"sections":[]}' ?>,
    selectedSection: null,
    selectedBlock: null,
    dirty: false,

    init() {
        if (!this.blueprint || typeof this.blueprint !== 'object') this.blueprint = { sections: [] };
        if (!Array.isArray(this.blueprint.sections)) this.blueprint.sections = [];
        this.render();
        window.addEventListener('beforeunload', (e) => {
            if (this.dirty) { e.preventDefault(); e.returnValue = ''; }
        });
    },

    render() {
        const canvas = document.getElementById('canvas');
        canvas.innerHTML = '';
        const sectionColors = { reading: 'border-l-blue-500', listening: 'border-l-amber-500', writing: 'border-l-emerald-500', translation: 'border-l-purple-500', part: 'border-l-rose-500', default: 'border-l-slate-400' };

        // Part numbering:
        // A 'part' section is a marker that closes the nearest preceding content
        // section, tagging it as "Part N" scoped to that content section's TYPE.
        // Example: listening, part, listening, part → Listening Part 1, Listening Part 2.
        const partNums = {};   // contentSectionIdx -> N
        const typeCnt = {};    // type -> running count of parts of that type
        for (let si = 0; si < this.blueprint.sections.length; si++) {
            if (this.blueprint.sections[si].type !== 'part') continue;
            for (let k = si - 1; k >= 0; k--) {
                const prev = this.blueprint.sections[k];
                if (prev.type === 'part') continue;
                if (partNums[k]) break; // already marked
                typeCnt[prev.type] = (typeCnt[prev.type] || 0) + 1;
                partNums[k] = typeCnt[prev.type];
                break;
            }
        }

        this.blueprint.sections.forEach((section, si) => {
            // Render 'part' as a slim inline marker, not a full card
            if (section.type === 'part') {
                const divider = document.createElement('div');
                divider.className = 'flex items-center gap-2 py-1';
                divider.innerHTML = `
                    <span class="h-px flex-1 bg-rose-200"></span>
                    <span class="text-xs font-medium text-rose-500 px-2 py-0.5 bg-rose-50 rounded-full">⟵ ${EDITOR_I18N.part_marker}</span>
                    <button onclick="event.stopPropagation();Editor.removeSection(${si})" class="p-0.5 text-rose-400 hover:text-rose-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <span class="h-px flex-1 bg-rose-200"></span>`;
                document.getElementById('canvas').appendChild(divider);
                return;
            }
            const typeLabels = { reading: EDITOR_I18N.type_reading, listening: EDITOR_I18N.type_listening, writing: EDITOR_I18N.type_writing, translation: EDITOR_I18N.type_translation, default: EDITOR_I18N.type_general };
            const partPrefix = partNums[si] ? `${typeLabels[section.type] || section.type} ${EDITOR_I18N.title_part} ${partNums[si]} · ` : '';
            const div = document.createElement('div');
            div.className = `section-card card border-l-4 ${sectionColors[section.type] || sectionColors.default} overflow-hidden`;
            div.dataset.sectionIdx = si;
            div.onclick = (e) => { if (e.target.closest('.block-item')) return; this.selectSection(si); };

            let blocksHtml = '';
            (section.blocks || []).forEach((block, bi) => {
                const blockDots = { single_choice:'bg-primary-500', multi_choice:'bg-primary-400', fill_blank:'bg-teal-500', true_false:'bg-orange-500', reading_material:'bg-cyan-500', writing:'bg-green-500', translation:'bg-violet-500', description:'bg-yellow-500', media:'bg-pink-500', module:'bg-slate-400' };
                blocksHtml += `
                <div class="block-item flex items-center justify-between p-3 bg-white rounded-lg border border-slate-200 hover:border-primary-300 cursor-pointer group" data-section="${si}" data-block="${bi}" onclick="Editor.selectBlock(${si}, ${bi})">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full ${blockDots[block.type] || 'bg-slate-400'} flex-shrink-0"></span>
                        <span class="text-sm text-slate-700 truncate">${this.blockLabel(block)}</span>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        ${bi > 0 ? `<button onclick="event.stopPropagation();Editor.moveBlock(${si},${bi},-1)" class="p-0.5 text-slate-400 hover:text-slate-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>` : ''}
                        ${bi < section.blocks.length - 1 ? `<button onclick="event.stopPropagation();Editor.moveBlock(${si},${bi},1)" class="p-0.5 text-slate-400 hover:text-slate-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>` : ''}
                        <button onclick="event.stopPropagation();Editor.removeBlock(${si},${bi})" class="p-0.5 text-red-400 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                </div>`;
            });

            div.innerHTML = `
            <div class="px-5 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 text-slate-400 cursor-grab" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                    <span class="text-sm font-semibold text-slate-700">${partPrefix}${section.title || EDITOR_I18N.untitled_section}</span>
                    <span class="text-xs text-slate-400">(${section.type})</span>
                </div>
                <div class="flex items-center gap-1">
                    ${si > 0 ? `<button onclick="event.stopPropagation();Editor.moveSection(${si},-1)" class="p-1 text-slate-400 hover:text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>` : ''}
                    ${si < this.blueprint.sections.length - 1 ? `<button onclick="event.stopPropagation();Editor.moveSection(${si},1)" class="p-1 text-slate-400 hover:text-slate-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>` : ''}
                    <button onclick="event.stopPropagation();Editor.removeSection(${si})" class="p-1 text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            </div>
            <div class="p-4 space-y-2 blocks-area" data-section="${si}"
                 ondragover="event.preventDefault(); this.classList.add('bg-primary-50/30')"
                 ondragleave="this.classList.remove('bg-primary-50/30')"
                 ondrop="Editor.dropBlock(event, ${si}); this.classList.remove('bg-primary-50/30')">
                ${blocksHtml}
                ${!section.blocks || section.blocks.length === 0 ? `<p class="text-xs text-slate-400 text-center py-4">${EDITOR_I18N.drop_blocks}</p>` : ''}
            </div>`;

            canvas.appendChild(div);
        });
    },

    blockLabel(block) {
        const labels = { single_choice: EDITOR_I18N.block_single_choice, multi_choice: EDITOR_I18N.block_multi_choice, fill_blank: EDITOR_I18N.block_fill_blank, true_false: EDITOR_I18N.block_true_false, reading_material: EDITOR_I18N.block_reading_material, writing: EDITOR_I18N.block_writing, translation: EDITOR_I18N.block_translation, description: EDITOR_I18N.block_description, media: EDITOR_I18N.block_media, module: EDITOR_I18N.block_module };
        let label = labels[block.type] || block.type;
        if (block.count && block.count > 1) label += ` (×${block.count})`;
        if (block.title) label = block.title;
        return label;
    },

    dragStart(e, kind, type) {
        e.dataTransfer.setData('text/plain', JSON.stringify({ kind, type }));
        e.dataTransfer.effectAllowed = 'copy';
    },

    dropSection(e) {
        e.preventDefault();
        try {
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            if (data.kind === 'section') {
                this.addSection(data.type);
            }
        } catch(err) {}
    },

    dropBlock(e, sectionIdx) {
        e.preventDefault();
        try {
            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
            if (data.kind === 'block') {
                this.addBlock(sectionIdx, data.type);
            } else if (data.kind === 'module') {
                this.addBlock(sectionIdx, 'module', { module_id: data.type });
            }
        } catch(err) {}
    },

    addSection(type = 'default') {
        const id = 'section_' + Date.now();
        const titles = { reading: EDITOR_I18N.title_reading, listening: EDITOR_I18N.title_listening, writing: EDITOR_I18N.title_writing, translation: EDITOR_I18N.title_translation, part: EDITOR_I18N.title_part, default: EDITOR_I18N.title_new_section };
        this.blueprint.sections.push({
            id, type, title: titles[type] || titles.default, blocks: []
        });
        this.dirty = true;
        this.render();
    },

    removeSection(idx) {
        this.blueprint.sections.splice(idx, 1);
        this.dirty = true;
        this.selectedSection = null;
        this.render();
        this.clearProps();
    },

    moveSection(idx, dir) {
        const sections = this.blueprint.sections;
        const target = idx + dir;
        if (target < 0 || target >= sections.length) return;
        [sections[idx], sections[target]] = [sections[target], sections[idx]];
        this.dirty = true;
        this.render();
    },

    addBlock(sectionIdx, type, extra = {}) {
        if (!this.blueprint.sections[sectionIdx]) return;
        this.blueprint.sections[sectionIdx].blocks.push({
            id: 'block_' + Date.now(),
            type,
            count: 1,
            score: 1,
            title: '',
            ...extra
        });
        this.dirty = true;
        this.render();
    },

    addBlockToTarget(type, extra = {}) {
        if (!this.blueprint.sections.length) {
            this.addSection('default');
        }
        let idx = this.selectedBlock ? this.selectedBlock.si
                : (this.selectedSection !== null ? this.selectedSection
                : this.blueprint.sections.length - 1);
        this.addBlock(idx, type, extra);
    },

    removeBlock(si, bi) {
        this.blueprint.sections[si].blocks.splice(bi, 1);
        this.dirty = true;
        this.selectedBlock = null;
        this.render();
        this.clearProps();
    },

    moveBlock(si, bi, dir) {
        const blocks = this.blueprint.sections[si].blocks;
        const target = bi + dir;
        if (target < 0 || target >= blocks.length) return;
        [blocks[bi], blocks[target]] = [blocks[target], blocks[bi]];
        this.dirty = true;
        this.render();
    },

    selectSection(idx) {
        this.selectedSection = idx;
        this.selectedBlock = null;
        const section = this.blueprint.sections[idx];
        const panel = document.getElementById('props-panel');
        panel.innerHTML = `
        <div class="space-y-4">
            <h4 class="text-xs font-semibold text-slate-500 uppercase">${EDITOR_I18N.section_props}</h4>
            <div>
                <label class="form-label">${EDITOR_I18N.title_field}</label>
                <input type="text" class="form-input" value="${this.esc(section.title || '')}" onchange="Editor.updateSection(${idx}, 'title', this.value)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.type_field}</label>
                <select class="form-input" onchange="Editor.updateSection(${idx}, 'type', this.value)">
                    <option value="default" ${section.type==='default'?'selected':''}>${EDITOR_I18N.type_general}</option>
                    <option value="reading" ${section.type==='reading'?'selected':''}>${EDITOR_I18N.type_reading}</option>
                    <option value="listening" ${section.type==='listening'?'selected':''}>${EDITOR_I18N.type_listening}</option>
                    <option value="writing" ${section.type==='writing'?'selected':''}>${EDITOR_I18N.type_writing}</option>
                    <option value="translation" ${section.type==='translation'?'selected':''}>${EDITOR_I18N.type_translation}</option>
                    <option value="part" ${section.type==='part'?'selected':''}><?= $t('admin.templates.ed_section_part') ?></option>
                </select>
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.time_limit}</label>
                <input type="number" class="form-input" value="${section.time_limit || ''}" placeholder="${EDITOR_I18N.no_limit}" onchange="Editor.updateSection(${idx}, 'time_limit', parseInt(this.value) || null)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.instructions}</label>
                <textarea class="form-input text-xs" rows="3" placeholder="${EDITOR_I18N.instructions_ph}" onchange="Editor.updateSection(${idx}, 'instructions', this.value)">${this.esc(section.instructions || '')}</textarea>
            </div>
            <div class="pt-2 border-t border-slate-200 text-xs text-slate-400">
                <p>${EDITOR_I18N.blocks_count_suffix.replace('__N__', (section.blocks || []).length)}</p>
            </div>
        </div>`;
    },

    selectBlock(si, bi) {
        this.selectedSection = null;
        this.selectedBlock = { si, bi };
        const block = this.blueprint.sections[si].blocks[bi];
        const panel = document.getElementById('props-panel');
        panel.innerHTML = `
        <div class="space-y-4">
            <h4 class="text-xs font-semibold text-slate-500 uppercase">${EDITOR_I18N.block_props}</h4>
            <div>
                <label class="form-label">${EDITOR_I18N.label}</label>
                <input type="text" class="form-input" value="${this.esc(block.title || '')}" placeholder="${this.blockLabel(block)}" onchange="Editor.updateBlock(${si}, ${bi}, 'title', this.value)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.type_field}</label>
                <select class="form-input" onchange="Editor.updateBlock(${si}, ${bi}, 'type', this.value)">
                    <option value="single_choice" ${block.type==='single_choice'?'selected':''}>${EDITOR_I18N.block_single_choice}</option>
                    <option value="multi_choice" ${block.type==='multi_choice'?'selected':''}>${EDITOR_I18N.block_multi_choice}</option>
                    <option value="fill_blank" ${block.type==='fill_blank'?'selected':''}>${EDITOR_I18N.block_fill_blank}</option>
                    <option value="true_false" ${block.type==='true_false'?'selected':''}>${EDITOR_I18N.block_true_false}</option>
                    <option value="reading_material" ${block.type==='reading_material'?'selected':''}>${EDITOR_I18N.block_reading_material}</option>
                    <option value="writing" ${block.type==='writing'?'selected':''}>${EDITOR_I18N.block_writing}</option>
                    <option value="translation" ${block.type==='translation'?'selected':''}>${EDITOR_I18N.block_translation}</option>
                    <option value="description" ${block.type==='description'?'selected':''}><?= $t('admin.templates.ed_block_description') ?></option>
                    <option value="media" ${block.type==='media'?'selected':''}><?= $t('admin.templates.ed_block_media') ?></option>
                </select>
            </div>
            ${block.type === 'description' ? `
            <div>
                <label class="form-label">${EDITOR_I18N.content_md}</label>
                <textarea class="form-input text-xs" rows="4" placeholder="${EDITOR_I18N.content_md_ph}" onchange="Editor.updateBlock(${si}, ${bi}, 'content', this.value)">${this.esc(block.content || '')}</textarea>
            </div>` : ''}
            ${block.type === 'media' ? `
            <div>
                <label class="form-label">${EDITOR_I18N.media_type}</label>
                <select class="form-input" onchange="Editor.updateBlock(${si}, ${bi}, 'media_type', this.value)">
                    <option value="image" ${block.media_type==='image'?'selected':''}>${EDITOR_I18N.media_image}</option>
                    <option value="audio" ${block.media_type==='audio'?'selected':''}>${EDITOR_I18N.media_audio}</option>
                </select>
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.url}</label>
                <input type="text" class="form-input text-xs" value="${this.esc(block.url || '')}" placeholder="https://..." onchange="Editor.updateBlock(${si}, ${bi}, 'url', this.value)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.file_path}</label>
                <input type="text" class="form-input text-xs" value="${this.esc(block.file || '')}" placeholder="${EDITOR_I18N.file_ph}" onchange="Editor.updateBlock(${si}, ${bi}, 'file', this.value)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.caption}</label>
                <input type="text" class="form-input text-xs" value="${this.esc(block.caption || '')}" onchange="Editor.updateBlock(${si}, ${bi}, 'caption', this.value)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.link_url}</label>
                <input type="text" class="form-input text-xs" value="${this.esc(block.link_url || '')}" placeholder="https://..." onchange="Editor.updateBlock(${si}, ${bi}, 'link_url', this.value)">
            </div>` : ''}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">${EDITOR_I18N.count}</label>
                    <input type="number" class="form-input" value="${block.count || 1}" min="1" onchange="Editor.updateBlock(${si}, ${bi}, 'count', parseInt(this.value) || 1)">
                </div>
                <div>
                    <label class="form-label">${EDITOR_I18N.score_each}</label>
                    <input type="number" class="form-input" step="0.5" value="${block.score || 1}" min="0" onchange="Editor.updateBlock(${si}, ${bi}, 'score', parseFloat(this.value) || 1)">
                </div>
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.difficulty}</label>
                <input type="number" class="form-input" min="1" max="5" value="${block.difficulty || 3}" onchange="Editor.updateBlock(${si}, ${bi}, 'difficulty', parseInt(this.value) || 3)">
            </div>
            <div>
                <label class="form-label">${EDITOR_I18N.notes}</label>
                <textarea class="form-input text-xs" rows="2" placeholder="${EDITOR_I18N.notes_ph}" onchange="Editor.updateBlock(${si}, ${bi}, 'notes', this.value)">${this.esc(block.notes || '')}</textarea>
            </div>
            <div class="pt-2 border-t border-slate-200 text-xs text-slate-400">
                <p>${EDITOR_I18N.total_pts.replace('__N__', (block.count || 1) * (block.score || 1))}</p>
            </div>
        </div>`;
    },

    updateSection(idx, key, value) {
        this.blueprint.sections[idx][key] = value;
        this.dirty = true;
        this.render();
    },

    updateBlock(si, bi, key, value) {
        this.blueprint.sections[si].blocks[bi][key] = value;
        this.dirty = true;
        this.render();
        this.selectBlock(si, bi);
    },

    clearProps() {
        document.getElementById('props-panel').innerHTML = `<p class="text-sm text-slate-400 text-center py-6">${EDITOR_I18N.select_item}</p>`;
    },

    esc(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    },

    async save() {
        const name = document.getElementById('bp-name').value.trim();
        const status = document.getElementById('bp-status').value;
        try {
            await QS.fetch(`/admin/templates/${this.templateId}/update`, {
                method: 'POST',
                body: {
                    name,
                    status,
                    blueprint_json: JSON.stringify(this.blueprint)
                }
            });
            this.dirty = false;
            QS.toast(EDITOR_I18N.saved);
        } catch(e) { QS.toast(e.message, 'error'); }
    },

    schema() {
        return {
            "$schema": "https://json-schema.org/draft-07/schema#",
            "title": "PaperBlueprint",
            "type": "object",
            "required": ["sections"],
            "properties": {
                "sections": {
                    "type": "array",
                    "items": {
                        "type": "object",
                        "required": ["id", "type", "title", "blocks"],
                        "properties": {
                            "id": { "type": "string" },
                            "type": { "enum": ["default","reading","listening","writing","translation","part"] },
                            "title": { "type": "string" },
                            "time_limit": { "type": ["integer","null"], "minimum": 0 },
                            "instructions": { "type": "string" },
                            "blocks": {
                                "type": "array",
                                "items": {
                                    "type": "object",
                                    "required": ["id","type"],
                                    "properties": {
                                        "id": { "type": "string" },
                                        "type": { "enum": ["single_choice","multi_choice","fill_blank","true_false","reading_material","writing","translation","description","media","module"] },
                                        "title": { "type": "string" },
                                        "count": { "type": "integer", "minimum": 1, "default": 1 },
                                        "score": { "type": "number", "minimum": 0, "default": 1 },
                                        "difficulty": { "type": "integer", "minimum": 1, "maximum": 5 },
                                        "notes": { "type": "string" },
                                        "module_id": { "type": ["integer","null"] }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        };
    },

    exportSchema() {
        const blob = new Blob([JSON.stringify(this.schema(), null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'paper-blueprint.schema.json';
        a.click();
        URL.revokeObjectURL(url);
        QS.toast(EDITOR_I18N.schema_downloaded);
    },

    copySchema() {
        navigator.clipboard.writeText(JSON.stringify(this.schema(), null, 2))
            .then(() => QS.toast(EDITOR_I18N.schema_copied))
            .catch(() => QS.toast(EDITOR_I18N.copy_failed, 'error'));
    },

    openImport() {
        document.getElementById('import-json').value = '';
        document.getElementById('import-error').classList.add('hidden');
        document.getElementById('import-modal').classList.remove('hidden');
    },

    closeImport() {
        document.getElementById('import-modal').classList.add('hidden');
    },

    validateBlueprint(bp) {
        if (!bp || typeof bp !== 'object') return EDITOR_I18N.err_root;
        if (!Array.isArray(bp.sections)) return EDITOR_I18N.err_sections;
        const validSectionTypes = ['default','reading','listening','writing','translation','part'];
        const validBlockTypes = ['single_choice','multi_choice','fill_blank','true_false','reading_material','writing','translation','description','media','module'];
        for (let i = 0; i < bp.sections.length; i++) {
            const s = bp.sections[i];
            if (!s || typeof s !== 'object') return EDITOR_I18N.err_section_invalid.replace('__I__', i);
            if (!validSectionTypes.includes(s.type)) return EDITOR_I18N.err_section_type.replace('__I__', i).replace('__T__', s.type);
            if (typeof s.title !== 'string') return EDITOR_I18N.err_section_title.replace('__I__', i);
            if (!Array.isArray(s.blocks)) return EDITOR_I18N.err_section_blocks.replace('__I__', i);
            for (let j = 0; j < s.blocks.length; j++) {
                const b = s.blocks[j];
                if (!b || typeof b !== 'object') return EDITOR_I18N.err_block_invalid.replace('__I__', i).replace('__J__', j);
                if (!validBlockTypes.includes(b.type)) return EDITOR_I18N.err_block_type.replace('__I__', i).replace('__J__', j).replace('__T__', b.type);
            }
        }
        return null;
    },

    applyImport() {
        const raw = document.getElementById('import-json').value.trim();
        const errBox = document.getElementById('import-error');
        errBox.classList.add('hidden');
        if (!raw) { errBox.textContent = EDITOR_I18N.err_paste_first; errBox.classList.remove('hidden'); return; }
        let parsed;
        try { parsed = JSON.parse(raw); } catch(e) {
            errBox.textContent = EDITOR_I18N.err_invalid_json.replace('__MSG__', e.message);
            errBox.classList.remove('hidden');
            return;
        }
        const err = this.validateBlueprint(parsed);
        if (err) { errBox.textContent = err; errBox.classList.remove('hidden'); return; }
        // Normalize: ensure ids
        parsed.sections.forEach((s, i) => {
            if (!s.id) s.id = 'section_' + Date.now() + '_' + i;
            s.blocks.forEach((b, j) => { if (!b.id) b.id = 'block_' + Date.now() + '_' + i + '_' + j; });
        });
        this.blueprint = parsed;
        this.dirty = true;
        this.selectedSection = null;
        this.selectedBlock = null;
        this.render();
        this.clearProps();
        this.closeImport();
        this.save();
    }
};

document.addEventListener('DOMContentLoaded', () => Editor.init());
</script>
