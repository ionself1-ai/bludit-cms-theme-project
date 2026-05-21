<?php
// Single page / post view
?>
<div class="page-single">

    <article class="single-post">

        <!-- Post header -->
        <header class="single-header">
            <div class="single-meta-top">
                <?php if ($page->category()): ?>
                <a href="<?php echo $page->categoryPermalink(); ?>" class="post-category">
                    <?php echo htmlspecialchars($page->category()); ?>
                </a>
                <?php endif; ?>
                <span class="post-date"><?php echo $page->date(); ?></span>
                <?php
                $rt = '';
                try { $rt = $page->readingTime(); } catch (Exception $e) {}
                ?>
                <?php if (!empty($rt)): ?>
                <span class="post-reading-time"><?php echo htmlspecialchars($rt); ?></span>
                <?php endif; ?>
            </div>

            <h1 class="single-title"><?php echo htmlspecialchars($page->title()); ?></h1>

            <?php if ($page->description()): ?>
            <p class="single-desc"><?php echo htmlspecialchars($page->description()); ?></p>
            <?php endif; ?>
        </header>

        <!-- Cover image -->
        <?php
        $coverImg = '';
        try { $coverImg = $page->coverImage(true); } catch (Exception $e) {}
        ?>
        <?php if (!empty($coverImg)): ?>
        <div class="single-cover">
            <img src="<?php echo htmlspecialchars($coverImg); ?>" alt="<?php echo htmlspecialchars($page->title()); ?>">
        </div>
        <?php endif; ?>

        <!-- Post content -->
        <div class="single-content">
            <?php echo $page->content(); ?>
        </div>

        <!-- Tags -->
        <?php
        $pageTags = $page->tags(true);
        if (!empty($pageTags) && is_array($pageTags)):
        ?>
        <footer class="single-footer">
            <div class="single-tags">
                <?php foreach ($pageTags as $tagKey => $tagName): ?>
                <a href="<?php echo DOMAIN; ?>tag/<?php echo urlencode($tagKey); ?>" class="post-tag">#<?php echo htmlspecialchars($tagName); ?></a>
                <?php endforeach; ?>
            </div>
        </footer>
        <?php endif; ?>

    </article>

    <!-- Back link -->
    <div class="back-link">
        <a href="<?php echo DOMAIN; ?>">← Все записи</a>
    </div>

</div>
