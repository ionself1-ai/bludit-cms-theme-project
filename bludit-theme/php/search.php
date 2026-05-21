<?php
// Search results page
?>
<div class="page-home">

    <div class="hero">
        <h1 class="hero-title">Результаты поиска</h1>
        <?php if (!empty($_GET['q'])): ?>
        <p class="hero-desc">По запросу: «<?php echo htmlspecialchars($_GET['q']); ?>»</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($content)): ?>
    <div class="posts-grid">
        <?php foreach ($content as $post): ?>
        <article class="post-card">
            <div class="post-meta-top">
                <?php if ($post->category()): ?>
                <span class="post-category"><?php echo $post->category(); ?></span>
                <?php endif; ?>
                <span class="post-date"><?php echo $post->date('d M Y'); ?></span>
            </div>
            <h2 class="post-title">
                <a href="<?php echo $post->permalink(); ?>"><?php echo $post->title(); ?></a>
            </h2>
            <p class="post-excerpt"><?php echo $post->description(); ?></p>
            <div class="post-footer">
                <a href="<?php echo $post->permalink(); ?>" class="post-read-more">Читать →</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>Ничего не найдено. Попробуйте другой запрос.</p>
        <a href="<?php echo DOMAIN; ?>" class="filter-btn filter-btn-active" style="display:inline-flex;margin-top:1rem;">← На главную</a>
    </div>
    <?php endif; ?>

</div>
