<div class="page-single">
    <article class="single-post author-page">
        <div class="author-card">
            <?php if (!empty($author['avatar'])): ?>
                <img src="<?= htmlspecialchars($author['avatar']) ?>" alt="" class="author-avatar">
            <?php else: ?>
                <div class="author-avatar author-avatar-placeholder"><?= htmlspecialchars(mb_substr($author['name'] ?? 'A', 0, 1)) ?></div>
            <?php endif; ?>
            <h1 class="author-name"><?= htmlspecialchars($author['name'] ?? 'Автор') ?></h1>
            <?php if (!empty($author['bio'])): ?>
                <p class="author-bio"><?= nl2br(htmlspecialchars($author['bio'])) ?></p>
            <?php endif; ?>
        </div>
    </article>

    <h2 style="margin-top:3rem; margin-bottom:1.5rem; font-size:1.5rem;">Статьи автора</h2>
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
        <div class="empty-state"><p>Статей пока нет.</p></div>
    <?php endif; ?>
</div>