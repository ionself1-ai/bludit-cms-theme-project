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

        <div class="share-buttons">
            <span><?= Icon::svg('share', 14) ?> Поделиться:</span>
            <?php $url = BASE_URL . '?route=post/' . urlencode($post['slug']); ?>
            <a href="https://t.me/share/url?url=<?= urlencode($url) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="share-btn"><?= Icon::svg('telegram', 14) ?> Telegram</a>
            <a href="https://vk.com/share.php?url=<?= urlencode($url) ?>" target="_blank" class="share-btn"><?= Icon::svg('vk', 14) ?> VK</a>
            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($url) ?>&text=<?= urlencode($post['title']) ?>" target="_blank" class="share-btn"><?= Icon::svg('twitter', 14) ?> Twitter</a>
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