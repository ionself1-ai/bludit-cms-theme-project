<?php
ob_start();
if (($_GET['delete'] ?? '') !== '' && Auth::checkCsrf($_GET['csrf'] ?? '')) {
    Pages::delete($_GET['delete']);
    header('Location: '.BASE_URL.'?route=admin/pages'); exit;
}
$pages = Pages::all();
?>
<div class="admin-header">
    <h1>Статические страницы</h1>
    <a href="<?= BASE_URL ?>?route=admin/page-edit" class="btn btn-primary">+ Новая страница</a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Название</th><th>URL</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($pages as $p): ?>
            <tr>
                <td><a href="<?= BASE_URL ?>?route=admin/page-edit&slug=<?= urlencode($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a></td>
                <td><code><?= htmlspecialchars($p['slug']) ?></code></td>
                <td>
                    <a href="<?= BASE_URL ?>?route=page/<?= urlencode($p['slug']) ?>" target="_blank">Открыть</a> ·
                    <a href="?route=admin/pages&delete=<?= urlencode($p['slug']) ?>&csrf=<?= Auth::csrf() ?>" onclick="return confirm('Удалить?')" style="color:#ef4444">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
