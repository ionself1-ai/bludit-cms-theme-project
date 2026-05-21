<?php
// Category page
?>
<div class="page-home">

    <div class="hero">
        <h1 class="hero-title"><?php echo $category->name(); ?></h1>
        <?php if ($category->description()): ?>
        <p class="hero-desc"><?php echo $category->description(); ?></p>
        <?php endif; ?>
    </div>

    <div class="filter-bar">
        <a href="<?php echo DOMAIN; ?>" class="filter-btn">← Все</a>
        <span class="filter-btn filter-btn-active"><?php echo $category->name(); ?></span>
    </div>

    <?php if (!empty($content)): ?>
    <div class="posts-grid">
        <?php foreach ($content as $post): ?>
        <article class="post-card">
            <div class="post-meta-top">
                <span class="post-category"><?php echo $post->category(); ?></span>
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
        <p>В этой категории пока нет записей.</p>
    </div>
    <?php endif; ?>

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
