<?php
// Мгновенный поиск (живые подсказки). /?route=suggest&q=...
header('Content-Type: application/json; charset=UTF-8');

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode(['ok' => true, 'items' => [], 'tags' => [], 'cats' => []]);
    exit;
}

$qLower = mb_strtolower($q);
$results = [];

foreach (Posts::all(true) as $p) {
    $hay = mb_strtolower(
        ($p['title'] ?? '') . ' ' .
        ($p['description'] ?? '') . ' ' .
        implode(' ', $p['tags'] ?? [])
    );
    $pos = mb_strpos($hay, $qLower);
    if ($pos === false) continue;
    // Релевантность: вхождение в title — выше
    $titleHit = mb_stripos($p['title'] ?? '', $q) !== false;
    $score = $titleHit ? 100 - mb_stripos($p['title'], $q) : 50;
    $results[] = [
        'id' => $p['id'],
        'title' => $p['title'],
        'slug' => $p['slug'],
        'cover' => $p['cover'] ?? '',
        'date' => $p['date'] ?? '',
        'category' => $p['category'] ?? '',
        'score' => $score,
    ];
}
usort($results, fn($a, $b) => $b['score'] - $a['score']);
$results = array_slice($results, 0, 6);

// Также ищем по тегам и категориям
$tagMatches = [];
foreach (array_keys(Posts::allTags()) as $t) {
    if (mb_stripos($t, $q) !== false) {
        $tagMatches[] = $t;
        if (count($tagMatches) >= 4) break;
    }
}

$catMatches = [];
foreach (Categories::all() as $c) {
    if (mb_stripos($c['name'], $q) !== false) {
        $catMatches[] = $c;
        if (count($catMatches) >= 3) break;
    }
}

echo json_encode([
    'ok' => true,
    'q' => $q,
    'items' => $results,
    'tags' => $tagMatches,
    'cats' => $catMatches,
], JSON_UNESCAPED_UNICODE);
