<div class="page-home">
    <div class="hero">
        <h1 class="hero-title">#<?= htmlspecialchars($tag) ?></h1>
        <p class="hero-desc">Все статьи с этим тегом</p>
    </div>

    <?php if (!empty($pag['items'])): ?>
    <div class="posts-grid">
        <?php foreach ($pag['items'] as $post): $cat = Categories::get($post['category'] ?? ''); ?>
        <article class="post-card">
            <?php if (!empty($post['cover'])): ?>
            <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>" class="post-cover<?= !empty($post['title_on_cover']) ? ' has-overlay' : '' ?>">
                <img src="<?= htmlspecialchars($post['cover']) ?>" alt="" loading="lazy">
                <?= Posts::coverOverlayHtml($post) ?>
            </a>
            <?php endif; ?>
            <div class="post-card-body">
                <div class="post-meta-top">
                    <?php if ($cat): ?><a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a><?php endif; ?>
                    <span class="post-date"><?= date('d.m.Y', strtotime($post['date'])) ?></span>
                </div>
                <h2 class="post-title"><a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                <p class="post-excerpt"><?= htmlspecialchars(Posts::excerpt($post)) ?></p>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="empty-state"><p>По этому тегу ничего не найдено.</p></div>
    <?php endif; ?>
</div>