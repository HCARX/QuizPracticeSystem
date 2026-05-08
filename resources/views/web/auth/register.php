<!-- User Registration -->
<div class="max-w-sm mx-auto py-16 px-4">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900"><?= $t('auth.create_account') ?></h2>
        <p class="text-sm text-slate-500 mt-1"><?= $t('auth.start_practicing') ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <?php if (!empty($error)): ?>
        <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div>
                <label class="form-label"><?= $t('auth.username') ?></label>
                <input type="text" name="username" class="form-input" required autofocus minlength="3" maxlength="50" value="<?= htmlspecialchars($old_username ?? '') ?>">
            </div>
            <div>
                <label class="form-label"><?= $t('auth.email') ?? 'Email' ?></label>
                <input type="email" name="email" class="form-input" required maxlength="100" value="<?= htmlspecialchars($old_email ?? '') ?>">
            </div>
            <div>
                <label class="form-label"><?= $t('auth.password') ?></label>
                <input type="password" name="password" class="form-input" required minlength="6">
                <p class="text-xs text-slate-400 mt-1"><?= $t('auth.password_hint') ?></p>
            </div>
            <button type="submit" class="btn-primary w-full"><?= $t('auth.create_account') ?></button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            <?= $t('auth.have_account') ?> <a href="/login" class="text-primary-600 font-medium hover:text-primary-700"><?= $t('auth.sign_in_link') ?></a>
        </p>
    </div>
</div>
