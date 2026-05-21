<?php
// Авторизация админа
class Auth {
    public static function user() {
        $users = Storage::read('users');
        if (empty($users)) return null;
        return $users[0]; // только админ
    }

    public static function isLogged() {
        return !empty($_SESSION['admin_logged']);
    }

    public static function login($username, $password) {
        $user = self::user();
        if (!$user) return false;
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged'] = true;
            session_regenerate_id(true);
            return true;
        }
        return false;
    }

    public static function logout() {
        unset($_SESSION['admin_logged']);
        session_destroy();
    }

    public static function require() {
        if (!self::isLogged()) {
            header('Location: ' . BASE_URL . '?route=admin/login');
            exit;
        }
    }

    public static function csrf() {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf'];
    }

    public static function checkCsrf($token) {
        return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$token);
    }
}
