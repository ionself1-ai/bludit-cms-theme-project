<?php
ob_start();
$user = Auth::user();
$err = $msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $users = Storage::read('users');
    $users[0]['name'] = trim($_POST['name'] ?? '');
    $users[0]['bio'] = trim($_POST['bio'] ?? '');
    $users[0]['username'] = trim($_POST['username'] ?? $users[0]['username']);

    // Аватар
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $up = Uploader::image($_FILES['avatar'], 'avatars');
        if (!empty($up['success'])) $users[0]['avatar'] = $up['url'];
    }

    // Пароль
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 4) $err = 'Пароль слишком короткий';
        else $users[0]['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    }
    if (!$err) { Storage::write('users', $users); $msg = 'Сохранено'; $user = $users[0]; }
}
?>
<div class="admin-header"><h1>Профиль</h1></div>

<?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">

    <div class="form-row">
        <label>Аватар</label>
        <?php if (!empty($user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" class="avatar-preview">
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/*">
    </div>

    <div class="form-row"><label>Имя</label><input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></div>
    <div class="form-row"><label>О себе (bio)</label><textarea name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea></div>
    <div class="form-row"><label>Логин</label><input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"></div>
    <div class="form-row"><label>Новый пароль</label><input type="password" name="new_password" placeholder="оставьте пустым, чтобы не менять"></div>

    <div class="form-actions"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
