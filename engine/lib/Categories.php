<?php
class Categories {
    public static function all() {
        return Storage::read('categories');
    }

    public static function get($key) {
        foreach (self::all() as $c) {
            if (($c['key'] ?? '') === $key) return $c;
        }
        return null;
    }

    public static function save($data) {
        $cats = self::all();
        if (!empty($data['_oldKey'])) {
            $oldKey = $data['_oldKey'];
            unset($data['_oldKey']);
            foreach ($cats as $i => $c) {
                if ($c['key'] === $oldKey) {
                    $cats[$i] = $data;
                    Storage::write('categories', $cats);
                    // обновить ключ во всех статьях, если изменился
                    if ($oldKey !== $data['key']) {
                        $posts = Storage::read('posts');
                        foreach ($posts as &$p) {
                            if (($p['category'] ?? '') === $oldKey) $p['category'] = $data['key'];
                        }
                        Storage::write('posts', $posts);
                    }
                    return true;
                }
            }
        }
        $cats[] = $data;
        return Storage::write('categories', $cats);
    }

    public static function delete($key) {
        $cats = array_values(array_filter(self::all(), fn($c) => $c['key'] !== $key));
        return Storage::write('categories', $cats);
    }
}
