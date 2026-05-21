<?php
$site = Settings::all();
$user = Auth::user();
$categories = Categories::all();
$staticPages = Pages::all();
?>
<!DOCTYPE html>
<html lang="ru" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle . ' — ' . $site['site_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site['site_description']) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>theme/style.css">
</head>
<body>

<header class="navbar">
    <div class="site-container">
        <div class="navbar-inner">
            <a href="<?= BASE_URL ?>" class="navbar-logo">
                <div class="logo-icon"><?= htmlspecialchars(mb_substr($site['site_title'], 0, 1)) ?></div>
                <span class="logo-text"><?= htmlspecialchars($site['site_title']) ?></span>
            </a>

            <nav class="navbar-nav">
                <a href="<?= BASE_URL ?>" class="nav-link <?= $template === 'home' ? 'nav-link-active' : '' ?>">Explore</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="nav-link">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
                <?php foreach ($staticPages as $sp): if ($sp['slug'] === 'about'): ?>
                    <a href="<?= BASE_URL ?>?route=page/about" class="nav-link">Обо мне</a>
                <?php endif; endforeach; ?>

                <div class="dropdown">
                    <button class="nav-link dropdown-toggle" onclick="toggleDropdown(this)">More <span class="chevron">▾</span></button>
                    <div class="dropdown-menu">
                        <?php foreach ($staticPages as $sp): ?>
                            <a href="<?= BASE_URL ?>?route=page/<?= urlencode($sp['slug']) ?>" class="dropdown-item"><?= htmlspecialchars($sp['title']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </nav>

            <div class="navbar-actions">
                <form class="search-wrap" method="get" action="<?= BASE_URL ?>">
                    <input type="hidden" name="route" value="search">
                    <input type="search" name="q" class="search-input" placeholder="Поиск..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
                <?php if (Auth::isLogged()): ?>
                    <a href="<?= BASE_URL ?>?route=admin" class="nav-link">Админка</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?route=admin/login" class="nav-link">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="site-main">
    <div class="site-container">
        <?php require THEME_PATH . '/' . $template . '.php'; ?>
    </div>
</main>

<footer class="site-footer">
    <div class="site-container">
        <div class="footer-inner">
            <div class="footer-logo">
                <div class="logo-icon"><?= htmlspecialchars(mb_substr($site['site_title'], 0, 1)) ?></div>
                <span class="logo-text"><?= htmlspecialchars($site['site_title']) ?></span>
            </div>
            <div class="footer-links">
                <a href="<?= BASE_URL ?>">Explore</a>
                <?php foreach ($staticPages as $sp): ?>
                    <a href="<?= BASE_URL ?>?route=page/<?= urlencode($sp['slug']) ?>"><?= htmlspecialchars($sp['title']) ?></a>
                <?php endforeach; ?>
            </div>
            <p class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($site['site_title']) ?></p>
        </div>
    </div>
</footer>

<script>
function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    menu.classList.toggle('show');
}
document.addEventListener('click', e => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
});
</script>
</body>
</html>
