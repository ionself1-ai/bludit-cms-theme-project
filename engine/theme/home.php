<div class="page-home">
    <div class="hero">
        <div class="hero-badge"><span class="hero-dot"></span>Свежие материалы</div>
        <h1 class="hero-title"><?= htmlspecialchars($site['site_title']) ?></h1>
        <p class="hero-desc"><?= htmlspecialchars($site['site_description']) ?></p>
    </div>

    <div class="filter-bar">
        <a href="<?= BASE_URL ?>" class="filter-btn filter-btn-active">Все</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="filter-btn"><?= htmlspecialchars($cat['name']) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($pag['items'])): ?>
    <div class="posts-grid">
        <?php foreach ($pag['items'] as $post): $cat = Categories::get($post['category'] ?? ''); ?>
        <article class="post-card <?= !empty($post['sticky']) ? 'is-sticky' : '' ?>">
            <?php if (!empty($post['cover'])): ?>
            <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>" class="post-cover<?= !empty($post['title_on_cover']) ? ' has-overlay' : '' ?>">
                <img src="<?= htmlspecialchars($post['cover']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                <?php if (!empty($post['sticky'])): ?><span class="sticky-corner">📌</span><?php endif; ?>
                <?= Posts::coverOverlayHtml($post) ?>
            </a>
            <?php endif; ?>
            <div class="post-card-body">
                <div class="post-meta-top">
                    <?php if ($cat): ?>
                        <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endif; ?>
                    <span class="post-date"><?= date('d.m.Y', strtotime($post['date'])) ?></span>
                </div>
                <h2 class="post-title">
                    <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <p class="post-excerpt"><?= htmlspecialchars(Posts::excerpt($post)) ?></p>
                <div class="post-footer">
                    <div class="post-tags">
                        <?php foreach (array_slice($post['tags'] ?? [], 0, 2) as $tag): ?>
                            <a href="<?= BASE_URL ?>?route=tag/<?= urlencode($tag) ?>" class="post-tag">#<?= htmlspecialchars($tag) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>" class="post-read-more">Читать →</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="empty-state"><p>Записей пока нет.</p></div>
    <?php endif; ?>

    <?php if ($pag['pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pag['page'] > 1): ?>
            <a href="?route=<?= htmlspecialchars($route) ?>&page=<?= $pag['page']-1 ?>" class="pagination-btn">← Назад</a>
        <?php endif; ?>
        <span class="pagination-info"><?= $pag['page'] ?> / <?= $pag['pages'] ?></span>
        <?php if ($pag['page'] < $pag['pages']): ?>
            <a href="?route=<?= htmlspecialchars($route) ?>&page=<?= $pag['page']+1 ?>" class="pagination-btn">Вперёд →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>