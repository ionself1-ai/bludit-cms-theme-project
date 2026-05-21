<?php
ob_start();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    Settings::save([
        'site_title' => trim($_POST['site_title'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'posts_per_page' => max(1, (int)($_POST['posts_per_page'] ?? 9)),
    ]);
    $msg = 'Сохранено';
}
$s = Settings::all();
?>
<div class="admin-header"><h1>Настройки сайта</h1></div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

<form method="post" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">
    <div class="form-row"><label>Название сайта</label><input type="text" name="site_title" value="<?= htmlspecialchars($s['site_title']) ?>"></div>
    <div class="form-row"><label>Описание</label><textarea name="site_description"><?= htmlspecialchars($s['site_description']) ?></textarea></div>
    <div class="form-row"><label>Статей на странице</label><input type="text" name="posts_per_page" value="<?= (int)$s['posts_per_page'] ?>"></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
