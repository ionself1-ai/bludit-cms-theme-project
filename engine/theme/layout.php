<?php
$site = Settings::all();
$user = Auth::user();
$categories = Categories::all();
$staticPages = Pages::all();

// === SEO / OpenGraph / Twitter ===
$ogTitle = $pageTitle . ' — ' . $site['site_title'];
$ogDesc  = $site['site_description'] ?? '';
$ogImage = $site['og_default_image'] ?? '';
$ogType  = 'website';
$ogUrl   = (($_SERVER['HTTPS'] ?? '') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');

if (!empty($post) && is_array($post)) {
    $ogTitle = $post['title'] . ' — ' . $site['site_title'];
    if (!empty($post['description'])) $ogDesc = $post['description'];
    if (!empty($post['cover'])) {
        $ogImage = $post['cover'];
        // Если относительный путь — делаем абсолютным
        if (strpos($ogImage, 'http') !== 0) $ogImage = rtrim(BASE_URL, '/') . '/' . ltrim($ogImage, '/');
    }
    $ogType = 'article';
}
if (!empty($category) && is_array($category)) {
    $ogTitle = $category['name'] . ' — ' . $site['site_title'];
    if (!empty($category['description'])) $ogDesc = $category['description'];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ogTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($ogDesc) ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDesc) ?>">
    <meta property="og:type" content="<?= htmlspecialchars($ogType) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($site['site_title']) ?>">
    <meta property="og:locale" content="ru_RU">
    <?php if ($ogImage): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <?php if ($ogType === 'article' && !empty($post)): ?>
    <meta property="article:published_time" content="<?= date('c', strtotime($post['date'] ?? 'now')) ?>">
    <?php foreach ($post['tags'] ?? [] as $t): ?>
    <meta property="article:tag" content="<?= htmlspecialchars($t) ?>">
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDesc) ?>">
    <?php if ($ogImage): ?><meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>"><?php endif; ?>

    <!-- Canonical -->
    <link rel="canonical" href="<?= htmlspecialchars($ogUrl) ?>">

    <?php if ($ogType === 'article' && !empty($post)): ?>
    <!-- JSON-LD Article -->
    <script type="application/ld+json"><?= json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'description' => $ogDesc,
        'image' => $ogImage ?: null,
        'datePublished' => date('c', strtotime($post['date'] ?? 'now')),
        'dateModified' => date('c', strtotime($post['autosavedAt'] ?? $post['date'] ?? 'now')),
        'author' => ['@type' => 'Person', 'name' => $site['author_name'] ?? $site['site_title']],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $site['site_title'],
            'logo' => !empty($site['logo']) ? ['@type' => 'ImageObject', 'url' => $site['logo']] : null,
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $ogUrl],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>
    <?php endif; ?>

    <!-- PWA -->
    <link rel="manifest" href="<?= BASE_URL ?>?route=manifest">
    <meta name="theme-color" content="<?= htmlspecialchars($site['theme_color'] ?? '#3b82f6') ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($site['site_title'] ?? '') ?>">
    <?php if (!empty($site['logo'])): ?>
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($site['logo']) ?>">
    <?php endif; ?>

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
                <?php if (!empty($site['logo'])): ?>
                    <img src="<?= htmlspecialchars($site['logo']) ?>" alt="<?= htmlspecialchars($site['site_title']) ?>" class="logo-img">
                <?php else: ?>
                    <div class="logo-icon"><?= htmlspecialchars(mb_substr($site['site_title'], 0, 2)) ?></div>
                <?php endif; ?>
                <span class="logo-text"><?= htmlspecialchars($site['site_title']) ?></span>
            </a>

            <button class="icon-btn nav-toggle" onclick="toggleMobileNav()" aria-label="Открыть меню">
                <span class="nav-toggle-icon-open"><?= Icon::svg('menu', 20) ?></span>
                <span class="nav-toggle-icon-close"><?= Icon::svg('close', 20) ?></span>
            </button>

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
                <form class="search-wrap" method="get" action="<?= BASE_URL ?>" autocomplete="off">
                    <input type="hidden" name="route" value="search">
                    <span class="search-icon"><?= Icon::svg('search', 16) ?></span>
                    <input type="search" name="q" id="navbar-search" class="search-input" placeholder="Поиск..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" aria-autocomplete="list" aria-controls="search-suggest" autocomplete="off">
                    <div id="search-suggest" class="search-suggest" role="listbox" aria-label="Подсказки поиска"></div>
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

<?php
// Определяем активную категорию для подсветки в мобильной ленте
$activeCatKey = '';
if (($template ?? '') === 'category' && !empty($category['key'])) {
    $activeCatKey = $category['key'];
}
$isHome = ($template ?? '') === 'home';
?>
<nav class="mobile-cats" aria-label="Категории">
    <div class="mobile-cats-track">
        <a href="<?= BASE_URL ?>" class="mobile-cat <?= $isHome ? 'is-active' : '' ?>">
            <?= Icon::svg('home', 14) ?> Все
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>"
               class="mobile-cat <?= $activeCatKey === $cat['key'] ? 'is-active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= BASE_URL ?>?route=tags" class="mobile-cat">
            <?= Icon::svg('tag', 14) ?> Теги
        </a>
    </div>
</nav>

<main class="site-main">
    <div class="site-container">
        <?php require THEME_PATH . '/' . $template . '.php'; ?>
    </div>
</main>

<footer class="site-footer">
    <div class="site-container">
        <div class="subscribe-block">
            <div class="subscribe-text">
                <div class="subscribe-title">Подпишитесь на новые статьи</div>
                <div class="subscribe-desc">Раз в неделю присылаю свежие материалы. Без спама.</div>
            </div>
            <form class="subscribe-form" data-subscribe>
                <input type="email" name="email" required placeholder="ваш@email.ru" class="subscribe-input">
                <button type="submit" class="subscribe-btn">Подписаться</button>
                <div class="subscribe-status"></div>
            </form>
        </div>
        <div class="footer-inner">
            <div class="footer-logo">
                <?php if (!empty($site['logo'])): ?>
                    <img src="<?= htmlspecialchars($site['logo']) ?>" alt="" class="logo-img">
                <?php else: ?>
                    <div class="logo-icon"><?= htmlspecialchars(mb_substr($site['site_title'], 0, 2)) ?></div>
                <?php endif; ?>
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

<button type="button" id="pwa-install-btn" class="pwa-install-btn" style="display:none;" aria-label="Установить приложение">
    <?= Icon::svg('upload', 16) ?>
    <span>Установить приложение</span>
</button>

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

// Автоскролл мобильной ленты категорий к активной
(function(){
    const track = document.querySelector('.mobile-cats-track');
    if (!track) return;
    const active = track.querySelector('.mobile-cat.is-active');
    if (active) {
        const offset = active.offsetLeft - (track.clientWidth - active.clientWidth) / 2;
        track.scrollLeft = Math.max(0, offset);
    }
})();

// Автоскрытие шапки и мобильной ленты при скролле вниз (Instagram/Twitter-style)
(function(){
    if (!window.matchMedia('(max-width: 720px)').matches) return;
    let lastY = window.scrollY;
    let ticking = false;
    const DELTA = 6;
    const TOP_THRESHOLD = 80;

    function update() {
        const y = window.scrollY;
        const diff = y - lastY;
        // Игнор мелких движений
        if (Math.abs(diff) < DELTA) { ticking = false; return; }
        // У самого верха — всегда показано
        if (y < TOP_THRESHOLD) {
            document.body.classList.remove('nav-hidden');
        } else if (diff > 0) {
            // Скроллим вниз — прячем
            document.body.classList.add('nav-hidden');
            // На всякий случай закрываем мобильное меню
            document.body.classList.remove('mobile-nav-open');
        } else {
            // Скроллим вверх — показываем
            document.body.classList.remove('nav-hidden');
        }
        lastY = y;
        ticking = false;
    }
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }, { passive: true });
})();

function toggleMobileNav() {
    document.body.classList.toggle('mobile-nav-open');
}
document.addEventListener('click', e => {
    if (document.body.classList.contains('mobile-nav-open')) {
        if (!e.target.closest('.navbar-nav') && !e.target.closest('.nav-toggle')) {
            document.body.classList.remove('mobile-nav-open');
        }
    }
});

// PWA: регистрация Service Worker + кнопка «Установить»
(function(){
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('<?= BASE_URL ?>theme/sw.js', { scope: '<?= BASE_URL ?>' }).catch(() => {});
        });
    }
    // beforeinstallprompt — показываем плавающую кнопку «Установить»
    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        const btn = document.getElementById('pwa-install-btn');
        if (btn) {
            btn.style.display = 'inline-flex';
            btn.addEventListener('click', async () => {
                btn.style.display = 'none';
                deferredPrompt.prompt();
                await deferredPrompt.userChoice;
                deferredPrompt = null;
            }, { once: true });
        }
    });
    window.addEventListener('appinstalled', () => {
        const btn = document.getElementById('pwa-install-btn');
        if (btn) btn.style.display = 'none';
    });
})();

// Мгновенный поиск с подсказками
(function(){
    const input = document.getElementById('navbar-search');
    const box = document.getElementById('search-suggest');
    if (!input || !box) return;

    let timer = null;
    let lastQ = '';
    let activeIdx = -1;
    let items = [];

    function close() {
        box.classList.remove('is-open');
        box.innerHTML = '';
        activeIdx = -1;
        items = [];
    }
    function highlight(text, q) {
        if (!q) return text;
        const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return text.replace(re, '<mark>$1</mark>');
    }
    function render(data, q) {
        const all = [];
        (data.cats || []).forEach(c => all.push({ type:'cat', label:c.name, url:'<?= BASE_URL ?>?route=category/' + encodeURIComponent(c.key) }));
        (data.tags || []).forEach(t => all.push({ type:'tag', label:'#' + t, url:'<?= BASE_URL ?>?route=tag/' + encodeURIComponent(t) }));
        (data.items || []).forEach(p => all.push({ type:'post', label:p.title, cover:p.cover, date:p.date, url:'<?= BASE_URL ?>?route=post/' + encodeURIComponent(p.slug) }));
        items = all;

        if (!all.length) {
            box.innerHTML = '<div class="ss-empty">Ничего не найдено по «' + escapeHtml(q) + '»</div>';
            box.classList.add('is-open');
            return;
        }

        let html = '';
        const cats = all.filter(i => i.type==='cat');
        const tags = all.filter(i => i.type==='tag');
        const posts = all.filter(i => i.type==='post');

        if (cats.length || tags.length) {
            html += '<div class="ss-chips">';
            cats.forEach(c => html += '<a href="' + c.url + '" class="ss-chip ss-chip-cat" data-type="cat">' + highlight(escapeHtml(c.label), q) + '</a>');
            tags.forEach(t => html += '<a href="' + t.url + '" class="ss-chip ss-chip-tag" data-type="tag">' + highlight(escapeHtml(t.label), q) + '</a>');
            html += '</div>';
        }
        if (posts.length) {
            html += '<div class="ss-section-title">Статьи</div>';
            posts.forEach((p, i) => {
                const realIdx = all.indexOf(p);
                const img = p.cover
                    ? '<img src="' + escapeAttr(p.cover) + '" alt="" loading="lazy">'
                    : '<div class="ss-no-cover"></div>';
                const date = p.date ? new Date(p.date.replace(' ', 'T')).toLocaleDateString('ru-RU', { day:'2-digit', month:'2-digit', year:'numeric' }) : '';
                html += '<a href="' + p.url + '" class="ss-item" data-idx="' + realIdx + '">'
                     + img
                     + '<div class="ss-item-body">'
                     +   '<div class="ss-item-title">' + highlight(escapeHtml(p.label), q) + '</div>'
                     +   (date ? '<div class="ss-item-meta">' + date + '</div>' : '')
                     + '</div></a>';
            });
        }
        html += '<a href="<?= BASE_URL ?>?route=search&q=' + encodeURIComponent(q) + '" class="ss-all">Показать все результаты по «' + escapeHtml(q) + '» →</a>';

        box.innerHTML = html;
        box.classList.add('is-open');
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function escapeAttr(s) { return escapeHtml(s); }

    async function doSearch(q) {
        if (q === lastQ) return;
        lastQ = q;
        if (q.length < 2) { close(); return; }
        try {
            const r = await fetch('<?= BASE_URL ?>?route=suggest&q=' + encodeURIComponent(q));
            const d = await r.json();
            if (d.ok && lastQ === q) render(d, q);
        } catch (_) { /* ignore */ }
    }

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(timer);
        timer = setTimeout(() => doSearch(q), 180);
    });
    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && box.innerHTML) box.classList.add('is-open');
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-wrap')) close();
    });
    input.addEventListener('keydown', (e) => {
        const list = box.querySelectorAll('.ss-item, .ss-chip, .ss-all');
        if (e.key === 'Escape') { close(); input.blur(); return; }
        if (!list.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIdx = Math.min(activeIdx + 1, list.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIdx = Math.max(activeIdx - 1, 0);
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            list[activeIdx].click();
            return;
        } else { return; }
        list.forEach((el, i) => el.classList.toggle('is-active', i === activeIdx));
        if (list[activeIdx]) list[activeIdx].scrollIntoView({ block: 'nearest' });
    });
})();

// Лайки на постах
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const id = btn.dataset.postId;
        if (!id || btn.disabled) return;
        btn.disabled = true;
        // Оптимистично переключаем
        const liked = btn.classList.toggle('is-liked');
        const countEl = btn.querySelector('.like-count');
        let cnt = parseInt(countEl.dataset.count || '0', 10);
        cnt = Math.max(0, cnt + (liked ? 1 : -1));
        countEl.dataset.count = cnt;
        countEl.textContent = cnt;
        // Анимация
        btn.classList.add('is-bumping');
        setTimeout(() => btn.classList.remove('is-bumping'), 400);
        // Обновляем подсказку
        const hint = btn.parentElement.querySelector('.like-hint');
        if (hint) hint.textContent = liked ? hint.dataset.hintLiked : hint.dataset.hintEmpty;
        // Запрос
        try {
            const fd = new FormData();
            fd.append('post_id', id);
            const r = await fetch('<?= BASE_URL ?>?route=like', { method: 'POST', body: fd });
            const d = await r.json();
            if (d.ok) {
                countEl.dataset.count = d.count;
                countEl.textContent = d.count;
                btn.classList.toggle('is-liked', !!d.liked);
                btn.setAttribute('aria-pressed', d.liked ? 'true' : 'false');
            }
        } catch (_) {
            // Откатываем при ошибке
            btn.classList.toggle('is-liked');
            cnt = Math.max(0, cnt + (liked ? -1 : 1));
            countEl.dataset.count = cnt;
            countEl.textContent = cnt;
        } finally {
            btn.disabled = false;
        }
    });
});

// Подписка на новые статьи (все формы с data-subscribe)
document.querySelectorAll('form[data-subscribe]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const status = form.querySelector('.subscribe-status');
        const input = form.querySelector('input[name="email"]');
        const btn = form.querySelector('button[type="submit"]');
        const email = (input.value || '').trim();
        if (!email) return;
        btn.disabled = true;
        status.textContent = 'Отправка...';
        status.className = 'subscribe-status is-loading';
        try {
            const fd = new FormData();
            fd.append('email', email);
            const r = await fetch('<?= BASE_URL ?>?route=subscribe&action=new', { method: 'POST', body: fd });
            const d = await r.json();
            if (d.ok) {
                status.textContent = '✓ ' + (d.message || 'Готово');
                status.className = 'subscribe-status is-success';
                input.value = '';
            } else {
                status.textContent = d.error || 'Ошибка';
                status.className = 'subscribe-status is-error';
            }
        } catch (_) {
            status.textContent = 'Ошибка сети';
            status.className = 'subscribe-status is-error';
        } finally {
            btn.disabled = false;
        }
    });
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