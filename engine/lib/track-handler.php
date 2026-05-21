<?php
// Pixel-endpoint: POST { path, referer, postId? } → пишет статистику и возвращает 204
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(204); exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Не считаем админов
$isAdmin = false;
if (session_status() === PHP_SESSION_NONE) @session_start();
if (!empty($_SESSION['user'])) $isAdmin = true;

if (!$isAdmin) {
    Stats::track([
        'path' => $data['path'] ?? '/',
        'referer' => $data['referer'] ?? '',
        'postId' => $data['postId'] ?? '',
        'ua' => $ua,
        'ip' => $ip,
    ]);
}

http_response_code(204);
