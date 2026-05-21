<?php
header('Content-Type: application/rss+xml; charset=utf-8');
$site = Settings::all();
$posts = array_slice(Posts::all(true), 0, 20);
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
<channel>
    <title><?= htmlspecialchars($site['site_title']) ?></title>
    <link><?= BASE_URL ?></link>
    <description><?= htmlspecialchars($site['site_description']) ?></description>
    <language>ru</language>
    <?php foreach ($posts as $p): ?>
    <item>
        <title><?= htmlspecialchars($p['title']) ?></title>
        <link><?= BASE_URL ?>?route=post/<?= urlencode($p['slug']) ?></link>
        <guid><?= BASE_URL ?>?route=post/<?= urlencode($p['slug']) ?></guid>
        <pubDate><?= date(DATE_RSS, strtotime($p['date'])) ?></pubDate>
        <description><![CDATA[<?= Posts::excerpt($p, 300) ?>]]></description>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
