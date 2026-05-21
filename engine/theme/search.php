<div class="page-home">
    <div class="hero">
        <h1 class="hero-title">Результаты поиска</h1>
        <?php if (!empty($query)): ?>
            <p class="hero-desc">По запросу: «<?= htmlspecialchars($query) ?>»</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($pag['items'])): ?>
    <div class="posts-grid">
        <?php foreach ($pag['items'] as $post): $cat = Categories::get($post['category'] ?? ''); ?>
        <article class="post-card">
            <?php if (!empty($post['cover'])): ?>
            <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>" class="post-cover<?= !empty($post['title_on_cover']) ? ' has-overlay' : '' ?>">
                <img src="<?= htmlspecialchars($post['cover']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                <?= Posts::coverOverlayHtml($post) ?>
            </a>
            <?php endif; ?>
            <div class="post-card-body">
                <div class="post-meta-top">
                    <?php if ($cat): ?>
                        <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endif; ?>
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
                    <a href="<?= BASE_URL ?>?route=post/<?= urlencode($post['slug']) ?>" class="post-read-more">Читать →</a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="empty-state">
            <p>Ничего не найдено.</p>
            <a href="<?= BASE_URL ?>" class="filter-btn filter-btn-active" style="display:inline-flex;margin-top:1rem;">← На главную</a>
        </div>
    <?php endif; ?>
</div>