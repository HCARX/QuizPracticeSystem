<!-- Reset Password -->
<div class="max-w-sm mx-auto py-16 px-4">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900"><?= $t('auth.reset_password') ?></h2>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <?php if (!empty($error)): ?>
        <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" action="/reset-password/<?= htmlspecialchars($token) ?>" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div>
                <label class="form-label"><?= $t('auth.new_password') ?></label>
                <input type="password" name="new_password" class="form-input" required minlength="6" autofocus>
            </div>
            <div>
                <label class="form-label"><?= $t('auth.confirm_password') ?></label>
                <input type="password" name="confirm_password" class="form-input" required minlength="6">
            </div>
            <button type="submit" class="btn-primary w-full"><?= $t('auth.reset_password') ?></button>
        </form>
    </div>
</div>
