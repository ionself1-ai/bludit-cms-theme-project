<?php
class Settings {
    private static $cache = null;

    public static function all() {
        if (self::$cache === null) {
            self::$cache = Storage::read('settings', [
                'site_title' => 'Мой блог',
                'site_description' => 'Личный блог об интересном',
                'posts_per_page' => 9,
            ]);
        }
        return self::$cache;
    }

    public static function get($key, $default = '') {
        $s = self::all();
        return $s[$key] ?? $default;
    }

    public static function save($data) {
        self::$cache = array_merge(self::all(), $data);
        return Storage::write('settings', self::$cache);
    }
}
