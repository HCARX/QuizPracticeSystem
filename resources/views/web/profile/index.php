<!-- User Profile / Dashboard -->
<?php
    $timeH = floor($stats['total_time'] / 3600);
    $timeM = floor(($stats['total_time'] % 3600) / 60);
?>
<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900"><?= $t('profile.title') ?></h1>
        <p class="text-sm text-slate-500 mt-1"><?= $t('profile.track_journey') ?></p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $statItems = [
            ['label' => $t('profile.total_sessions'), 'value' => $stats['total_sessions'], 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'bg' => 'bg-primary-50', 'fg' => 'text-primary-600'],
            ['label' => $t('profile.avg_accuracy'), 'value' => $stats['avg_accuracy'] . '%', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-600'],
            ['label' => $t('profile.questions_done'), 'value' => $stats['total_questions'], 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-blue-50', 'fg' => 'text-blue-600'],
            ['label' => $t('profile.study_time'), 'value' => ($timeH > 0 ? "{$timeH}h " : '') . "{$timeM}m", 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-amber-50', 'fg' => 'text-amber-600'],
        ];
        foreach ($statItems as $si): ?>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 <?= $si['bg'] ?> rounded-lg flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 <?= $si['fg'] ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $si['icon'] ?>"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-900"><?= $si['value'] ?></p>
            <p class="text-xs text-slate-500 mt-0.5"><?= $si['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Nav -->
    <div class="grid grid-cols-3 gap-4">
        <a href="/mistakes" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-red-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-slate-900"><?= $stats['mistakes'] ?></p>
                    <p class="text-sm text-slate-500"><?= $t('profile.mistakes') ?></p>
                </div>
                <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </a>
        <a href="/vocabulary" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-violet-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-slate-900"><?= $stats['vocab'] ?></p>
                    <p class="text-sm text-slate-500"><?= $t('profile.vocabulary') ?></p>
                </div>
                <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center group-hover:bg-violet-100">
                    <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
        </a>
        <a href="/favorites" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-amber-300 hover:shadow-sm transition-all group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold text-slate-900"><?= $stats['favorites'] ?></p>
                    <p class="text-sm text-slate-500"><?= $t('profile.favorites') ?></p>
                </div>
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center group-hover:bg-amber-100">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Performance by Type -->
    <?php if ($typeStats): ?>
    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Accuracy Trend -->
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-slate-900"><?= $t('profile.accuracy_trend') ?></h3>
                <span class="text-xs text-slate-400"><?= $t('profile.last_n_sessions', ['n' => count($trend)]) ?></span>
            </div>
            <?php if (count($trend) >= 2):
                $w = 560; $h = 120; $pad = 8;
                $max = 100; $min = 0;
                $n = count($trend);
                $step = $n > 1 ? ($w - 2*$pad) / ($n - 1) : 0;
                $points = [];
                foreach ($trend as $i => $v) {
                    $x = $pad + $i * $step;
                    $y = $h - $pad - (($v - $min) / ($max - $min)) * ($h - 2*$pad);
                    $points[] = round($x, 1) . ',' . round($y, 1);
                }
                $pathD = 'M ' . implode(' L ', $points);
                $areaD = $pathD . " L {$points[count($points)-1]},{$h} L {$points[0]},{$h} Z";
                // Replace commas in L points properly
                $areaD = 'M ' . implode(' L ', $points) . ' L ' . round($pad + ($n-1)*$step, 1) . ',' . $h . ' L ' . round($pad, 1) . ',' . $h . ' Z';
            ?>
            <svg viewBox="0 0 <?= $w ?> <?= $h ?>" class="w-full h-28">
                <defs>
                    <linearGradient id="sparkGrad" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#6366f1" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <path d="<?= $areaD ?>" fill="url(#sparkGrad)"/>
                <path d="<?= $pathD ?>" fill="none" stroke="#6366f1" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>
                <?php foreach ($points as $p): [$px,$py] = explode(',', $p); ?>
                <circle cx="<?= $px ?>" cy="<?= $py ?>" r="2.5" fill="#fff" stroke="#6366f1" stroke-width="1.5"/>
                <?php endforeach; ?>
            </svg>
            <div class="flex justify-between text-xs text-slate-400 mt-1"><span>0%</span><span>100%</span></div>
            <?php else: ?>
            <p class="text-sm text-slate-400 py-6 text-center"><?= $t('profile.trend_empty') ?></p>
            <?php endif; ?>
        </div>

        <!-- Performance by Type -->
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <h3 class="text-sm font-semibold text-slate-900 mb-4"><?= $t('profile.perf_by_type') ?></h3>
            <div class="space-y-3">
                <?php foreach ($typeStats as $ts):
                    $acc = $ts['total'] > 0 ? round(($ts['correct'] / $ts['total']) * 100) : 0;
                    $barColor = $acc >= 80 ? 'bg-emerald-500' : ($acc >= 60 ? 'bg-amber-500' : 'bg-red-500');
                ?>
                <div class="flex items-center gap-3">
                    <span class="w-28 text-xs text-slate-600 truncate"><?= ucwords(str_replace('_',' ',$ts['type'])) ?></span>
                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full <?= $barColor ?> rounded-full transition-all" style="width:<?= $acc ?>%"></div></div>
                    <span class="w-10 text-xs text-right font-medium text-slate-700"><?= $acc ?>%</span>
                    <span class="w-12 text-xs text-right text-slate-400"><?= $ts['correct'] ?>/<?= $ts['total'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Sessions -->
    <?php if ($recentSessions): ?>
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="text-sm font-semibold text-slate-900 mb-4"><?= $t('profile.recent_practice') ?></h3>
        <div class="space-y-3">
            <?php foreach ($recentSessions as $rs): ?>
            <div class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate"><?= htmlspecialchars($rs['paper_title'] ?? '') ?></p>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($rs['subject_name'] ?? '') ?> &middot; <?= date('M d, H:i', strtotime($rs['started_at'])) ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <?php if ($rs['status'] === 'completed'): ?>
                    <span class="text-sm font-semibold <?= $rs['accuracy'] >= 80 ? 'text-emerald-600' : ($rs['accuracy'] >= 60 ? 'text-amber-600' : 'text-red-600') ?>"><?= $rs['accuracy'] ?>%</span>
                    <a href="/quiz/<?= $rs['id'] ?>/result" class="text-xs text-primary-600 hover:text-primary-700 font-medium"><?= $t('profile.review') ?></a>
                    <?php else: ?>
                    <span class="badge badge-warning"><?= $t('profile.in_progress') ?></span>
                    <a href="/quiz/<?= $rs['id'] ?>" class="text-xs text-primary-600 hover:text-primary-700 font-medium"><?= $t('profile.continue') ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
