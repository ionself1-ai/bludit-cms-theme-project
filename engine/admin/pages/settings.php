<?php
ob_start();
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $data = [
        'site_title' => trim($_POST['site_title'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'posts_per_page' => max(1, (int)($_POST['posts_per_page'] ?? 9)),
        'logo' => trim($_POST['logo'] ?? ''),
    ];
    if (!empty($_FILES['logo_file']['tmp_name'])) {
        $up = Uploader::image($_FILES['logo_file'], 'logo');
        if (!empty($up['success'])) $data['logo'] = $up['url'];
        else $err = $up['error'] ?? 'Ошибка загрузки';
    }
    if (!empty($_POST['remove_logo'])) $data['logo'] = '';

    if (!$err) {
        Settings::save($data);
        $msg = 'Сохранено';
    }
}
$s = Settings::all();
?>
<div class="admin-header"><h1>Настройки сайта</h1></div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">

    <div class="form-row">
        <label>Логотип</label>
        <?php if (!empty($s['logo'])): ?>
            <img src="<?= htmlspecialchars($s['logo']) ?>" style="width:80px;height:80px;border-radius:12px;object-fit:cover;margin-bottom:8px;display:block;">
        <?php endif; ?>
        <input type="file" name="logo_file" accept="image/*">
        <div class="hint">Или вставь URL картинки:</div>
        <input type="text" name="logo" value="<?= htmlspecialchars($s['logo'] ?? '') ?>" placeholder="https://...">
        <?php if (!empty($s['logo'])): ?>
            <label style="margin-top:6px;display:block;"><input type="checkbox" name="remove_logo" value="1"> Удалить логотип</label>
        <?php endif; ?>
    </div>

    <div class="form-row"><label>Название сайта</label><input type="text" name="site_title" value="<?= htmlspecialchars($s['site_title']) ?>"></div>
    <div class="form-row"><label>Описание</label><textarea name="site_description"><?= htmlspecialchars($s['site_description']) ?></textarea></div>
    <div class="form-row"><label>Статей на странице</label><input type="text" name="posts_per_page" value="<?= (int)$s['posts_per_page'] ?>"></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
