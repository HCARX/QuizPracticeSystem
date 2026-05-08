<!-- User Login -->
<div class="max-w-sm mx-auto py-16 px-4">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900"><?= $t('auth.welcome_back') ?></h2>
        <p class="text-sm text-slate-500 mt-1"><?= $t('auth.sign_in_subtitle') ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <?php if (!empty($error)): ?>
        <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div>
                <label class="form-label"><?= $t('auth.username') ?> / Email</label>
                <input type="text" name="username" class="form-input" required autofocus value="<?= htmlspecialchars($old_username ?? '') ?>">
            </div>
            <div>
                <label class="form-label"><?= $t('auth.password') ?></label>
                <input type="password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="btn-primary w-full"><?= $t('auth.login') ?></button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-4">
            <a href="/forgot-password" class="text-primary-600 font-medium hover:text-primary-700"><?= $t('auth.forgot') ?></a>
        </p>

        <p class="text-center text-sm text-slate-500 mt-6">
            <?= $t('auth.no_account') ?> <a href="/register" class="text-primary-600 font-medium hover:text-primary-700"><?= $t('auth.create_one') ?></a>
        </p>
    </div>
</div>
