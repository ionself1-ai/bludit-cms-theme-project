<?php
ob_start();
$posts = Posts::all(false);
$cats = Categories::all();
$pages = Pages::all();

// Краткая статистика для дашборда
$statsToday = Stats::today();
$uniqToday = count($statsToday['visitors'] ?? []);
$viewsToday = (int)($statsToday['views'] ?? 0);
$stats7 = Stats::summary(7);
?>
<div class="admin-header">
    <h1>Дашборд</h1>
    <?php if (count($posts) <= 1): ?>
        <button class="btn btn-primary" id="install-demo-btn"><?= Icon::svg('upload', 14) ?> Установить демо-контент</button>
    <?php endif; ?>
</div>
<div id="demo-result"></div>
<script>
document.getElementById('install-demo-btn')?.addEventListener('click', async (e) => {
    e.target.disabled = true;
    e.target.textContent = 'Загружаю...';
    try {
        const r = await fetch('<?= BASE_URL ?>?route=admin/install-demo');
        const d = await r.json();
        document.getElementById('demo-result').innerHTML =
            '<div class="alert alert-success">Добавлено статей: ' + d.posts_added + ', категорий: ' + d.categories_added + '. Обновляю...</div>';
        setTimeout(() => location.reload(), 1200);
    } catch (err) {
        document.getElementById('demo-result').innerHTML = '<div class="alert alert-error">Ошибка установки</div>';
        e.target.disabled = false;
    }
});
</script>

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Статей</div><div style="font-size:2rem;font-weight:700;"><?= count($posts) ?></div></div>
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Категорий</div><div style="font-size:2rem;font-weight:700;"><?= count($cats) ?></div></div>
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Страниц</div><div style="font-size:2rem;font-weight:700;"><?= count($pages) ?></div></div>
    <a href="<?= BASE_URL ?>?route=admin/stats" class="admin-card" style="text-decoration:none; color:inherit; display:block;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Уникальных сегодня</div>
        <div style="font-size:2rem;font-weight:700; color:#10b981;"><?= $uniqToday ?></div>
        <div style="font-size:11.5px; color:var(--muted); margin-top:2px;"><?= $viewsToday ?> просмотров</div>
    </a>
    <a href="<?= BASE_URL ?>?route=admin/stats&period=7" class="admin-card" style="text-decoration:none; color:inherit; display:block;">
        <div style="font-size:12px;color:var(--muted);text-transform:uppercase;">За 7 дней</div>
        <div style="font-size:2rem;font-weight:700; color:var(--accent);"><?= number_format($stats7['total_unique'], 0, '.', ' ') ?></div>
        <div style="font-size:11.5px; color:var(--muted); margin-top:2px;"><?= number_format($stats7['total_views'], 0, '.', ' ') ?> просмотров</div>
    </a>
</div>

<!-- Мини-график за 7 дней -->
<div class="admin-card" style="margin-bottom: 2rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
        <h2 style="font-size:1rem;">Посещения за неделю</h2>
        <a href="<?= BASE_URL ?>?route=admin/stats" style="font-size:13px;">Подробнее →</a>
    </div>
    <?php $maxD = max(array_column($stats7['by_day'], 'views') ?: [1]) ?: 1; ?>
    <div style="display:flex; align-items:flex-end; gap:8px; height:100px;">
        <?php foreach ($stats7['by_day'] as $d):
            $h = max(2, round($d['views'] * 100 / $maxD));
        ?>
            <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%;" title="<?= date('d.m.Y', strtotime($d['date'])) ?>: <?= $d['views'] ?> просмотров, <?= $d['unique'] ?> уник.">
                <div style="flex:1; width:100%; display:flex; align-items:flex-end;">
                    <div style="width:100%; height:<?= $h ?>%; background:linear-gradient(to top, var(--accent), #60a5fa); border-radius:3px 3px 0 0; min-height:2px;"></div>
                </div>
                <div style="font-size:10.5px; color:var(--muted); margin-top:4px;"><?= date('d.m', strtotime($d['date'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="admin-card">
    <h2 style="margin-bottom:1rem;font-size:1.1rem;">Последние статьи</h2>
    <table class="admin-table">
        <thead><tr><th>Название</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
        <tbody>
        <?php foreach (array_slice($posts, 0, 5) as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= !empty($p['published']) ? '✓ Опубликовано' : '— Черновик' ?></td>
                <td><?= date('d.m.Y', strtotime($p['date'])) ?></td>
                <td><a href="<?= BASE_URL ?>?route=admin/post-edit&id=<?= urlencode($p['id']) ?>">Изменить</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?><tr><td colspan="4" style="color:var(--muted)">Статей пока нет</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';