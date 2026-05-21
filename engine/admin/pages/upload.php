<?php
// Editor.js image endpoint
header('Content-Type: application/json');
$result = Uploader::image($_FILES['image'] ?? [], 'posts');
echo json_encode($result);
