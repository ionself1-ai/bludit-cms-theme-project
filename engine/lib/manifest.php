<?php
// PWA manifest.json (динамический — берёт данные из настроек)
header('Content-Type: application/manifest+json; charset=UTF-8');

$site = Settings::all();
$logo = $site['logo'] ?? '';
$icon192 = $logo ?: (BASE_URL . 'theme/icon-192.png');
$icon512 = $logo ?: (BASE_URL . 'theme/icon-512.png');
$themeColor = $site['theme_color'] ?? '#3b82f6';

echo json_encode([
    'name' => $site['site_title'] ?? 'Blog',
    'short_name' => mb_substr($site['site_title'] ?? 'Blog', 0, 12),
    'description' => $site['site_description'] ?? '',
    'start_url' => BASE_URL,
    'scope' => BASE_URL,
    'display' => 'standalone',
    'orientation' => 'portrait-primary',
    'background_color' => '#ffffff',
    'theme_color' => $themeColor,
    'lang' => 'ru',
    'icons' => [
        ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
    ],
    'categories' => ['news', 'blog', 'lifestyle'],
    'shortcuts' => [
        [
            'name' => 'Поиск',
            'url' => BASE_URL . '?route=search',
        ],
        [
            'name' => 'Все теги',
            'url' => BASE_URL . '?route=tags',
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
