<?php $site = Settings::all(); ?>
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админка — <?= htmlspecialchars($site['site_title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>theme/style.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h2>Управление</h2>
        <nav class="admin-menu">
            <a href="<?= BASE_URL ?>?route=admin" class="<?= ($action === 'dashboard') ? 'active' : '' ?>">Дашборд</a>
            <a href="<?= BASE_URL ?>?route=admin/posts" class="<?= str_starts_with($action ?? '', 'post') ? 'active' : '' ?>">Статьи</a>
            <a href="<?= BASE_URL ?>?route=admin/categories" class="<?= str_starts_with($action ?? '', 'categor') ? 'active' : '' ?>">Категории</a>
            <a href="<?= BASE_URL ?>?route=admin/pages" class="<?= str_starts_with($action ?? '', 'page') ? 'active' : '' ?>">Страницы</a>
            <a href="<?= BASE_URL ?>?route=admin/profile" class="<?= ($action === 'profile') ? 'active' : '' ?>">Профиль</a>
            <a href="<?= BASE_URL ?>?route=admin/settings" class="<?= ($action === 'settings') ? 'active' : '' ?>">Настройки</a>
            <div style="height:1px; background:var(--border); margin:1rem 0;"></div>
            <a href="<?= BASE_URL ?>" target="_blank">↗ На сайт</a>
            <a href="<?= BASE_URL ?>?route=admin/logout">Выйти</a>
        </nav>
    </aside>
    <main class="admin-main">
        <?= $body ?? '' ?>
    </main>
</div>
</body>
</html>
