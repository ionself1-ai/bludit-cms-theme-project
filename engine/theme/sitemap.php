<?php
header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?= BASE_URL ?></loc><changefreq>daily</changefreq><priority>1.0</priority></url>
    <?php foreach (Posts::all(true) as $p): ?>
    <url>
        <loc><?= BASE_URL ?>?route=post/<?= urlencode($p['slug']) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($p['date'])) ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <?php endforeach; ?>
    <?php foreach (Categories::all() as $c): ?>
    <url><loc><?= BASE_URL ?>?route=category/<?= urlencode($c['key']) ?></loc><priority>0.6</priority></url>
    <?php endforeach; ?>
    <?php foreach (Pages::all() as $pg): ?>
    <url><loc><?= BASE_URL ?>?route=page/<?= urlencode($pg['slug']) ?></loc><priority>0.5</priority></url>
    <?php endforeach; ?>
</urlset>
