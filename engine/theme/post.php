<?php
$cat = Categories::get($post['category'] ?? '');
$rt = Posts::readingTime($post);
$related = Posts::related($post, 3);
$author = Auth::user();
?>
<div class="page-single">
    <article class="single-post">
        <header class="single-header">
            <div class="single-meta-top">
                <?php if ($cat): ?>
                    <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endif; ?>
                <span class="post-date"><?= date('d.m.Y', strtotime($post['date'])) ?></span>
                <span class="post-reading-time"><?= Icon::svg('clock', 12) ?> <?= $rt ?> мин</span>
                <?php $views = Stats::postViews($post['id']); ?>
                <span class="post-views" title="<?= number_format($views, 0, '.', ' ') ?> просмотров"><?= Icon::svg('eye', 12) ?> <?= Stats::formatViews($views) ?></span>
                <?php if (!empty($post['sticky'])): ?>
                    <span class="post-sticky-badge"><?= Icon::svg('pin', 12) ?> Закреплено</span>
                <?php endif; ?>
            </div>
            <h1 class="single-title"><?= htmlspecialchars($post['title']) ?></h1>
            <?php if (!empty($post['description'])): ?>
                <p class="single-desc"><?= htmlspecialchars($post['description']) ?></p>
            <?php endif; ?>

            <?php if ($author): ?>
            <a href="<?= BASE_URL ?>?route=author" class="author-byline">
                <?php if (!empty($author['avatar'])): ?>
                    <img src="<?= htmlspecialchars($author['avatar']) ?>" class="author-byline-avatar" alt="">
                <?php else: ?>
                    <div class="author-byline-avatar author-avatar-placeholder"><?= htmlspecialchars(mb_substr($author['name'] ?? 'A', 0, 1)) ?></div>
                <?php endif; ?>
                <span><?= htmlspecialchars($author['name'] ?? 'Автор') ?></span>
            </a>
            <?php endif; ?>
        </header>

        <?php if (!empty($post['cover'])): ?>
        <div class="single-cover">
            <img src="<?= htmlspecialchars($post['cover']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="single-content">
            <?= Posts::renderContent($post['content'] ?? []) ?>
        </div>

        <?php if (!empty($post['tags'])): ?>
        <footer class="single-footer">
            <div class="single-tags">
                <?php foreach ($post['tags'] as $tag): ?>
                    <a href="<?= BASE_URL ?>?route=tag/<?= urlencode($tag) ?>" class="post-tag">#<?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            </div>
        </footer>
        <?php endif; ?>

        <?php
        $likeCount = Posts::likes($post['id']);
        $isLiked = !empty($_COOKIE['liked_' . $post['id']]);
        ?>
        <div class="like-bar">
            <button type="button" class="like-btn <?= $isLiked ? 'is-liked' : '' ?>" data-post-id="<?= htmlspecialchars($post['id']) ?>" aria-pressed="<?= $isLiked ? 'true' : 'false' ?>" aria-label="Поставить лайк">
                <span class="like-icon-outline"><?= Icon::svg('heart', 20) ?></span>
                <span class="like-icon-fill"><?= Icon::svg('heart-fill', 20) ?></span>
                <span class="like-count" data-count="<?= (int)$likeCount ?>"><?= (int)$likeCount ?></span>
            </button>
            <span class="like-hint" data-hint-empty="Понравилась статья? Поставьте лайк" data-hint-liked="Спасибо!"><?= $isLiked ? 'Спасибо!' : 'Понравилась статья? Поставьте лайк' ?></span>
        </div>
        <?php
        // Абсолютный URL поста (для корректного шаринга)
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $shareUrl = $scheme . '://' . $host . str_replace(BASE_URL, BASE_URL, BASE_URL) . '?route=post/' . urlencode($post['slug']);
        // Приведём к абсолютной форме на случай относительного BASE_URL
        if (strpos(BASE_URL, 'http') === 0) {
            $shareUrl = rtrim(BASE_URL, '/') . '/?route=post/' . urlencode($post['slug']);
        }
        ?>
        <div class="share-buttons" data-share-url="<?= htmlspecialchars($shareUrl) ?>" data-share-title="<?= htmlspecialchars($post['title']) ?>">
            <span class="share-label"><?= Icon::svg('share', 14) ?> Поделиться:</span>
            <a href="https://t.me/share/url?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="share-btn share-tg" aria-label="Поделиться в Telegram"><?= Icon::svg('telegram', 14) ?> Telegram</a>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' — ' . $shareUrl) ?>" target="_blank" rel="noopener" class="share-btn share-wa" aria-label="Поделиться в WhatsApp"><?= Icon::svg('whatsapp', 14) ?> WhatsApp</a>
            <a href="https://vk.com/share.php?url=<?= urlencode($shareUrl) ?>" target="_blank" rel="noopener" class="share-btn share-vk" aria-label="Поделиться ВКонтакте"><?= Icon::svg('vk', 14) ?> VK</a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($shareUrl) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" rel="noopener" class="share-btn share-tw" aria-label="Поделиться в Twitter"><?= Icon::svg('twitter', 14) ?> Twitter</a>
            <button type="button" class="share-btn share-copy" aria-label="Скопировать ссылку"><?= Icon::svg('link', 14) ?> <span class="share-copy-text">Копировать</span></button>
            <button type="button" class="share-btn share-native" aria-label="Системное меню" style="display:none;"><?= Icon::svg('share', 14) ?> Поделиться</button>
        </div>
        <script>
        (function(){
            const wrap = document.currentScript.previousElementSibling;
            if (!wrap || !wrap.classList.contains('share-buttons')) return;
            const url = wrap.dataset.shareUrl;
            const title = wrap.dataset.shareTitle;
            const copyBtn = wrap.querySelector('.share-copy');
            const copyText = wrap.querySelector('.share-copy-text');
            const nativeBtn = wrap.querySelector('.share-native');

            if (copyBtn) {
                copyBtn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(url);
                        copyText.textContent = '✓ Скопировано';
                        setTimeout(() => copyText.textContent = 'Копировать', 1800);
                    } catch (e) {
                        // fallback
                        const ta = document.createElement('textarea');
                        ta.value = url;
                        document.body.appendChild(ta);
                        ta.select();
                        try { document.execCommand('copy'); copyText.textContent = '✓ Скопировано'; setTimeout(() => copyText.textContent = 'Копировать', 1800); } catch(_){}
                        document.body.removeChild(ta);
                    }
                });
            }
            // Системное меню шаринга (iOS/Android)
            if (navigator.share && nativeBtn) {
                nativeBtn.style.display = '';
                nativeBtn.addEventListener('click', async () => {
                    try { await navigator.share({ title, url }); } catch(_){}
                });
            }
        })();
        </script>
        <div class="post-subscribe">
            <div class="post-subscribe-icon">📬</div>
            <div class="post-subscribe-text">
                <div class="post-subscribe-title">Понравилось? Подпишитесь на новые статьи</div>
                <div class="post-subscribe-desc">Раз в неделю — свежие материалы на почту. Без спама.</div>
            </div>
            <form class="subscribe-form" data-subscribe>
                <input type="email" name="email" required placeholder="ваш@email.ru" class="subscribe-input">
                <button type="submit" class="subscribe-btn">Подписаться</button>
                <div class="subscribe-status"></div>
            </form>
        </div>
    </article>

    <?php if (!empty($related)): ?>
    <section class="related-posts">
        <h2 class="related-title">Похожие статьи</h2>
        <div class="posts-grid">
            <?php foreach ($related as $rp): ?>
            <article class="post-card">
                <?php if (!empty($rp['cover'])): ?>
                <a href="<?= BASE_URL ?>?route=post/<?= urlencode($rp['slug']) ?>" class="post-cover">
                    <img src="<?= htmlspecialchars($rp['cover']) ?>" alt="" loading="lazy">
                </a>
                <?php endif; ?>
                <div class="post-card-body">
                    <div class="post-meta-top">
                        <span class="post-date"><?= date('d.m.Y', strtotime($rp['date'])) ?></span>
                    </div>
                    <h3 class="post-title"><a href="<?= BASE_URL ?>?route=post/<?= urlencode($rp['slug']) ?>"><?= htmlspecialchars($rp['title']) ?></a></h3>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="back-link"><a href="<?= BASE_URL ?>">← Все записи</a></div>
</div>