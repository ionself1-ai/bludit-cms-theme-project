<?php
ob_start();
$msg = $err = '';

// Тестовая отправка письма
if (($_POST['action'] ?? '') === 'test_mail' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $to = trim($_POST['test_to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $err = 'Введите корректный email для теста';
    } else {
        // Сначала сохраним актуальные SMTP-настройки в Settings
        Settings::save([
            'mail_from' => trim($_POST['mail_from'] ?? ''),
            'mail_from_name' => trim($_POST['mail_from_name'] ?? ''),
            'smtp_host' => trim($_POST['smtp_host'] ?? ''),
            'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
            'smtp_user' => trim($_POST['smtp_user'] ?? ''),
            'smtp_pass' => trim($_POST['smtp_pass'] ?? ''),
            'smtp_secure' => $_POST['smtp_secure'] ?? 'tls',
        ]);
        $ok = Mailer::send($to, 'Тестовое письмо', Mailer::template('Тестовое письмо',
            '<p>Если вы видите это письмо — настройки почты работают корректно. ✓</p>',
            'Отправлено из админ-панели для проверки настроек.'));
        $msg = $ok ? 'Тестовое письмо отправлено на ' . htmlspecialchars($to) : 'Не удалось отправить — проверьте настройки SMTP';
        if (!$ok) $err = $msg; else $msg = $msg;
        if ($ok) $err = '';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $data = [
        'site_title' => trim($_POST['site_title'] ?? ''),
        'site_description' => trim($_POST['site_description'] ?? ''),
        'posts_per_page' => max(1, (int)($_POST['posts_per_page'] ?? 9)),
        'logo' => trim($_POST['logo'] ?? ''),
        'mail_from' => trim($_POST['mail_from'] ?? ''),
        'mail_from_name' => trim($_POST['mail_from_name'] ?? ''),
        'smtp_host' => trim($_POST['smtp_host'] ?? ''),
        'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
        'smtp_user' => trim($_POST['smtp_user'] ?? ''),
        'smtp_pass' => trim($_POST['smtp_pass'] ?? ''),
        'smtp_secure' => $_POST['smtp_secure'] ?? 'tls',
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

    <h2 style="margin-top:2rem; padding-top:1.5rem; border-top:1px solid var(--border); font-size:1.15rem;">Почтовая рассылка</h2>
    <p style="color:var(--muted); font-size:13px; margin-bottom:1rem;">Настройки для отправки писем подписчикам и подтверждения подписки. Если SMTP не указан — используется системная функция mail().</p>

    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
            <label>Email отправителя</label>
            <input type="email" name="mail_from" value="<?= htmlspecialchars($s['mail_from'] ?? '') ?>" placeholder="noreply@example.com">
        </div>
        <div>
            <label>Имя отправителя</label>
            <input type="text" name="mail_from_name" value="<?= htmlspecialchars($s['mail_from_name'] ?? '') ?>" placeholder="<?= htmlspecialchars($s['site_title']) ?>">
        </div>
    </div>

    <div class="form-row" style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:12px;">
        <div>
            <label>SMTP-хост</label>
            <input type="text" name="smtp_host" value="<?= htmlspecialchars($s['smtp_host'] ?? '') ?>" placeholder="smtp.yandex.ru">
        </div>
        <div>
            <label>Порт</label>
            <input type="text" name="smtp_port" value="<?= htmlspecialchars($s['smtp_port'] ?? '587') ?>">
        </div>
        <div>
            <label>Шифрование</label>
            <select name="smtp_secure">
                <option value="tls" <?= ($s['smtp_secure'] ?? 'tls')==='tls'?'selected':'' ?>>TLS</option>
                <option value="ssl" <?= ($s['smtp_secure'] ?? '')==='ssl'?'selected':'' ?>>SSL</option>
                <option value="none" <?= ($s['smtp_secure'] ?? '')==='none'?'selected':'' ?>>Без шифрования</option>
            </select>
        </div>
    </div>

    <div class="form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
            <label>SMTP-логин</label>
            <input type="text" name="smtp_user" value="<?= htmlspecialchars($s['smtp_user'] ?? '') ?>" autocomplete="off">
        </div>
        <div>
            <label>SMTP-пароль</label>
            <input type="password" name="smtp_pass" value="<?= htmlspecialchars($s['smtp_pass'] ?? '') ?>" autocomplete="new-password">
        </div>
    </div>

    <div class="form-actions"><button type="submit" class="btn btn-primary">Сохранить</button></div>
</form>

<div class="admin-card" style="margin-top:1rem;">
    <h3 style="font-size:1rem; margin-bottom:0.75rem;">Проверить отправку</h3>
    <form method="post" style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
        <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">
        <input type="hidden" name="action" value="test_mail">
        <!-- Скрытые поля чтобы передать SMTP-параметры на момент теста -->
        <input type="hidden" name="mail_from" value="<?= htmlspecialchars($s['mail_from'] ?? '') ?>">
        <input type="hidden" name="mail_from_name" value="<?= htmlspecialchars($s['mail_from_name'] ?? '') ?>">
        <input type="hidden" name="smtp_host" value="<?= htmlspecialchars($s['smtp_host'] ?? '') ?>">
        <input type="hidden" name="smtp_port" value="<?= htmlspecialchars($s['smtp_port'] ?? '587') ?>">
        <input type="hidden" name="smtp_user" value="<?= htmlspecialchars($s['smtp_user'] ?? '') ?>">
        <input type="hidden" name="smtp_pass" value="<?= htmlspecialchars($s['smtp_pass'] ?? '') ?>">
        <input type="hidden" name="smtp_secure" value="<?= htmlspecialchars($s['smtp_secure'] ?? 'tls') ?>">
        <div style="flex:1; min-width:220px;">
            <label style="font-size:13px; color:var(--muted);">Email для теста</label>
            <input type="email" name="test_to" required placeholder="ваш@email.ru">
        </div>
        <button type="submit" class="btn">Отправить тест</button>
    </form>
    <p style="color:var(--muted); font-size:12px; margin-top:8px;">Сохраните настройки выше перед отправкой теста.</p>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';