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
        <p>Ничего не найдено. Попробуйте другой запрос.</p>
        <a href="<?php echo DOMAIN; ?>" class="filter-btn filter-btn-active" style="display:inline-flex;margin-top:1rem;">← На главную</a>
    </div>
    <?php endif; ?>

</div>
