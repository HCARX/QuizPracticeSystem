<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="mb-8">
        <a href="/" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            <?= $t('practice.back') ?>
        </a>
        <h1 class="text-2xl font-bold text-slate-900"><?= $t('practice.title') ?></h1>
        <p class="text-sm text-slate-500 mt-1"><?= $t('practice.subtitle') ?></p>
    </div>

    <form method="POST" action="/practice/start" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

        <div>
            <label class="form-label"><?= $t('practice.subject') ?></label>
            <select name="subject_id" class="form-input">
                <option value="0"><?= $t('practice.all_subjects') ?></option>
                <?php foreach ($subjects as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['alias'] ?: $s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="form-label"><?= $t('practice.question_types') ?></label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <?php foreach ($types as $key => $label): ?>
                <label class="flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm cursor-pointer hover:bg-slate-100 has-[:checked]:bg-primary-50 has-[:checked]:border-primary-300 has-[:checked]:text-primary-700">
                    <input type="checkbox" name="types[]" value="<?= $key ?>" class="rounded text-primary-600 focus:ring-primary-500">
                    <span><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-slate-400 mt-1.5"><?= $t('practice.types_hint') ?></p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="form-label"><?= $t('practice.difficulty') ?></label>
                <select name="difficulty" class="form-input">
                    <option value="0"><?= $t('practice.any') ?></option>
                    <option value="1"><?= $t('practice.diff_1') ?></option>
                    <option value="2"><?= $t('practice.diff_2') ?></option>
                    <option value="3"><?= $t('practice.diff_3') ?></option>
                    <option value="4"><?= $t('practice.diff_4') ?></option>
                    <option value="5"><?= $t('practice.diff_5') ?></option>
                </select>
            </div>
            <div>
                <label class="form-label"><?= $t('practice.question_count') ?></label>
                <input type="number" name="count" value="10" min="1" max="100" class="form-input">
            </div>
        </div>

        <div>
            <label class="form-label"><?= $t('practice.source') ?></label>
            <div class="grid grid-cols-3 gap-2">
                <?php foreach (['all' => $t('practice.source_all'), 'mistakes' => $t('practice.source_mistakes'), 'favorites' => $t('practice.source_favorites')] as $val => $label): ?>
                <label class="flex items-center justify-center px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm cursor-pointer hover:bg-slate-100 has-[:checked]:bg-primary-50 has-[:checked]:border-primary-300 has-[:checked]:text-primary-700 has-[:checked]:font-semibold">
                    <input type="radio" name="source" value="<?= $val ?>" class="sr-only" <?= $val === 'all' ? 'checked' : '' ?>>
                    <span><?= $label ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="shuffle" value="1" checked class="rounded text-primary-600 focus:ring-primary-500">
                <?= $t('practice.shuffle_label') ?>
            </label>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
            <a href="/" class="btn-secondary"><?= $t('common.cancel') ?></a>
            <button type="submit" class="btn-primary">
                <?= $t('practice.start') ?>
                <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </form>
</div>
