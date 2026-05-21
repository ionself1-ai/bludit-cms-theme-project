<?php $cat = Categories::get($post['category'] ?? ''); ?>
<div class="page-single">
    <article class="single-post">
        <header class="single-header">
            <div class="single-meta-top">
                <?php if ($cat): ?>
                    <a href="<?= BASE_URL ?>?route=category/<?= urlencode($cat['key']) ?>" class="post-category"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endif; ?>
                <span class="post-date"><?= date('d.m.Y', strtotime($post['date'])) ?></span>
            </div>
            <h1 class="single-title"><?= htmlspecialchars($post['title']) ?></h1>
            <?php if (!empty($post['description'])): ?>
                <p class="single-desc"><?= htmlspecialchars($post['description']) ?></p>
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
    </article>

    <div class="back-link"><a href="<?= BASE_URL ?>">← Все записи</a></div>
</div>
