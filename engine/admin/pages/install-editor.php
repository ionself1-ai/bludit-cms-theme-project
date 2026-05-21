<?php
// Загружает Editor.js и плагины локально в /assets/editorjs/
header('Content-Type: application/json');

$dir = ROOT_PATH . '/assets/editorjs';
if (!is_dir($dir)) @mkdir($dir, 0755, true);

$libs = [
    'editorjs.js'      => 'https://cdn.jsdelivr.net/npm/@editorjs/editorjs@2.30.7/dist/editorjs.umd.min.js',
    'header.js'        => 'https://cdn.jsdelivr.net/npm/@editorjs/header@2.8.8/dist/header.umd.min.js',
    'list.js'          => 'https://cdn.jsdelivr.net/npm/@editorjs/list@1.10.0/dist/list.umd.min.js',
    'checklist.js'     => 'https://cdn.jsdelivr.net/npm/@editorjs/checklist@1.6.0/dist/checklist.umd.min.js',
    'quote.js'         => 'https://cdn.jsdelivr.net/npm/@editorjs/quote@2.7.3/dist/quote.umd.min.js',
    'warning.js'       => 'https://cdn.jsdelivr.net/npm/@editorjs/warning@1.4.1/dist/warning.umd.min.js',
    'code.js'          => 'https://cdn.jsdelivr.net/npm/@editorjs/code@2.9.3/dist/code.umd.min.js',
    'delimiter.js'     => 'https://cdn.jsdelivr.net/npm/@editorjs/delimiter@1.4.2/dist/delimiter.umd.min.js',
    'image.js'         => 'https://cdn.jsdelivr.net/npm/@editorjs/image@2.10.3/dist/image.umd.min.js',
    'embed.js'         => 'https://cdn.jsdelivr.net/npm/@editorjs/embed@2.7.6/dist/embed.umd.min.js',
    'table.js'         => 'https://cdn.jsdelivr.net/npm/@editorjs/table@2.4.5/dist/table.umd.min.js',
    'marker.js'        => 'https://cdn.jsdelivr.net/npm/@editorjs/marker@1.4.0/dist/marker.umd.min.js',
    'inline-code.js'   => 'https://cdn.jsdelivr.net/npm/@editorjs/inline-code@1.5.2/dist/inline-code.umd.min.js',
    'underline.js'     => 'https://cdn.jsdelivr.net/npm/@editorjs/underline@1.2.1/dist/underline.umd.min.js',
    'raw.js'           => 'https://cdn.jsdelivr.net/npm/@editorjs/raw@2.5.1/dist/raw.umd.min.js',
    'link.js'          => 'https://cdn.jsdelivr.net/npm/@editorjs/link@2.6.2/dist/link.umd.min.js',
];

$results = [];
foreach ($libs as $fname => $url) {
    $dest = $dir . '/' . $fname;
    if (file_exists($dest) && filesize($dest) > 1000) {
        $results[$fname] = 'cached';
        continue;
    }
    $ctx = stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'Mozilla/5.0']]);
    $data = @file_get_contents($url, false, $ctx);
    if ($data && strlen($data) > 1000) {
        file_put_contents($dest, $data);
        $results[$fname] = 'downloaded';
    } else {
        $results[$fname] = 'failed';
    }
}

echo json_encode(['ok' => true, 'files' => $results, 'path' => '/assets/editorjs/']);
