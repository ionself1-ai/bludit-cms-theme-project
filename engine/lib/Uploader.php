<?php
class Uploader {
    public static function image($file, $subdir = 'posts') {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => 0, 'error' => 'Файл не загружен'];
        }
        $allowed = ['jpg','jpeg','png','gif','webp','svg'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return ['success' => 0, 'error' => 'Недопустимый формат'];
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => 0, 'error' => 'Файл больше 10 МБ'];
        }
        $dir = UPLOADS_PATH . '/' . $subdir;
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $path)) {
            return ['success' => 0, 'error' => 'Не удалось сохранить'];
        }
        $url = UPLOADS_URL . $subdir . '/' . $name;
        return ['success' => 1, 'file' => ['url' => $url], 'url' => $url];
    }
}
