<?php
// Простое JSON-хранилище (как в Bludit)
class Storage {
    public static function read($file, $default = []) {
        $path = DATA_PATH . '/' . $file . '.json';
        if (!file_exists($path)) return $default;
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        return is_array($data) ? $data : $default;
    }

    public static function write($file, $data) {
        $path = DATA_PATH . '/' . $file . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($path, $json, LOCK_EX) !== false;
    }

    public static function uuid() {
        return bin2hex(random_bytes(8));
    }

    public static function slug($text) {
        $text = mb_strtolower(trim($text));
        $translit = [
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
            'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
            'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c',
            'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
        ];
        $text = strtr($text, $translit);
        $text = preg_replace('/[^a-z0-9\-]+/u', '-', $text);
        $text = trim($text, '-');
        return $text ?: 'post';
    }
}
