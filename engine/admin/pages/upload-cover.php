<?php
header('Content-Type: application/json');
if (empty($_POST['csrf']) || !Auth::checkCsrf($_POST['csrf'])) {
    echo json_encode(['success'=>0,'error'=>'CSRF']); exit;
}
$res = Uploader::image($_FILES['image'] ?? [], 'posts');
echo json_encode($res);
