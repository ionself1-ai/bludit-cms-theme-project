<!DOCTYPE html>
<html lang="<?php echo $site->locale(); ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if ($WHERE_AM_I == 'home'): ?>
            <?php echo $site->title(); ?> — <?php echo $site->slogan(); ?>
        <?php else: ?>
            <?php echo $page->title(); ?> — <?php echo $site->title(); ?>
        <?php endif; ?>
    </title>
    <meta name="description" content="<?php echo ($WHERE_AM_I == 'home') ? $site->description() : $page->description(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php echo Theme::css('css/style.css') ?>
    <?php Theme::plugins('siteHead') ?>
</head>
<body>
<?php Theme::plugins('siteBodyBegin') ?>

<!-- Navbar -->
<header class="navbar">
    <div class="site-container">
        <div class="navbar-inner">

            <!-- Logo -->
            <a href="<?php echo DOMAIN; ?>" class="navbar-logo">
                <div class="logo-icon"><?php echo mb_substr($site->title(), 0, 1); ?></div>
                <span class="logo-text"><?php echo $site->title(); ?></span>
            </a>

            <!-- Nav links -->
            <nav class="navbar-nav">
                <a href="<?php echo DOMAIN; ?>" class="nav-link <?php echo ($WHERE_AM_I == 'home') ? 'nav-link-active' : ''; ?>">
                    Explore
                </a>
                <?php foreach ($categories as $category): ?>
                <a href="<?php echo $category->permalink(); ?>" class="nav-link">
                    <?php echo $category->name(); ?>
                </a>
                <?php endforeach; ?>
                <?php foreach ($staticContent as $staticPage): ?>
                    <?php if ($staticPage->slug() == 'about'): ?>
                    <a href="<?php echo $staticPage->permalink(); ?>" class="nav-link <?php echo ($WHERE_AM_I == 'page' && $page->slug() == 'about') ? 'nav-link-active' : ''; ?>">
                        Обо мне
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="<?php echo DOMAIN; ?>blog" class="nav-link <?php echo ($WHERE_AM_I == 'blog') ? 'nav-link-active' : ''; ?>">
                    Блог
                </a>

                <!-- More dropdown -->
                <div class="dropdown">
                    <button class="nav-link dropdown-toggle" onclick="toggleDropdown(this)">
                        More <span class="chevron">▾</span>
                    </button>
                    <div class="dropdown-menu">
                        <?php foreach ($staticContent as $staticPage): ?>
                            <?php if (in_array($staticPage->slug(), ['privacy-policy', 'terms-of-service', 'privacy', 'terms'])): ?>
                            <a href="<?php echo $staticPage->permalink(); ?>" class="dropdown-item">
                                <?php echo $staticPage->title(); ?>
                            </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="<?php echo DOMAIN; ?>privacy-policy" class="dropdown-item">Privacy Policy</a>
                        <a href="<?php echo DOMAIN; ?>terms-of-service" class="dropdown-item">Terms of Service</a>
                    </div>
                </div>
            </nav>

            <!-- Right actions -->
            <div class="navbar-actions">
                <!-- Search -->
                <div class="search-wrap">
                    <button class="icon-btn" id="searchToggle" onclick="toggleSearch()" title="Поиск">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                    <div class="search-box" id="searchBox">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <form action="<?php echo DOMAIN; ?>search" method="get">
                            <input type="text" name="q" placeholder="Поиск..." id="searchInput" autocomplete="off">
                        </form>
                        <button onclick="toggleSearch()" class="search-close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Theme toggle -->
                <button class="icon-btn" id="themeToggle" onclick="toggleTheme()" title="Переключить тему">
                    <svg id="iconMoon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                    <svg id="iconSun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                </button>

                <!-- Add button (admin only) -->
                <?php if (defined('ADMIN_URI')): ?>
                <a href="<?php echo DOMAIN_ADMIN; ?>new-content" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    <span>Добавить</span>
                </a>
                <?php endif; ?>

                <!-- Profile -->
                <div class="dropdown dropdown-right">
                    <button class="avatar-btn" onclick="toggleDropdown(this)">
                        <?php echo mb_substr($site->title(), 0, 1); ?>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            <p class="dropdown-name"><?php echo $site->title(); ?></p>
                        </div>
                        <?php if (defined('ADMIN_URI')): ?>
                        <a href="<?php echo DOMAIN_ADMIN; ?>" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                            Панель управления
                        </a>
                        <a href="<?php echo DOMAIN_ADMIN; ?>new-content" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Новая запись
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo DOMAIN_ADMIN; ?>logout" class="dropdown-item dropdown-item-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                            Выйти
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

<!-- Main content -->
<main class="main-content">
    <div class="site-container">
        <?php if ($WHERE_AM_I == 'home'): ?>
            <?php include(THEME_DIR_PHP . 'home.php'); ?>
        <?php elseif ($WHERE_AM_I == 'page'): ?>
            <?php include(THEME_DIR_PHP . 'page.php'); ?>
        <?php elseif ($WHERE_AM_I == 'category'): ?>
            <?php include(THEME_DIR_PHP . 'category.php'); ?>
        <?php elseif ($WHERE_AM_I == 'search'): ?>
            <?php include(THEME_DIR_PHP . 'search.php'); ?>
        <?php else: ?>
            <?php include(THEME_DIR_PHP . 'home.php'); ?>
        <?php endif; ?>
    </div>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="site-container">
        <div class="footer-inner">
            <div class="footer-logo">
                <div class="logo-icon"><?php echo mb_substr($site->title(), 0, 1); ?></div>
                <span class="logo-text"><?php echo $site->title(); ?></span>
            </div>
            <div class="footer-links">
                <a href="<?php echo DOMAIN; ?>">Explore</a>
                <a href="<?php echo DOMAIN; ?>blog">Блог</a>
                <?php foreach ($staticContent as $staticPage): ?>
                    <?php if ($staticPage->slug() == 'about'): ?>
                    <a href="<?php echo $staticPage->permalink(); ?>">Обо мне</a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <p class="footer-copy">© <?php echo date('Y'); ?> <?php echo $site->title(); ?></p>
        </div>
    </div>
</footer>

<?php echo Theme::js('js/theme.js') ?>
<?php Theme::plugins('siteBodyEnd') ?>
</body>
</html>
