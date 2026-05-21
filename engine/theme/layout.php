<?php
$site = Settings::all();
$user = Auth::user();
$categories = Categories::all();
$staticPages = Pages::all();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle . ' — ' . $site['site_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($site['site_description']) ?>">
    <link rel="alternate" type="application/rss+xml" title="RSS" href="<?= BASE_URL ?>?route=rss">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>theme/style.css">
    <script>
    (function(){
        const saved = localStorage.getItem('theme');
        const sys = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.dataset.theme = saved || sys;
    })();
    </script>
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
                    <button class="nav-link dropdown-toggle" onclick="toggleDropdown(this)">More <?= Icon::svg('chevron-down', 14, 'chevron') ?></button>
                    <div class="dropdown-menu">
                        <a href="<?= BASE_URL ?>?route=tags" class="dropdown-item"><?= Icon::svg('tag', 14) ?> Все теги</a>
                        <a href="<?= BASE_URL ?>?route=author" class="dropdown-item"><?= Icon::svg('user', 14) ?> Об авторе</a>
                        <a href="<?= BASE_URL ?>?route=rss" class="dropdown-item"><?= Icon::svg('rss', 14) ?> RSS</a>
                        <?php foreach ($staticPages as $sp): ?>
                            <a href="<?= BASE_URL ?>?route=page/<?= urlencode($sp['slug']) ?>" class="dropdown-item"><?= Icon::svg('file', 14) ?> <?= htmlspecialchars($sp['title']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </nav>

            <div class="navbar-actions">
                <form class="search-wrap" method="get" action="<?= BASE_URL ?>">
                    <input type="hidden" name="route" value="search">
                    <span class="search-icon"><?= Icon::svg('search', 16) ?></span>
                    <input type="search" name="q" class="search-input" placeholder="Поиск..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </form>
                <button class="icon-btn theme-toggle" onclick="toggleTheme()" title="Сменить тему" aria-label="Сменить тему">
                    <span class="theme-icon-light"><?= Icon::svg('sun', 18) ?></span>
                    <span class="theme-icon-dark"><?= Icon::svg('moon', 18) ?></span>
                </button>
                <?php if (Auth::isLogged()): ?>
                    <a href="<?= BASE_URL ?>?route=admin" class="nav-link" title="Админка"><?= Icon::svg('settings', 16) ?></a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?route=admin/login" class="nav-link" title="Войти"><?= Icon::svg('log-in', 16) ?></a>
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
                <a href="<?= BASE_URL ?>"><?= Icon::svg('home', 14) ?> Главная</a>
                <a href="<?= BASE_URL ?>?route=tags"><?= Icon::svg('tag', 14) ?> Теги</a>
                <a href="<?= BASE_URL ?>?route=author"><?= Icon::svg('user', 14) ?> Автор</a>
                <?php foreach ($staticPages as $sp): ?>
                    <a href="<?= BASE_URL ?>?route=page/<?= urlencode($sp['slug']) ?>"><?= Icon::svg('file', 14) ?> <?= htmlspecialchars($sp['title']) ?></a>
                <?php endforeach; ?>
                <a href="<?= BASE_URL ?>?route=rss"><?= Icon::svg('rss', 14) ?> RSS</a>
            </div>
            <p class="footer-copy">© <?= date('Y') ?> <?= htmlspecialchars($site['site_title']) ?></p>
        </div>
    </div>
</footer>

<div class="reading-progress" id="reading-progress"></div>

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

function toggleTheme() {
    const cur = document.documentElement.dataset.theme || 'light';
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.dataset.theme = next;
    localStorage.setItem('theme', next);
}

// Индикатор прогресса чтения
const progress = document.getElementById('reading-progress');
const article = document.querySelector('.single-content');
if (progress && article) {
    window.addEventListener('scroll', () => {
        const rect = article.getBoundingClientRect();
        const total = article.offsetHeight - window.innerHeight;
        const scrolled = Math.min(Math.max(-rect.top, 0), total);
        const pct = total > 0 ? (scrolled / total) * 100 : 0;
        progress.style.width = pct + '%';
    });
}
</script>
</body>
</html>