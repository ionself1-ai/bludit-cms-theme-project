<?php
// Автосохранение черновика статьи. Не публикует — только сохраняет.
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'POST only']); exit;
}
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload) || empty($payload['csrf']) || !Auth::checkCsrf($payload['csrf'])) {
    echo json_encode(['ok' => false, 'error' => 'CSRF']); exit;
}

$data = [
    'id' => $payload['id'] ?? null,
    'title' => trim($payload['title'] ?? 'Без названия'),
    'slug' => trim($payload['slug'] ?? ''),
    'description' => trim($payload['description'] ?? ''),
    'category' => $payload['category'] ?? '',
    'cover' => $payload['cover'] ?? '',
    'tags' => $payload['tags'] ?? [],
    'sticky' => !empty($payload['sticky']),
    'title_on_cover' => !empty($payload['title_on_cover']),
    'cover_overlay_type' => $payload['cover_overlay_type'] ?? 'title',
    'publish_at' => !empty($payload['publish_at']) ? date('Y-m-d H:i:s', strtotime($payload['publish_at'])) : '',
    'content' => is_array($payload['content']) ? $payload['content'] : ['blocks' => []],
    'published' => !empty($payload['published']),
    'autosavedAt' => date('c'),
];

$id = Posts::save($data);
echo json_encode(['ok' => true, 'id' => $id, 'time' => date('H:i:s')]);