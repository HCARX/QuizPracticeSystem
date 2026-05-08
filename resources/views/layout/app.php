<!DOCTYPE html>
<html lang="<?= $htmlLang ?? 'zh-CN' ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Quiz Practice System') ?></title>
    <meta name="csrf-token" content="<?= $csrf_token ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: { 50:'#EFF6FF',100:'#DBEAFE',200:'#BFDBFE',300:'#93C5FD',400:'#60A5FA',500:'#3B82F6',600:'#2563EB',700:'#1D4ED8',800:'#1E40AF',900:'#1E3A8A' },
                },
                fontFamily: {
                    sans: ['-apple-system','BlinkMacSystemFont','Segoe UI','Inter','Roboto','sans-serif']
                }
            }
        }
    }
    </script>
    <style type="text/tailwindcss">
        .btn-primary { @apply inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors; }
        .btn-secondary { @apply inline-flex items-center justify-center px-5 py-2.5 bg-white text-slate-700 text-sm font-medium rounded-lg border border-slate-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors; }
        .form-input { @apply block w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors; }
        .form-label { @apply block text-sm font-medium text-slate-700 mb-1.5; }
        .card { @apply bg-white rounded-xl border border-slate-200 shadow-sm; }
        .badge { @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium; }
        .badge-success { @apply bg-emerald-50 text-emerald-700; }
        .badge-warning { @apply bg-amber-50 text-amber-700; }
        .badge-info { @apply bg-blue-50 text-blue-700; }
        .badge-danger { @apply bg-red-50 text-red-700; }
        .badge-gray { @apply bg-slate-100 text-slate-600; }
    </style>
    <?= $headExtra ?? '' ?>
</head>
<body class="h-full bg-slate-50 font-sans antialiased">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="text-lg font-bold text-slate-900"><?= $t('nav.brand') ?></span>
                </a>

                <div class="hidden sm:flex items-center gap-6">
                    <a href="/" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"><?= $t('nav.home') ?></a>
                    <?php if ($auth): ?>
                        <a href="/profile" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"><?= $t('nav.progress') ?></a>
                        <a href="/vocabulary" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"><?= $t('nav.vocabulary') ?></a>
                        <a href="/locale/<?= $locale === 'zh' ? 'en' : 'zh' ?>" class="text-xs font-semibold text-slate-500 hover:text-primary-600 px-2 py-1 rounded-md border border-slate-200"><?= $t('lang.toggle') ?></a>
                        <div class="flex items-center gap-3 pl-4 border-l border-slate-200">
                            <a href="/settings" class="flex items-center gap-2 group">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center overflow-hidden group-hover:ring-2 group-hover:ring-primary-300 transition">
                                    <?php if (!empty($auth['avatar'])): ?>
                                        <span class="text-lg leading-none"><?= htmlspecialchars($auth['avatar']) ?></span>
                                    <?php else: ?>
                                        <span class="text-sm font-semibold text-primary-700"><?= strtoupper(substr($auth['display_name'] ?: $auth['username'], 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-sm font-medium text-slate-700 group-hover:text-primary-600"><?= htmlspecialchars($auth['display_name'] ?: $auth['username']) ?></span>
                            </a>
                            <a href="/logout" class="text-sm text-slate-400 hover:text-red-500 transition-colors"><?= $t('nav.logout') ?></a>
                        </div>
                    <?php else: ?>
                        <a href="/locale/<?= $locale === 'zh' ? 'en' : 'zh' ?>" class="text-xs font-semibold text-slate-500 hover:text-primary-600 px-2 py-1 rounded-md border border-slate-200"><?= $t('lang.toggle') ?></a>
                        <a href="/login" class="btn-primary text-sm"><?= $t('nav.signin') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <main>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-4 py-3 text-sm text-emerald-800"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-800"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
        <?= $content ?>
    </main>

    <!-- Toast -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 space-y-3"></div>

    <script>
    const QS = {
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        async fetch(url, options = {}) {
            const defaults = {
                headers: { 'X-CSRF-TOKEN': this.csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            };
            if (options.body && !(options.body instanceof FormData)) {
                defaults.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.body);
            }
            const config = { ...defaults, ...options, headers: { ...defaults.headers, ...options.headers } };
            const response = await fetch(url, config);
            if (!response.ok) { const err = await response.json().catch(() => ({})); throw new Error(err.error || 'Request failed'); }
            return response.json();
        },
        toast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = { success: 'bg-emerald-600', error: 'bg-red-600', info: 'bg-primary-600' };
            const el = document.createElement('div');
            el.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg text-sm font-medium`;
            el.textContent = message;
            container.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        },
        confirm(message) {
            return new Promise(resolve => {
                const overlay = document.createElement('div');
                overlay.className = 'fixed inset-0 bg-black/40 flex items-center justify-center z-[60]';
                overlay.innerHTML = `<div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 overflow-hidden">
                    <div class="p-6"><p class="text-sm text-slate-700">${message}</p></div>
                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                        <button class="btn-secondary" data-action="cancel"><?= $t('common.cancel') ?></button>
                        <button class="btn-primary bg-red-600 hover:bg-red-700" data-action="confirm"><?= $t('common.confirm') ?></button>
                    </div></div>`;
                document.body.appendChild(overlay);
                overlay.addEventListener('click', e => {
                    const action = e.target.dataset.action;
                    if (action) { overlay.remove(); resolve(action === 'confirm'); }
                });
            });
        }
    };
    </script>
    <?php if ($auth): ?>
    <!-- Word Selection Popup (划词翻译) -->
    <div id="word-select-btn" class="fixed z-50 hidden">
        <button onclick="WordSelect.explain()" class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-lg shadow-lg hover:bg-primary-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Explain
        </button>
    </div>
    <div id="word-popup" class="fixed z-50 hidden max-w-sm w-80">
        <div class="bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <svg class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span id="word-popup-word" class="text-sm font-semibold text-slate-900 truncate"></span>
                </div>
                <button onclick="WordSelect.close()" class="text-slate-400 hover:text-slate-600 flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="word-popup-body" class="px-4 py-3 text-sm text-slate-700 leading-relaxed max-h-64 overflow-y-auto">
                <div class="flex items-center justify-center py-6">
                    <div class="w-5 h-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
                    <span class="ml-2 text-sm text-slate-400">Analyzing...</span>
                </div>
            </div>
            <div id="word-popup-actions" class="px-4 py-3 border-t border-slate-200 bg-slate-50 hidden">
                <button onclick="WordSelect.addToVocabulary()" class="w-full text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center justify-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add to Vocabulary
                </button>
            </div>
        </div>
    </div>
    <script>
    const WordSelect = {
        selectedWord: '',
        selectedSentence: '',
        init() {
            document.addEventListener('mouseup', (e) => {
                if (e.target.closest('#word-select-btn') || e.target.closest('#word-popup')) return;
                setTimeout(() => this.onSelection(e), 10);
            });
            document.addEventListener('mousedown', (e) => {
                if (!e.target.closest('#word-select-btn') && !e.target.closest('#word-popup')) {
                    this.hideBtn();
                }
            });
        },
        onSelection(e) {
            const sel = window.getSelection();
            const text = sel.toString().trim();
            if (!text || text.length < 1 || text.length > 100 || text.includes('\n')) {
                this.hideBtn();
                return;
            }
            this.selectedWord = text;
            const range = sel.getRangeAt(0);
            const container = range.commonAncestorContainer;
            const parent = container.nodeType === 3 ? container.parentElement : container;
            const block = parent.closest('.quiz-text, p, td, span, div');
            this.selectedSentence = block ? block.textContent.trim().substring(0, 500) : '';
            const rect = range.getBoundingClientRect();
            const btn = document.getElementById('word-select-btn');
            btn.style.left = (rect.left + rect.width / 2 - 40) + 'px';
            btn.style.top = (rect.top + window.scrollY - 36) + 'px';
            btn.classList.remove('hidden');
        },
        hideBtn() { document.getElementById('word-select-btn').classList.add('hidden'); },
        async explain() {
            this.hideBtn();
            const popup = document.getElementById('word-popup');
            const body = document.getElementById('word-popup-body');
            const actions = document.getElementById('word-popup-actions');
            document.getElementById('word-popup-word').textContent = this.selectedWord;
            body.innerHTML = '<div class="flex items-center justify-center py-6"><div class="w-5 h-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin"></div><span class="ml-2 text-sm text-slate-400">Analyzing...</span></div>';
            actions.classList.add('hidden');
            const btn = document.getElementById('word-select-btn');
            const x = parseInt(btn.style.left) - 100;
            const y = parseInt(btn.style.top) + 40;
            popup.style.left = Math.max(8, Math.min(x, window.innerWidth - 330)) + 'px';
            popup.style.top = y + 'px';
            popup.classList.remove('hidden');
            try {
                const res = await QS.fetch('/api/ai/explain-word', {
                    method: 'POST',
                    body: { word: this.selectedWord, sentence: this.selectedSentence }
                });
                const explanation = res.explanation || res.result || 'No explanation available.';
                body.innerHTML = explanation.replace(/\n/g, '<br>');
                actions.classList.remove('hidden');
            } catch (e) {
                body.innerHTML = '<p class="text-red-500">'+e.message+'</p>';
            }
        },
        close() { document.getElementById('word-popup').classList.add('hidden'); },
        async addToVocabulary() {
            try {
                await QS.fetch('/vocabulary', {
                    method: 'POST',
                    body: { word: this.selectedWord, sentence: this.selectedSentence }
                });
                QS.toast('Added to vocabulary');
            } catch (e) { QS.toast(e.message, 'error'); }
        }
    };
    document.addEventListener('DOMContentLoaded', () => WordSelect.init());
    </script>
    <?php endif; ?>
    <?= $scripts ?? '' ?>
</body>
</html>
