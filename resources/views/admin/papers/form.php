<!-- Paper Create/Edit Form -->
<div class="max-w-3xl">
    <form id="paper-form" method="POST" action="<?= $paper ? "/admin/papers/{$paper['id']}/update" : '/admin/papers' ?>" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

        <div class="card p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200"><?= $t('admin.papers.basic_info') ?></h3>

            <div>
                <label class="form-label"><?= $t('admin.papers.subject') ?> <span class="text-red-500">*</span></label>
                <select name="subject_id" class="form-input" required>
                    <option value=""><?= $t('admin.papers.select_subject') ?></option>
                    <?php foreach ($subjects as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= ($paper['subject_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label"><?= $t('admin.common.title') ?> <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="form-input" placeholder="<?= $t('admin.papers.title_ph') ?>" value="<?= htmlspecialchars($paper['title'] ?? '') ?>" required>
            </div>

            <div>
                <label class="form-label"><?= $t('admin.papers.subtitle') ?></label>
                <input type="text" name="subtitle" class="form-input" placeholder="<?= $t('admin.papers.subtitle_ph') ?>" value="<?= htmlspecialchars($paper['subtitle'] ?? '') ?>">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.papers.year') ?></label>
                    <input type="text" name="year" class="form-input" placeholder="2023" value="<?= htmlspecialchars($paper['year'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.papers.month') ?></label>
                    <input type="text" name="month" class="form-input" placeholder="06" value="<?= htmlspecialchars($paper['month'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.papers.duration') ?></label>
                    <input type="number" name="duration" class="form-input" placeholder="120" value="<?= $paper['duration'] ?? 120 ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.papers.total_score') ?></label>
                    <input type="number" name="total_score" class="form-input" placeholder="710" value="<?= $paper['total_score'] ?? '' ?>" step="0.5">
                </div>
            </div>

            <div>
                <label class="form-label"><?= $t('admin.papers.difficulty') ?></label>
                <div class="flex items-center gap-4 mt-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <label class="flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="difficulty" value="<?= $i ?>" class="text-primary-600 focus:ring-primary-500" <?= ($paper['difficulty'] ?? 3) == $i ? 'checked' : '' ?>>
                        <span class="text-sm text-slate-600"><?= $i ?></span>
                    </label>
                    <?php endfor; ?>
                </div>
            </div>

            <div>
                <label class="form-label"><?= $t('admin.papers.description') ?></label>
                <textarea name="description" class="form-input" rows="3" placeholder="<?= $t('admin.papers.desc_ph') ?>"><?= htmlspecialchars($paper['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="/admin/papers" class="btn-secondary"><?= $t('admin.common.cancel') ?></a>
            <button type="submit" class="btn-primary"><?= $paper ? $t('admin.papers.update_btn') : $t('admin.papers.create_btn') ?></button>
        </div>
    </form>
</div>
