<!-- User Settings -->
<div class="max-w-3xl mx-auto px-4 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900"><?= $t('settings.title') ?></h1>
        <p class="text-sm text-slate-500 mt-1"><?= $t('settings.subtitle') ?></p>
    </div>

    <!-- Profile Card -->
    <form method="POST" action="/settings" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
        <h2 class="text-lg font-semibold text-slate-900"><?= $t('settings.profile') ?></h2>

        <!-- Avatar Preview + Emoji Picker -->
        <div>
            <label class="form-label"><?= $t('settings.avatar') ?></label>
            <div class="flex items-center gap-4">
                <div id="avatar-preview" class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center text-3xl">
                    <?php if (!empty($user['avatar'])): ?>
                        <span><?= htmlspecialchars($user['avatar']) ?></span>
                    <?php else: ?>
                        <span class="text-xl font-semibold text-primary-700"><?= strtoupper(substr($user['display_name'] ?: $user['username'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <input type="text" id="avatar-input" name="avatar" maxlength="8" value="<?= htmlspecialchars($user['avatar'] ?? '') ?>" placeholder="😀" class="form-input w-24 text-center text-xl">
                <button type="button" id="clear-avatar" class="text-xs text-slate-500 hover:text-red-500">×</button>
            </div>
            <p class="text-xs text-slate-500 mt-2"><?= $t('settings.avatar_hint') ?></p>
            <div id="emoji-grid" class="mt-3 grid grid-cols-10 gap-1 p-3 bg-slate-50 rounded-lg border border-slate-200 text-2xl">
                <?php foreach (['😀','😎','🤓','🥳','🤠','🦊','🐼','🐱','🐶','🦁','🐯','🐸','🐵','🦄','🐙','🦉','🐺','🐨','🦝','🐧','🌸','🌼','🌻','🌺','🍀','🍎','🍊','🍋','🍉','🍇','🍓','🥝','🍒','🥑','🌈','⭐','🔥','💎','🎯','🚀','📚','✏️','🎨','🎧','⚽','🏆','🎮','🧩','🧠','💡'] as $e): ?>
                    <button type="button" class="emoji-btn w-8 h-8 rounded hover:bg-white transition"><?= $e ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <label class="form-label"><?= $t('settings.username') ?></label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled class="form-input bg-slate-50 text-slate-500 cursor-not-allowed">
            <p class="text-xs text-slate-500 mt-1"><?= $t('settings.username_hint') ?></p>
        </div>

        <div>
            <label class="form-label"><?= $t('settings.display_name') ?></label>
            <input type="text" name="display_name" maxlength="100" value="<?= htmlspecialchars($user['display_name'] ?? '') ?>" class="form-input">
        </div>

        <div>
            <label class="form-label"><?= $t('settings.email') ?></label>
            <input type="email" name="email" maxlength="100" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="form-input">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary"><?= $t('settings.save') ?></button>
        </div>
    </form>

    <!-- Password Card -->
    <form method="POST" action="/settings/password" class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
        <h2 class="text-lg font-semibold text-slate-900"><?= $t('settings.password') ?></h2>

        <div>
            <label class="form-label"><?= $t('settings.current_password') ?></label>
            <input type="password" name="current_password" required class="form-input">
        </div>

        <div>
            <label class="form-label"><?= $t('settings.new_password') ?></label>
            <input type="password" name="new_password" required minlength="6" class="form-input">
        </div>

        <div>
            <label class="form-label"><?= $t('settings.confirm_password') ?></label>
            <input type="password" name="confirm_password" required minlength="6" class="form-input">
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary"><?= $t('settings.update_password') ?></button>
        </div>
    </form>
</div>

<script>
(function() {
    const input = document.getElementById('avatar-input');
    const preview = document.getElementById('avatar-preview');
    const initial = <?= json_encode(strtoupper(substr($user['display_name'] ?: $user['username'], 0, 1))) ?>;

    function render() {
        const v = input.value.trim();
        if (v) {
            preview.innerHTML = '<span>' + v.replace(/[<>&]/g, '') + '</span>';
        } else {
            preview.innerHTML = '<span class="text-xl font-semibold text-primary-700">' + initial + '</span>';
        }
    }

    input.addEventListener('input', render);
    document.getElementById('clear-avatar').addEventListener('click', () => { input.value = ''; render(); });
    document.querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', () => { input.value = btn.textContent.trim(); render(); });
    });
})();
</script>
