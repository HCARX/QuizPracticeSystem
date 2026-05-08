<!-- Forgot Password -->
<div class="max-w-sm mx-auto py-16 px-4">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900"><?= $t('auth.reset_title') ?></h2>
        <p class="text-sm text-slate-500 mt-1"><?= $t('auth.reset_subtitle') ?></p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="mb-6 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
            <p class="text-sm text-emerald-700"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-sm text-red-700"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <form method="POST" action="/forgot-password" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
            <div>
                <label class="form-label"><?= $t('auth.email') ?></label>
                <input type="email" name="email" class="form-input" required autofocus>
            </div>
            <button type="submit" class="btn-primary w-full"><?= $t('auth.send_link') ?></button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
            <a href="/login" class="text-primary-600 font-medium hover:text-primary-700"><?= $t('auth.sign_in_link') ?></a>
        </p>
    </div>
</div>
