<?php
// Список ранее загруженных изображений (для галереи выбора обложки)
header('Content-Type: application/json');

$subdirs = ['posts', 'avatars'];
$exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
$items = [];

foreach ($subdirs as $sub) {
    $dir = UPLOADS_PATH . '/' . $sub;
    if (!is_dir($dir)) continue;
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = $dir . '/' . $f;
        if (!is_file($path)) continue;
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $exts, true)) continue;
        $items[] = [
            'url' => UPLOADS_URL . $sub . '/' . rawurlencode($f),
            'name' => $f,
            'size' => filesize($path),
            'mtime' => filemtime($path),
            'subdir' => $sub,
        ];
    }
}

// Сортировка: новые сверху
usort($items, fn($a, $b) => $b['mtime'] - $a['mtime']);

echo json_encode(['ok' => true, 'items' => $items], JSON_UNESCAPED_UNICODE);
