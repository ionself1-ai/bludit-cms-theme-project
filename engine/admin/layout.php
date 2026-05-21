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
            <a href="<?= BASE_URL ?>?route=admin" class="<?= ($action === 'dashboard') ? 'active' : '' ?>"><?= Icon::svg('home', 16) ?> <span>Дашборд</span></a>
            <a href="<?= BASE_URL ?>?route=admin/posts" class="<?= str_starts_with($action ?? '', 'post') ? 'active' : '' ?>"><?= Icon::svg('file', 16) ?> <span>Статьи</span></a>
            <a href="<?= BASE_URL ?>?route=admin/categories" class="<?= str_starts_with($action ?? '', 'categor') ? 'active' : '' ?>"><?= Icon::svg('folder', 16) ?> <span>Категории</span></a>
            <a href="<?= BASE_URL ?>?route=admin/pages" class="<?= str_starts_with($action ?? '', 'page') ? 'active' : '' ?>"><?= Icon::svg('file', 16) ?> <span>Страницы</span></a>
            <a href="<?= BASE_URL ?>?route=admin/profile" class="<?= ($action === 'profile') ? 'active' : '' ?>"><?= Icon::svg('user', 16) ?> <span>Профиль</span></a>
            <a href="<?= BASE_URL ?>?route=admin/settings" class="<?= ($action === 'settings') ? 'active' : '' ?>"><?= Icon::svg('settings', 16) ?> <span>Настройки</span></a>
            <div style="height:1px; background:var(--border); margin:1rem 0;"></div>
            <a href="<?= BASE_URL ?>" target="_blank"><?= Icon::svg('eye', 16) ?> <span>На сайт</span></a>
            <a href="<?= BASE_URL ?>?route=admin/logout"><?= Icon::svg('log-out', 16) ?> <span>Выйти</span></a>
        </nav>
    </aside>
    <main class="admin-main">
        <?= $body ?? '' ?>
    </main>
</div>
</body>
</html>