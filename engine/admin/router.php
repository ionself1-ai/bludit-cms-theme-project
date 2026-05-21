<?php
// Роутер админки
$action = $parts[1] ?? 'dashboard';

// Открытые роуты
$open = ['login'];

if (!in_array($action, $open)) {
    Auth::require();
}

$file = __DIR__ . '/pages/' . preg_replace('/[^a-z0-9_-]/i', '', $action) . '.php';
if (!file_exists($file)) {
    $file = __DIR__ . '/pages/dashboard.php';
}
require $file;
