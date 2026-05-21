<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Auth::login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: ' . BASE_URL . '?route=admin');
        exit;
    }
    $error = 'Неверный логин или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>theme/style.css">
</head>
<body>
<div class="login-page">
    <form method="post" class="login-box">
        <h1>Вход в админку</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <div class="form-row">
            <label>Логин</label>
            <input type="text" name="username" required autofocus>
        </div>
        <div class="form-row">
            <label>Пароль</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Войти</button>
        <p class="hint" style="text-align:center; margin-top:1rem;">По умолчанию: admin / admin</p>
    </form>
</div>
</body>
</html>
