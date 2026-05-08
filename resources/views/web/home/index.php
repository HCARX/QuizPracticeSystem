<!-- Home Page -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Hero -->
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-slate-900 mb-3"><?= $t('home.hero_title') ?></h1>
        <p class="text-base text-slate-500 max-w-xl mx-auto"><?= $t('home.hero_subtitle') ?></p>
    </div>

    <?php if (!empty($ongoing)): ?>
    <!-- Resume Banner -->
    <div class="max-w-3xl mx-auto mb-8">
        <a href="/quiz/<?= (int)$ongoing['id'] ?>" class="flex items-center justify-between p-5 bg-gradient-to-r from-primary-600 to-indigo-600 rounded-2xl text-white shadow-sm hover:shadow-lg transition-shadow group">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-11 h-11 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wider text-white/70 mb-0.5"><?= $t('home.continue_practice') ?></p>
                    <p class="text-base font-semibold truncate"><?= htmlspecialchars($ongoing['paper_title']) ?></p>
                </div>
            </div>
            <span class="text-sm font-medium opacity-90 group-hover:translate-x-1 transition-transform flex-shrink-0 ml-4"><?= $t('home.resume') ?></span>
        </a>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="max-w-3xl mx-auto mb-8 grid sm:grid-cols-3 gap-3">
        <a href="/practice" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-primary-700"><?= $t('home.specialized_practice') ?></p>
                <p class="text-xs text-slate-500"><?= $t('home.specialized_desc') ?></p>
            </div>
        </a>
        <a href="/mistakes" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-primary-700"><?= $t('home.mistakes') ?></p>
                <p class="text-xs text-slate-500"><?= $t('home.mistakes_desc') ?></p>
            </div>
        </a>
        <a href="/favorites" class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-primary-700"><?= $t('home.favorites') ?></p>
                <p class="text-xs text-slate-500"><?= $t('home.favorites_desc') ?></p>
            </div>
        </a>
    </div>

    <!-- Subject Pills -->
    <div class="flex flex-wrap items-center justify-center gap-2 mb-8">
        <a href="/"
           class="px-4 py-2 rounded-full text-sm font-medium transition-all <?= $selectedSubject === '' ? 'bg-primary-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-300 hover:text-primary-700' ?>">
            <?= $t('common.all') ?>
        </a>
        <?php foreach ($subjects as $s): ?>
        <a href="/?subject=<?= $s['id'] ?>"
           class="px-4 py-2 rounded-full text-sm font-medium transition-all <?= $selectedSubject == $s['id'] ? 'bg-primary-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-primary-300 hover:text-primary-700' ?>">
            <?= htmlspecialchars($s['alias'] ?: $s['name']) ?>
            <span class="ml-1 text-xs opacity-70"><?= $s['paper_count'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Search -->
    <div class="max-w-md mx-auto mb-8">
        <form method="GET" class="relative">
            <?php if ($selectedSubject): ?><input type="hidden" name="subject" value="<?= htmlspecialchars($selectedSubject) ?>"><?php endif; ?>
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= $t('home.search_papers') ?>"
                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </form>
    </div>

    <!-- Paper Grid -->
    <?php if (empty($papers)): ?>
    <div class="text-center py-16">
        <svg class="w-20 h-20 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <h3 class="text-lg font-semibold text-slate-500 mb-2"><?= $t('home.no_papers_title') ?></h3>
        <p class="text-sm text-slate-400"><?= $t('home.no_papers_desc') ?></p>
    </div>
    <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($papers as $paper): ?>
        <a href="/quiz/<?= $paper['id'] ?>/start" class="bg-white rounded-xl border border-slate-200 hover:border-primary-200 hover:shadow-md transition-all group overflow-hidden block">
            <!-- Color Bar -->
            <div class="h-1.5" style="background:<?= htmlspecialchars($paper['subject_color'] ?? '#3B82F6') ?>"></div>

            <div class="p-5">
                <!-- Subject Tag -->
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full" style="background:<?= htmlspecialchars($paper['subject_color'] ?? '#3B82F6') ?>15; color:<?= htmlspecialchars($paper['subject_color'] ?? '#3B82F6') ?>">
                        <?= htmlspecialchars($paper['subject_name'] ?? '') ?>
                    </span>
                    <?php if ($paper['year']): ?>
                    <span class="text-xs text-slate-400"><?= htmlspecialchars($paper['year']) ?><?= $paper['month'] ? '.' . $paper['month'] : '' ?></span>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h3 class="text-base font-semibold text-slate-900 mb-1 group-hover:text-primary-700 transition-colors line-clamp-2"><?= htmlspecialchars($paper['title']) ?></h3>
                <?php if ($paper['subtitle']): ?>
                <p class="text-sm text-slate-400 mb-3 line-clamp-1"><?= htmlspecialchars($paper['subtitle']) ?></p>
                <?php endif; ?>

                <!-- Stats -->
                <div class="flex items-center gap-4 mt-4 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= $paper['question_count'] ?? 0 ?> <?= $t('home.questions_unit') ?>
                    </div>
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?= $paper['duration'] ?><?= $t('home.minutes_unit') ?>
                    </div>
                    <div class="flex items-center gap-0.5 ml-auto">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-3 h-3 <?= $i <= ($paper['difficulty'] ?? 3) ? 'text-amber-400' : 'text-slate-200' ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<style>
.line-clamp-1 { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 1; }
.line-clamp-2 { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
</style>
