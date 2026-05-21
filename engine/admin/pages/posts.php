<?php
ob_start();

if (($_GET['delete'] ?? '') !== '' && Auth::checkCsrf($_GET['csrf'] ?? '')) {
    Posts::delete($_GET['delete']);
    header('Location: ' . BASE_URL . '?route=admin/posts'); exit;
}

$posts = Posts::all(false);
?>
<div class="admin-header">
    <h1>Статьи</h1>
    <a href="<?= BASE_URL ?>?route=admin/post-edit" class="btn btn-primary">+ Новая статья</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Название</th><th>Категория</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): $c = Categories::get($p['category'] ?? ''); ?>
            <tr>
                <td><a href="<?= BASE_URL ?>?route=admin/post-edit&id=<?= urlencode($p['id']) ?>"><?= htmlspecialchars($p['title']) ?></a></td>
                <td><?= $c ? htmlspecialchars($c['name']) : '—' ?></td>
                <td><?= !empty($p['published']) ? '<span style="color:#10b981">✓ Опубликовано</span>' : '<span style="color:var(--muted)">Черновик</span>' ?></td>
                <td><?= date('d.m.Y', strtotime($p['date'])) ?></td>
                <td>
                    <a href="<?= BASE_URL ?>?route=post/<?= urlencode($p['slug']) ?>" target="_blank">Открыть</a> ·
                    <a href="?route=admin/posts&delete=<?= urlencode($p['id']) ?>&csrf=<?= Auth::csrf() ?>" onclick="return confirm('Удалить статью?')" style="color:#ef4444">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?><tr><td colspan="5" style="color:var(--muted);text-align:center;padding:2rem">Статей пока нет. <a href="<?= BASE_URL ?>?route=admin/post-edit">Создать первую</a></td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
