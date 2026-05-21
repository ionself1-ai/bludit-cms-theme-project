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
        <?php
        // Безопасный обход категорий через $categories->db
        try {
            if (isset($categories) && is_object($categories) && isset($categories->db) && is_array($categories->db)) {
                $catBaseUrl = defined('DOMAIN_CATEGORIES') ? DOMAIN_CATEGORIES : (DOMAIN . 'category/');
                foreach ($categories->db as $catKey => $catFields) {
                    $catName = isset($catFields['name']) ? $catFields['name'] : $catKey;
                    echo '<a href="' . htmlspecialchars($catBaseUrl . $catKey) . '" class="filter-btn">' . htmlspecialchars($catName) . '</a>';
                }
            }
        } catch (Exception $e) { /* пропуск */ }
        ?>
    </div>

    <!-- Posts grid -->
    <?php if (!empty($content)): ?>
    <div class="posts-grid">
        <?php foreach ($content as $post): ?>
        <article class="post-card">
            <?php
            $coverImg = '';
            try { $coverImg = $post->coverImage(true); } catch (Exception $e) {}
            ?>
            <?php if (!empty($coverImg)): ?>
            <a href="<?php echo $post->permalink(); ?>" class="post-cover">
                <img src="<?php echo htmlspecialchars($coverImg); ?>" alt="<?php echo htmlspecialchars($post->title()); ?>" loading="lazy">
            </a>
            <?php endif; ?>
            <div class="post-card-body">
                <div class="post-meta-top">
                    <?php if ($post->category()): ?>
                    <a href="<?php echo $post->categoryPermalink(); ?>" class="post-category"><?php echo htmlspecialchars($post->category()); ?></a>
                    <?php endif; ?>
                    <span class="post-date"><?php echo $post->date(); ?></span>
                </div>
                <h2 class="post-title">
                    <a href="<?php echo $post->permalink(); ?>"><?php echo htmlspecialchars($post->title()); ?></a>
                </h2>
                <?php if ($post->description()): ?>
                <p class="post-excerpt"><?php echo htmlspecialchars($post->description()); ?></p>
                <?php endif; ?>
                <div class="post-footer">
                    <div class="post-tags">
                        <?php
                        $postTags = $post->tags(true);
                        if (!empty($postTags) && is_array($postTags)):
                            $shownTags = array_slice($postTags, 0, 2);
                            foreach ($shownTags as $tagKey => $tagName):
                        ?>
                        <span class="post-tag">#<?php echo htmlspecialchars($tagName); ?></span>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                    <a href="<?php echo $post->permalink(); ?>" class="post-read-more">Читать →</a>
                </div>
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
    <?php if (isset($numberOfPages) && $numberOfPages > 1): ?>
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
