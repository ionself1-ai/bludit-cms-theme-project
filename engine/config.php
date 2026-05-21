<?php
// Базовая конфигурация движка
if (!defined('ENGINE')) define('ENGINE', true);

date_default_timezone_set('Europe/Moscow');

define('ROOT_PATH', __DIR__);
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('THEME_PATH', ROOT_PATH . '/theme');

// Базовый URL — автоопределение
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if (!defined('BASE_URL')) define('BASE_URL', $scheme . '://' . $host . $scriptDir . '/');
if (!defined('UPLOADS_URL')) define('UPLOADS_URL', BASE_URL . 'uploads/');

// Создаём базовые папки при первом запуске
foreach ([DATA_PATH, UPLOADS_PATH, UPLOADS_PATH . '/posts', UPLOADS_PATH . '/avatars'] as $p) {
    if (!is_dir($p)) @mkdir($p, 0755, true);
}

session_start();
