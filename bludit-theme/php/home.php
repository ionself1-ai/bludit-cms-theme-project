<?php
// Home page — list of posts
?>
<div class="page-home">

    <!-- Hero -->
    <div class="hero">
        <div class="hero-badge">
            <span class="hero-dot"></span>
            Свежие материалы
        </div>
        <h1 class="hero-title"><?php echo $site->title(); ?></h1>
        <p class="hero-desc"><?php echo $site->description(); ?></p>
    </div>

    <!-- Category filter -->
    <div class="filter-bar">
        <a href="<?php echo DOMAIN; ?>" class="filter-btn filter-btn-active">Все</a>
        <?php foreach ($categories as $category): ?>
        <a href="<?php echo $category->permalink(); ?>" class="filter-btn">
            <?php echo $category->name(); ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Posts grid -->
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
                <div class="post-tags">
                    <?php if ($post->tagsArray()): ?>
                        <?php foreach (array_slice($post->tagsArray(), 0, 2) as $tag): ?>
                        <span class="post-tag">#<?php echo $tag; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="<?php echo $post->permalink(); ?>" class="post-read-more">Читать →</a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <p>Записей пока нет. Создайте первую!</p>
    </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($numberOfPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?>
        <a href="<?php echo $paginationPrevURL; ?>" class="pagination-btn">← Назад</a>
        <?php endif; ?>
        <span class="pagination-info"><?php echo $currentPage; ?> / <?php echo $numberOfPages; ?></span>
        <?php if ($currentPage < $numberOfPages): ?>
        <a href="<?php echo $paginationNextURL; ?>" class="pagination-btn">Вперёд →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
