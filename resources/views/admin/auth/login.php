<!DOCTYPE html>
<html lang="<?= htmlspecialchars($locale ?? 'zh') ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t('admin.auth.title') ?> - <?= $t('admin.brand') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: { 50:'#EFF6FF',100:'#DBEAFE',500:'#3B82F6',600:'#2563EB',700:'#1D4ED8',900:'#1E3A8A' }
                }
            }
        }
    }
    </script>
</head>
<body class="h-full bg-slate-50 font-sans antialiased">
<div class="min-h-full flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900"><?= $t('admin.auth.console') ?></h1>
            <p class="text-sm text-slate-500 mt-1"><?= $t('admin.auth.subtitle') ?></p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            <?php if (!empty($error)): ?>
            <div class="mb-6 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/login" class="space-y-5">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">

                <div>
                    <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5"><?= $t('admin.auth.username') ?></label>
                    <input type="text" id="username" name="username" required autofocus
                           class="block w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                           placeholder="<?= $t('admin.auth.username_ph') ?>"
                           value="<?= htmlspecialchars($old_username ?? '') ?>">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5"><?= $t('admin.auth.password') ?></label>
                    <input type="password" id="password" name="password" required
                           class="block w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                           placeholder="<?= $t('admin.auth.password_ph') ?>">
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors">
                    <?= $t('admin.auth.sign_in') ?>
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6"><?= $t('footer.name') ?></p>
    </div>
</div>
</body>
</html>
