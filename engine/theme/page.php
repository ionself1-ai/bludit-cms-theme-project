<div class="page-single">
    <article class="single-post">
        <header class="single-header">
            <h1 class="single-title"><?= htmlspecialchars($page['title']) ?></h1>
        </header>
        <div class="single-content">
            <?= Posts::renderContent($page['content'] ?? []) ?>
        </div>
    </article>
    <div class="back-link"><a href="<?= BASE_URL ?>">← На главную</a></div>
</div>
