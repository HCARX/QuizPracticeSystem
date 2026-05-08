<!DOCTYPE html>
<html lang="<?= $htmlLang ?? 'zh-CN' ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - Quiz System</title>
    <meta name="csrf-token" content="<?= $csrf_token ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: { 50:'#EFF6FF',100:'#DBEAFE',200:'#BFDBFE',300:'#93C5FD',400:'#60A5FA',500:'#3B82F6',600:'#2563EB',700:'#1D4ED8',800:'#1E40AF',900:'#1E3A8A' },
                    surface: { 50:'#F8FAFC',100:'#F1F5F9',200:'#E2E8F0',300:'#CBD5E1' }
                },
                fontFamily: {
                    sans: ['-apple-system','BlinkMacSystemFont','Segoe UI','Inter','Roboto','sans-serif']
                }
            }
        }
    }
    </script>
    <style type="text/tailwindcss">
        [x-cloak] { display: none !important; }
        .sidebar-link { @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors; }
        .sidebar-link.active { @apply text-primary-700 bg-primary-50 font-semibold; }
        .btn-primary { @apply inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors disabled:opacity-50; }
        .btn-secondary { @apply inline-flex items-center justify-center px-4 py-2 bg-white text-slate-700 text-sm font-medium rounded-lg border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors; }
        .btn-danger { @apply inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors; }
        .btn-ghost { @apply inline-flex items-center justify-center px-3 py-1.5 text-slate-500 text-sm font-medium rounded-lg hover:text-slate-700 hover:bg-slate-100 transition-colors; }
        .form-input { @apply block w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors; }
        .form-label { @apply block text-sm font-medium text-slate-700 mb-1.5; }
        .card { @apply bg-white rounded-xl border border-slate-200 shadow-sm; }
        .badge { @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium; }
        .badge-success { @apply bg-emerald-50 text-emerald-700; }
        .badge-warning { @apply bg-amber-50 text-amber-700; }
        .badge-info { @apply bg-blue-50 text-blue-700; }
        .badge-danger { @apply bg-red-50 text-red-700; }
        .badge-gray { @apply bg-slate-100 text-slate-600; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
    </style>
    <?= $headExtra ?? '' ?>
</head>
<body class="h-full bg-surface-50 font-sans antialiased">
<div class="flex h-full">

    <!-- Sidebar -->
    <aside class="hidden lg:flex lg:flex-col w-64 border-r border-slate-200 bg-white">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-200">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <span class="text-lg font-bold text-slate-900 tracking-tight"><?= $t('admin.brand') ?></span>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <a href="/admin/dashboard" class="sidebar-link <?= ($currentNav ?? '') === 'dashboard' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                <?= $t('admin.dashboard') ?>
            </a>

            <div class="pt-4 pb-1 px-3"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wider"><?= $t('admin.content_group') ?></p></div>

            <a href="/admin/subjects" class="sidebar-link <?= ($currentNav ?? '') === 'subjects' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <?= $t('admin.subjects') ?>
            </a>
            <a href="/admin/papers" class="sidebar-link <?= ($currentNav ?? '') === 'papers' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <?= $t('admin.papers') ?>
            </a>
            <a href="/admin/templates" class="sidebar-link <?= ($currentNav ?? '') === 'templates' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                <?= $t('admin.templates') ?>
            </a>
            <a href="/admin/questions" class="sidebar-link <?= ($currentNav ?? '') === 'questions' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?= $t('admin.questions') ?>
            </a>

            <div class="pt-4 pb-1 px-3"><p class="text-xs font-semibold text-slate-400 uppercase tracking-wider"><?= $t('admin.system_group') ?></p></div>

            <a href="/admin/ai" class="sidebar-link <?= ($currentNav ?? '') === 'ai' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <?= $t('admin.ai') ?>
            </a>
            <a href="/admin/users" class="sidebar-link <?= ($currentNav ?? '') === 'users' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197V21"/></svg>
                <?= $t('admin.users') ?>
            </a>
            <a href="/admin/mail" class="sidebar-link <?= ($currentNav ?? '') === 'mail' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <?= $t('admin.mail') ?>
            </a>
            <a href="/admin/logs" class="sidebar-link <?= ($currentNav ?? '') === 'logs' ? 'active' : '' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <?= $t('admin.logs') ?>
            </a>
        </nav>

        <!-- User -->
        <div class="px-4 py-3 border-t border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-semibold text-primary-700"><?= strtoupper(substr($auth['username'] ?? 'A', 0, 1)) ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate"><?= htmlspecialchars($auth['username'] ?? 'Admin') ?></p>
                    <p class="text-xs text-slate-500"><?= htmlspecialchars($auth['role'] ?? '') ?></p>
                </div>
                <a href="/admin/logout" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Top Bar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0">
            <div class="flex items-center gap-4">
                <!-- Mobile menu toggle -->
                <button id="sidebar-toggle" class="lg:hidden text-slate-500 hover:text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-lg font-semibold text-slate-900"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="/" target="_blank" class="btn-ghost text-xs"><?= $t('admin.view_site') ?></a>
                <a href="/locale/<?= $locale === 'zh' ? 'en' : 'zh' ?>" class="text-xs font-semibold text-slate-500 hover:text-primary-600 px-2 py-1 rounded-md border border-slate-200"><?= $t('lang.toggle') ?></a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-emerald-800"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
            </div>
            <?php unset($_SESSION['flash_success']); endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-red-800"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <?php unset($_SESSION['flash_error']); endif; ?>

            <?= $content ?>
        </div>
    </main>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

<script>
const QS = {
    csrfToken: document.querySelector('meta[name="csrf-token"]').content,

    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        };
        if (options.body && !(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }
        const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };
        const response = await fetch(url, config);
        if (!response.ok) {
            const err = await response.json().catch(() => ({ error: 'Request failed' }));
            throw new Error(err.error || 'Request failed');
        }
        return response.json();
    },

    toast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-primary-600', warning: 'bg-amber-500' };
        const el = document.createElement('div');
        el.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium transform transition-all duration-300 translate-y-2 opacity-0`;
        el.textContent = message;
        container.appendChild(el);
        requestAnimationFrame(() => { el.classList.remove('translate-y-2', 'opacity-0'); });
        setTimeout(() => {
            el.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => el.remove(), 300);
        }, 3000);
    },

    confirm(message) {
        return new Promise(resolve => {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 bg-black/40 flex items-center justify-center z-50';
            overlay.innerHTML = `
                <div class="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                    <p class="text-sm text-slate-700 mb-6">${message}</p>
                    <div class="flex justify-end gap-3">
                        <button class="btn-secondary" data-action="cancel"><?= $t('common.cancel') ?></button>
                        <button class="btn-danger" data-action="confirm"><?= $t('common.confirm') ?></button>
                    </div>
                </div>`;
            document.body.appendChild(overlay);
            overlay.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                if (action) { overlay.remove(); resolve(action === 'confirm'); }
            });
        });
    }
};
</script>
<?= $scripts ?? '' ?>
</body>
</html>
