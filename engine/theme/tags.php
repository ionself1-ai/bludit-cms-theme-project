<div class="page-home">
    <div class="hero">
        <h1 class="hero-title">Все теги</h1>
        <p class="hero-desc">Облако тегов сайта</p>
    </div>
    <div class="tags-cloud">
        <?php foreach ($tagsCloud as $tag => $count): ?>
            <a href="<?= BASE_URL ?>?route=tag/<?= urlencode($tag) ?>" class="tag-cloud-item" style="font-size: <?= min(1.5, 0.85 + $count * 0.1) ?>rem;">
                #<?= htmlspecialchars($tag) ?> <span class="tag-count"><?= $count ?></span>
            </a>
        <?php endforeach; ?>
        <?php if (empty($tagsCloud)): ?>
            <p class="empty-state">Тегов пока нет.</p>
        <?php endif; ?>
    </div>
</div>
