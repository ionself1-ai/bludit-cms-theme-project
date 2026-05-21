<?php
// Single page / post view
?>
<div class="page-single">

    <article class="single-post">

        <!-- Post header -->
        <header class="single-header">
            <?php if ($page->category()): ?>
            <div class="single-meta-top">
                <a href="<?php echo $page->categoryPermalink(); ?>" class="post-category">
                    <?php echo $page->category(); ?>
                </a>
                <span class="post-date"><?php echo $page->date('d M Y'); ?></span>
            </div>
            <?php else: ?>
            <div class="single-meta-top">
                <span class="post-date"><?php echo $page->date('d M Y'); ?></span>
            </div>
            <?php endif; ?>

            <h1 class="single-title"><?php echo $page->title(); ?></h1>

            <?php if ($page->description()): ?>
            <p class="single-desc"><?php echo $page->description(); ?></p>
            <?php endif; ?>
        </header>

        <!-- Cover image -->
        <?php if ($page->coverImage()): ?>
        <div class="single-cover">
            <img src="<?php echo $page->coverImage(); ?>" alt="<?php echo $page->title(); ?>">
        </div>
        <?php endif; ?>

        <!-- Post content -->
        <div class="single-content">
            <?php echo $page->content(); ?>
        </div>

        <!-- Tags -->
        <?php if ($page->tagsArray()): ?>
        <footer class="single-footer">
            <div class="single-tags">
                <?php foreach ($page->tagsArray() as $tag): ?>
                <a href="<?php echo DOMAIN; ?>tag/<?php echo $tag; ?>" class="post-tag">#<?php echo $tag; ?></a>
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
