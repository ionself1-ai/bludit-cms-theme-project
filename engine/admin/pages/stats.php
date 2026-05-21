<?php
ob_start();

$period = (int)($_GET['period'] ?? 30);
if (!in_array($period, [7, 14, 30, 60, 90], true)) $period = 30;

$s = Stats::summary($period);
$today = Stats::today();
$uniqToday = count($today['visitors'] ?? []);
$viewsToday = (int)($today['views'] ?? 0);

// Сравнение со вчерашним днём
$yest = Stats::readDay(date('Y-m-d', strtotime('-1 day')));
$uniqYest = count($yest['visitors'] ?? []);
$viewsYest = (int)($yest['views'] ?? 0);
$diffUniq = $uniqToday - $uniqYest;
$diffViews = $viewsToday - $viewsYest;

// Подготовка данных для графика (JSON)
$chartLabels = array_map(fn($d) => date('d.m', strtotime($d['date'])), $s['by_day']);
$chartViews  = array_map(fn($d) => (int)$d['views'], $s['by_day']);
$chartUnique = array_map(fn($d) => (int)$d['unique'], $s['by_day']);

$maxVal = max(max($chartViews ?: [1]), max($chartUnique ?: [1]), 1);

// Получим заголовки постов для топа
$postsIndex = [];
foreach (Posts::all(false) as $p) $postsIndex[$p['id']] = $p;

// Среднее время онлайн (грубо: уникальные за период / период)
$avgDailyUnique = $period > 0 ? round($s['total_unique'] / $period) : 0;
$avgDailyViews  = $period > 0 ? round($s['total_views']  / $period) : 0;

// Устройства — процент
$devTotal = array_sum($s['devices']);
function devPct($v, $t) { return $t > 0 ? round($v * 100 / $t) : 0; }

?>
<div class="admin-header">
    <h1>Статистика</h1>
    <div style="display:flex; gap:6px;">
        <?php foreach ([7, 30, 90] as $p): ?>
            <a href="?route=admin/stats&period=<?= $p ?>" class="filter-btn <?= $period===$p?'filter-btn-active':'' ?>">
                <?= $p ?> дней
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- KPI карточки -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="admin-card stat-kpi">
        <div class="stat-kpi-label">Уникальных сегодня</div>
        <div class="stat-kpi-value"><?= $uniqToday ?></div>
        <div class="stat-kpi-diff <?= $diffUniq>=0?'up':'down' ?>">
            <?= $diffUniq>=0?'▲':'▼' ?> <?= abs($diffUniq) ?> к вчера
        </div>
    </div>
    <div class="admin-card stat-kpi">
        <div class="stat-kpi-label">Просмотров сегодня</div>
        <div class="stat-kpi-value"><?= $viewsToday ?></div>
        <div class="stat-kpi-diff <?= $diffViews>=0?'up':'down' ?>">
            <?= $diffViews>=0?'▲':'▼' ?> <?= abs($diffViews) ?> к вчера
        </div>
    </div>
    <div class="admin-card stat-kpi">
        <div class="stat-kpi-label">Уникальных за <?= $period ?> дней</div>
        <div class="stat-kpi-value"><?= number_format($s['total_unique'], 0, '.', ' ') ?></div>
        <div class="stat-kpi-diff"><?= $avgDailyUnique ?> в день в среднем</div>
    </div>
    <div class="admin-card stat-kpi">
        <div class="stat-kpi-label">Просмотров за <?= $period ?> дней</div>
        <div class="stat-kpi-value"><?= number_format($s['total_views'], 0, '.', ' ') ?></div>
        <div class="stat-kpi-diff"><?= $avgDailyViews ?> в день в среднем</div>
    </div>
</div>

<!-- График по дням -->
<div class="admin-card" style="margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h2 style="font-size:1.05rem;">Активность по дням</h2>
        <div style="display:flex; gap:1rem; font-size:12px;">
            <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; background:var(--accent); border-radius:2px;"></span> Просмотры</span>
            <span style="display:inline-flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; background:#10b981; border-radius:2px;"></span> Уникальные</span>
        </div>
    </div>
    <div class="stat-chart">
        <?php foreach ($s['by_day'] as $i => $d): 
            $vH = $maxVal > 0 ? max(2, round($d['views'] * 100 / $maxVal)) : 2;
            $uH = $maxVal > 0 ? max(2, round($d['unique'] * 100 / $maxVal)) : 2;
        ?>
            <div class="stat-chart-col" title="<?= date('d.m.Y', strtotime($d['date'])) ?>: <?= $d['views'] ?> просмотров, <?= $d['unique'] ?> уникальных">
                <div class="stat-chart-bars">
                    <div class="stat-bar stat-bar-views" style="height:<?= $vH ?>%"></div>
                    <div class="stat-bar stat-bar-unique" style="height:<?= $uH ?>%"></div>
                </div>
                <?php if ($period <= 14 || $i % max(1, (int)floor(count($s['by_day'])/10)) === 0): ?>
                    <div class="stat-chart-label"><?= date('d.m', strtotime($d['date'])) ?></div>
                <?php else: ?>
                    <div class="stat-chart-label">&nbsp;</div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Два колонки: топ статей + топ источников -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="admin-card">
        <h2 style="font-size:1.05rem; margin-bottom:0.75rem;">Топ статей</h2>
        <?php if (empty($s['top_posts'])): ?>
            <p style="color:var(--muted); font-size:14px;">Нет данных за этот период</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Статья</th><th style="text-align:right;">Просмотры</th></tr></thead>
                <tbody>
                <?php $maxPost = max($s['top_posts']); foreach ($s['top_posts'] as $pid => $cnt):
                    $title = $postsIndex[$pid]['title'] ?? 'Удалённая статья';
                    $slug = $postsIndex[$pid]['slug'] ?? '';
                    $pct = $maxPost > 0 ? round($cnt * 100 / $maxPost) : 0;
                ?>
                    <tr>
                        <td>
                            <?php if ($slug): ?>
                                <a href="<?= BASE_URL ?>?route=post/<?= urlencode($slug) ?>" target="_blank" style="font-weight:500;"><?= htmlspecialchars($title) ?></a>
                            <?php else: ?>
                                <span style="color:var(--muted);"><?= htmlspecialchars($title) ?></span>
                            <?php endif; ?>
                            <div class="stat-progress"><div class="stat-progress-bar" style="width:<?= $pct ?>%"></div></div>
                        </td>
                        <td style="text-align:right; font-weight:600;"><?= $cnt ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="admin-card">
        <h2 style="font-size:1.05rem; margin-bottom:0.75rem;">Источники трафика</h2>
        <?php if (empty($s['top_referers'])): ?>
            <p style="color:var(--muted); font-size:14px;">Нет данных</p>
        <?php else: ?>
            <table class="admin-table">
                <thead><tr><th>Источник</th><th style="text-align:right;">Переходов</th></tr></thead>
                <tbody>
                <?php $maxRef = max($s['top_referers']); foreach ($s['top_referers'] as $host => $cnt):
                    $pct = $maxRef > 0 ? round($cnt * 100 / $maxRef) : 0;
                ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($host) ?>
                            <div class="stat-progress"><div class="stat-progress-bar" style="width:<?= $pct ?>%; background:#f59e0b;"></div></div>
                        </td>
                        <td style="text-align:right; font-weight:600;"><?= $cnt ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Устройства + часы активности -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="admin-card">
        <h2 style="font-size:1.05rem; margin-bottom:0.75rem;">Устройства</h2>
        <?php
        $devLabels = ['desktop'=>'🖥️ Компьютер', 'mobile'=>'📱 Мобильные', 'tablet'=>'📋 Планшеты', 'bot'=>'🤖 Боты'];
        foreach ($devLabels as $key => $label):
            $v = (int)($s['devices'][$key] ?? 0);
            $pct = devPct($v, $devTotal);
        ?>
            <div style="margin-bottom:0.75rem;">
                <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:4px;">
                    <span><?= $label ?></span>
                    <span style="color:var(--muted);"><?= $v ?> (<?= $pct ?>%)</span>
                </div>
                <div class="stat-progress"><div class="stat-progress-bar" style="width:<?= $pct ?>%; background:<?= ['desktop'=>'#3b82f6','mobile'=>'#10b981','tablet'=>'#f59e0b','bot'=>'#94a3b8'][$key] ?>;"></div></div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="admin-card">
        <h2 style="font-size:1.05rem; margin-bottom:0.75rem;">Часы активности</h2>
        <?php $maxHour = max($s['hours'] ?: [1]) ?: 1; ?>
        <div class="stat-hours">
            <?php foreach ($s['hours'] as $h => $cnt):
                $hp = round($cnt * 100 / $maxHour);
            ?>
                <div class="stat-hour-col" title="<?= $h ?>:00 — <?= $cnt ?> просмотров">
                    <div class="stat-hour-bar" style="height:<?= max(2, $hp) ?>%"></div>
                    <div class="stat-hour-label"><?= $h ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div style="font-size:11.5px; color:var(--muted); margin-top:6px; text-align:center;">Часы (0-23) по времени сервера</div>
    </div>
</div>

<!-- Топ страниц -->
<div class="admin-card">
    <h2 style="font-size:1.05rem; margin-bottom:0.75rem;">Топ страниц</h2>
    <?php if (empty($s['top_paths'])): ?>
        <p style="color:var(--muted); font-size:14px;">Нет данных</p>
    <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>Адрес</th><th style="text-align:right;">Просмотры</th></tr></thead>
            <tbody>
            <?php foreach ($s['top_paths'] as $path => $cnt): ?>
                <tr>
                    <td style="font-family:ui-monospace, monospace; font-size:13px;"><?= htmlspecialchars($path) ?></td>
                    <td style="text-align:right; font-weight:600;"><?= $cnt ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.stat-kpi { padding: 1.25rem; }
.stat-kpi-label { font-size:11px; color:var(--muted); text-transform:uppercase; letter-spacing:0.04em; }
.stat-kpi-value { font-size: 2.1rem; font-weight:700; line-height:1.1; margin: 6px 0 4px; }
.stat-kpi-diff { font-size:12px; color:var(--muted); }
.stat-kpi-diff.up { color:#10b981; }
.stat-kpi-diff.down { color:#ef4444; }

.stat-chart {
    display: flex;
    align-items: flex-end;
    gap: 4px;
    height: 220px;
    padding-top: 1rem;
    overflow-x: auto;
}
.stat-chart-col {
    flex: 1;
    min-width: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.stat-chart-bars {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: flex-end;
    gap: 2px;
    justify-content: center;
}
.stat-bar {
    width: 45%;
    border-radius: 3px 3px 0 0;
    min-height: 2px;
    transition: opacity 0.15s;
}
.stat-chart-col:hover .stat-bar { opacity: 0.8; }
.stat-bar-views  { background: var(--accent); }
.stat-bar-unique { background: #10b981; }
.stat-chart-label { font-size:10.5px; color:var(--muted); margin-top:6px; white-space: nowrap; }

.stat-progress {
    height: 5px;
    background: var(--secondary);
    border-radius: 999px;
    margin-top: 4px;
    overflow: hidden;
}
.stat-progress-bar {
    height: 100%;
    background: var(--accent);
    border-radius: 999px;
    transition: width 0.3s;
}

.stat-hours {
    display: flex;
    align-items: flex-end;
    gap: 2px;
    height: 140px;
}
.stat-hour-col {
    flex: 1;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
}
.stat-hour-bar {
    width: 100%;
    background: linear-gradient(to top, var(--accent), #60a5fa);
    border-radius: 2px 2px 0 0;
    min-height: 2px;
}
.stat-hour-label { font-size: 9.5px; color:var(--muted); margin-top: 4px; }
</style>

<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
