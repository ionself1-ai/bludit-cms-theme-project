<?php
// Работа со статьями
class Posts {
    public static function all($publishedOnly = true) {
        $posts = Storage::read('posts');
        if ($publishedOnly) {
            $posts = array_filter($posts, fn($p) => !empty($p['published']));
        }
        // Сортировка по дате (новые сверху)
        usort($posts, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));
        return array_values($posts);
    }

    public static function byCategory($categoryKey) {
        return array_values(array_filter(self::all(true), fn($p) => ($p['category'] ?? '') === $categoryKey));
    }

    public static function bySlug($slug) {
        foreach (Storage::read('posts') as $p) {
            if (($p['slug'] ?? '') === $slug) return $p;
        }
        return null;
    }

    public static function byId($id) {
        foreach (Storage::read('posts') as $p) {
            if (($p['id'] ?? '') === $id) return $p;
        }
        return null;
    }

    public static function save($data) {
        $posts = Storage::read('posts');
        if (!empty($data['id'])) {
            foreach ($posts as $i => $p) {
                if ($p['id'] === $data['id']) {
                    $posts[$i] = array_merge($p, $data);
                    Storage::write('posts', $posts);
                    return $data['id'];
                }
            }
        }
        $data['id'] = $data['id'] ?? Storage::uuid();
        $data['date'] = $data['date'] ?? date('Y-m-d H:i:s');
        if (empty($data['slug'])) $data['slug'] = Storage::slug($data['title'] ?? 'post');
        // уникальный slug
        $base = $data['slug']; $i = 1;
        while (self::slugTaken($data['slug'], $data['id'])) { $data['slug'] = $base . '-' . (++$i); }
        $posts[] = $data;
        Storage::write('posts', $posts);
        return $data['id'];
    }

    private static function slugTaken($slug, $excludeId = null) {
        foreach (Storage::read('posts') as $p) {
            if (($p['slug'] ?? '') === $slug && ($p['id'] ?? '') !== $excludeId) return true;
        }
        return false;
    }

    public static function delete($id) {
        $posts = Storage::read('posts');
        $posts = array_values(array_filter($posts, fn($p) => ($p['id'] ?? '') !== $id));
        return Storage::write('posts', $posts);
    }

    public static function search($query) {
        $q = mb_strtolower(trim($query));
        if ($q === '') return [];
        return array_values(array_filter(self::all(true), function($p) use ($q) {
            $hay = mb_strtolower(
                ($p['title'] ?? '') . ' ' .
                ($p['description'] ?? '') . ' ' .
                self::plainText($p['content'] ?? [])
            );
            return mb_strpos($hay, $q) !== false;
        }));
    }

    public static function paginate($items, $page, $perPage = 10) {
        $total = count($items);
        $pages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;
        return [
            'items' => array_slice($items, $offset, $perPage),
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ];
    }

    // Рендер Editor.js JSON в HTML
    public static function renderContent($content) {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) $content = $decoded;
            else return $content;
        }
        if (empty($content['blocks'])) return '';
        $html = '';
        foreach ($content['blocks'] as $block) {
            $type = $block['type'] ?? '';
            $d = $block['data'] ?? [];
            switch ($type) {
                case 'header':
                    $lvl = (int)($d['level'] ?? 2);
                    $html .= "<h{$lvl}>" . ($d['text'] ?? '') . "</h{$lvl}>";
                    break;
                case 'paragraph':
                    $html .= '<p>' . ($d['text'] ?? '') . '</p>';
                    break;
                case 'list':
                    $tag = ($d['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
                    $html .= "<{$tag}>";
                    foreach ($d['items'] ?? [] as $li) $html .= '<li>' . $li . '</li>';
                    $html .= "</{$tag}>";
                    break;
                case 'quote':
                    $html .= '<blockquote><p>' . ($d['text'] ?? '') . '</p>';
                    if (!empty($d['caption'])) $html .= '<cite>' . $d['caption'] . '</cite>';
                    $html .= '</blockquote>';
                    break;
                case 'image':
                    $url = $d['file']['url'] ?? ($d['url'] ?? '');
                    if ($url) $html .= '<figure><img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($d['caption'] ?? '') . '">';
                    if (!empty($d['caption'])) $html .= '<figcaption>' . $d['caption'] . '</figcaption>';
                    if ($url) $html .= '</figure>';
                    break;
                case 'code':
                    $html .= '<pre><code>' . htmlspecialchars($d['code'] ?? '') . '</code></pre>';
                    break;
                case 'delimiter':
                    $html .= '<hr>';
                    break;
                case 'embed':
                    if (!empty($d['embed'])) $html .= '<div class="embed"><iframe src="' . htmlspecialchars($d['embed']) . '" frameborder="0" allowfullscreen></iframe></div>';
                    break;
                default:
                    if (!empty($d['text'])) $html .= '<p>' . $d['text'] . '</p>';
            }
        }
        return $html;
    }

    public static function plainText($content) {
        $html = self::renderContent($content);
        return strip_tags($html);
    }

    public static function excerpt($post, $len = 200) {
        if (!empty($post['description'])) return $post['description'];
        $text = self::plainText($post['content'] ?? []);
        return mb_substr($text, 0, $len) . (mb_strlen($text) > $len ? '…' : '');
    }
}
