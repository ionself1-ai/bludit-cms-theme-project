<div class="page-home">
    <div class="hero">
        <h1 class="hero-title"><?= htmlspecialchars($category['name']) ?></h1>
        <?php if (!empty($category['description'])): ?>
            <p class="hero-desc"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="filter-bar">
        <a href="<?= BASE_URL ?>" class="filter-btn">← Все</a>
        <span class="filter-btn filter-btn-active"><?= htmlspecialchars($category['name']) ?></span>
    </div>

    <?php if (!empty($pag['items'])): ?>
    <div class="posts-grid">
        <?php foreach ($pag['items'] as $post): ?>
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
                    <a href="<?= BASE_URL ?>?route=category/<?= urlencode($category['key']) ?>" class="post-category"><?= htmlspecialchars($category['name']) ?></a>
                    <span class="post-date"><?= date('d.m.Y', strtotime($post['date'])) ?></span>
                    <?php $lc = Posts::likes($post['id']); if ($lc > 0): ?>
                        <span class="post-likes" title="<?= $lc ?> лайков"><?= Icon::svg('heart-fill', 12) ?> <?= $lc ?></span>
                    <?php endif; ?>
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
        <div class="empty-state"><p>В этой категории пока нет записей.</p></div>
    <?php endif; ?>

    <?php if ($pag['pages'] > 1): ?>
    <div class="pagination">
        <?php if ($pag['page'] > 1): ?>
            <a href="?route=category/<?= urlencode($category['key']) ?>&page=<?= $pag['page']-1 ?>" class="pagination-btn">← Назад</a>
        <?php endif; ?>
        <span class="pagination-info"><?= $pag['page'] ?> / <?= $pag['pages'] ?></span>
        <?php if ($pag['page'] < $pag['pages']): ?>
            <a href="?route=category/<?= urlencode($category['key']) ?>&page=<?= $pag['page']+1 ?>" class="pagination-btn">Вперёд →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>