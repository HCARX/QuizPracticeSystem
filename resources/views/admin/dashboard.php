<!-- Dashboard -->
<div class="space-y-6">

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <?php
        $statCards = [
            ['label' => $t('admin.stats.subjects'), 'value' => $stats['subjects'], 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'primary'],
            ['label' => $t('admin.stats.papers'), 'value' => $stats['papers'], 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'violet'],
            ['label' => $t('admin.stats.questions'), 'value' => $stats['questions'], 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
            ['label' => $t('admin.stats.published'), 'value' => $stats['published_papers'], 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'emerald'],
            ['label' => $t('admin.stats.users'), 'value' => $stats['users'], 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197V21', 'color' => 'amber'],
            ['label' => $t('admin.stats.sessions'), 'value' => $stats['practice_sessions'], 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => 'rose'],
        ];
        $colorMap = [
            'primary' => ['bg-primary-50', 'text-primary-600'],
            'violet' => ['bg-violet-50', 'text-violet-600'],
            'blue' => ['bg-blue-50', 'text-blue-600'],
            'emerald' => ['bg-emerald-50', 'text-emerald-600'],
            'amber' => ['bg-amber-50', 'text-amber-600'],
            'rose' => ['bg-rose-50', 'text-rose-600'],
        ];
        foreach ($statCards as $card):
            $cm = $colorMap[$card['color']];
        ?>
        <div class="card p-5">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 <?= $cm[0] ?> rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5.5 h-5.5 <?= $cm[1] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $card['icon'] ?>"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900"><?= number_format($card['value']) ?></p>
                    <p class="text-sm text-slate-500"><?= $card['label'] ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Actions + Recent Papers -->
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Quick Actions -->
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4"><?= $t('admin.quick_actions') ?></h3>
            <div class="space-y-2">
                <a href="/admin/subjects" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 bg-primary-50 rounded-lg flex items-center justify-center group-hover:bg-primary-100">
                        <svg class="w-4.5 h-4.5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700"><?= $t('admin.qa.manage_subjects') ?></p>
                        <p class="text-xs text-slate-400"><?= $t('admin.qa.manage_subjects_desc') ?></p>
                    </div>
                </a>
                <a href="/admin/papers/create" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 bg-violet-50 rounded-lg flex items-center justify-center group-hover:bg-violet-100">
                        <svg class="w-4.5 h-4.5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700"><?= $t('admin.qa.create_paper') ?></p>
                        <p class="text-xs text-slate-400"><?= $t('admin.qa.create_paper_desc') ?></p>
                    </div>
                </a>
                <a href="/admin/ai" class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100">
                        <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-700"><?= $t('admin.qa.ai_settings') ?></p>
                        <p class="text-xs text-slate-400"><?= $t('admin.qa.ai_settings_desc') ?></p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Papers -->
        <div class="lg:col-span-2 card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900"><?= $t('admin.recent_papers') ?></h3>
                <a href="/admin/papers" class="text-sm text-primary-600 hover:text-primary-700 font-medium"><?= $t('admin.view_all') ?></a>
            </div>
            <?php if (empty($recentPapers)): ?>
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-sm text-slate-400"><?= $t('admin.no_papers_desc') ?></p>
            </div>
            <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($recentPapers as $paper): ?>
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-2 h-2 rounded-full flex-shrink-0 <?= $paper['status'] === 'published' ? 'bg-emerald-500' : ($paper['status'] === 'draft' ? 'bg-amber-400' : 'bg-slate-300') ?>"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate"><?= htmlspecialchars($paper['title']) ?></p>
                            <p class="text-xs text-slate-400"><?= htmlspecialchars($paper['subject_name'] ?? '') ?> &middot; <?= $paper['year'] ?? '' ?></p>
                        </div>
                    </div>
                    <span class="badge <?= $paper['status'] === 'published' ? 'badge-success' : ($paper['status'] === 'draft' ? 'badge-warning' : 'badge-gray') ?>"><?= $t('admin.status.' . $paper['status']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
