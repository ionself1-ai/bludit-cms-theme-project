<?php
ob_start();
$posts = Posts::all(false);
$cats = Categories::all();
$pages = Pages::all();
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

<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Статей</div><div style="font-size:2rem;font-weight:700;"><?= count($posts) ?></div></div>
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Категорий</div><div style="font-size:2rem;font-weight:700;"><?= count($cats) ?></div></div>
    <div class="admin-card"><div style="font-size:12px;color:var(--muted);text-transform:uppercase;">Страниц</div><div style="font-size:2rem;font-weight:700;"><?= count($pages) ?></div></div>
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