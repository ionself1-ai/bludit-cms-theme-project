<?php
// Статические страницы: about, rules, privacy и т.д.
class Pages {
    public static function all() {
        return Storage::read('pages');
    }

    public static function get($slug) {
        foreach (self::all() as $p) {
            if (($p['slug'] ?? '') === $slug) return $p;
        }
        return null;
    }

    public static function save($data) {
        $pages = self::all();
        if (!empty($data['_oldSlug'])) {
            $old = $data['_oldSlug'];
            unset($data['_oldSlug']);
            foreach ($pages as $i => $p) {
                if ($p['slug'] === $old) {
                    $pages[$i] = $data;
                    return Storage::write('pages', $pages);
                }
            }
        }
        $pages[] = $data;
        return Storage::write('pages', $pages);
    }

    public static function delete($slug) {
        $pages = array_values(array_filter(self::all(), fn($p) => $p['slug'] !== $slug));
        return Storage::write('pages', $pages);
    }
}
