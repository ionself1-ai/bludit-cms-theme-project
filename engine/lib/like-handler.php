<?php
// Лайки на постах. /?route=like
header('Content-Type: application/json');

$action = $parts[1] ?? ($_GET['action'] ?? 'toggle');
$postId = trim($_POST['post_id'] ?? $_GET['post_id'] ?? '');

// GET — просто счётчик
if ($action === 'count' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!$postId) { echo json_encode(['ok'=>false,'error'=>'no_id']); exit; }
    $likes = Storage::read('likes');
    $count = isset($likes[$postId]) ? (int)($likes[$postId]['count'] ?? 0) : 0;
    $liked = !empty($_COOKIE['liked_' . $postId]);
    echo json_encode(['ok'=>true,'count'=>$count,'liked'=>$liked]);
    exit;
}

// POST — toggle
if (!$postId) { echo json_encode(['ok'=>false,'error'=>'no_id']); exit; }
$post = Posts::byId($postId);
if (!$post) { echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }

$likes = Storage::read('likes');
if (!isset($likes[$postId])) $likes[$postId] = ['count'=>0, 'updated_at'=>date('c')];

$cookieName = 'liked_' . $postId;
$alreadyLiked = !empty($_COOKIE[$cookieName]);

if ($alreadyLiked) {
    // Снимаем лайк
    $likes[$postId]['count'] = max(0, (int)$likes[$postId]['count'] - 1);
    setcookie($cookieName, '', time() - 3600, '/');
    $liked = false;
} else {
    $likes[$postId]['count'] = (int)$likes[$postId]['count'] + 1;
    // 1 год
    setcookie($cookieName, '1', time() + 365 * 86400, '/');
    $liked = true;
}
$likes[$postId]['updated_at'] = date('c');
Storage::write('likes', $likes);

echo json_encode(['ok'=>true,'count'=>$likes[$postId]['count'],'liked'=>$liked]);
