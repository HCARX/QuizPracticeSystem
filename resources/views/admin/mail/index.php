<!-- Admin Mail Settings -->
<div class="space-y-6 max-w-3xl">
    <p class="text-sm text-slate-500"><?= $t('admin.mail.subtitle') ?></p>

    <div class="card p-6">
        <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200 mb-5"><?= $t('admin.mail.smtp_title') ?></h3>
        <form method="POST" action="/admin/mail" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label"><?= $t('admin.mail.smtp_host') ?></label>
                    <input type="text" name="smtp_host" class="form-input" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.example.com">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.smtp_port') ?></label>
                    <input type="number" name="smtp_port" class="form-input" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.username') ?></label>
                    <input type="text" name="smtp_user" class="form-input" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.password') ?></label>
                    <input type="password" name="smtp_pass" class="form-input" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.encryption') ?></label>
                    <select name="smtp_encryption" class="form-input">
                        <?php $enc = $settings['smtp_encryption'] ?? 'tls'; ?>
                        <option value="none" <?= $enc === 'none' ? 'selected' : '' ?>><?= $t('admin.mail.enc_none') ?></option>
                        <option value="ssl" <?= $enc === 'ssl' ? 'selected' : '' ?>><?= $t('admin.mail.enc_ssl') ?></option>
                        <option value="tls" <?= $enc === 'tls' ? 'selected' : '' ?>><?= $t('admin.mail.enc_tls') ?></option>
                    </select>
                </div>
                <div></div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.from_email') ?></label>
                    <input type="email" name="from_email" class="form-input" value="<?= htmlspecialchars($settings['from_email'] ?? '') ?>" placeholder="noreply@example.com">
                </div>
                <div>
                    <label class="form-label"><?= $t('admin.mail.from_name') ?></label>
                    <input type="text" name="from_name" class="form-input" value="<?= htmlspecialchars($settings['from_name'] ?? 'Quiz System') ?>">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-primary"><?= $t('common.save') ?></button>
            </div>
        </form>
    </div>

    <div class="card p-6">
        <h3 class="text-sm font-semibold text-slate-900 pb-3 border-b border-slate-200 mb-5"><?= $t('admin.mail.test_title') ?></h3>
        <form method="POST" action="/admin/mail/test" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div>
                <label class="form-label"><?= $t('admin.mail.recipient') ?></label>
                <input type="email" name="to" class="form-input" required placeholder="you@example.com">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary"><?= $t('admin.mail.send_test') ?></button>
            </div>
        </form>
    </div>
</div>
