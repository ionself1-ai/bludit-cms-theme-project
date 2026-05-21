<?php
// Работа со статьями
class Posts {
    public static function all($publishedOnly = true) {
        $posts = Storage::read('posts');
        if ($publishedOnly) {
            $posts = array_filter($posts, fn($p) => !empty($p['published']));
        }
        // Сортировка: sticky сверху, потом по дате (новые сверху)
        usort($posts, function($a, $b) {
            $sa = !empty($a['sticky']) ? 1 : 0;
            $sb = !empty($b['sticky']) ? 1 : 0;
            if ($sa !== $sb) return $sb - $sa;
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });
        return array_values($posts);
    }

    public static function byCategory($categoryKey) {
        return array_values(array_filter(self::all(true), fn($p) => ($p['category'] ?? '') === $categoryKey));
    }

    public static function byTag($tag) {
        $tag = mb_strtolower($tag);
        return array_values(array_filter(self::all(true), function($p) use ($tag) {
            $tags = array_map('mb_strtolower', $p['tags'] ?? []);
            return in_array($tag, $tags, true);
        }));
    }

    public static function allTags() {
        $all = [];
        foreach (self::all(true) as $p) {
            foreach ($p['tags'] ?? [] as $t) {
                $t = trim($t);
                if ($t === '') continue;
                $key = mb_strtolower($t);
                $all[$key] = ($all[$key] ?? 0) + 1;
            }
        }
        arsort($all);
        return $all;
    }

    public static function related($post, $limit = 3) {
        $tags = array_map('mb_strtolower', $post['tags'] ?? []);
        if (empty($tags)) return [];
        $scored = [];
        foreach (self::all(true) as $p) {
            if (($p['id'] ?? '') === ($post['id'] ?? '')) continue;
            $pTags = array_map('mb_strtolower', $p['tags'] ?? []);
            $common = count(array_intersect($tags, $pTags));
            if ($common > 0) $scored[] = ['post' => $p, 'score' => $common];
        }
        usort($scored, fn($a, $b) => $b['score'] - $a['score']);
        return array_map(fn($x) => $x['post'], array_slice($scored, 0, $limit));
    }

    // Рендер оверлея заголовка/категории поверх обложки в карточке
    public static function coverOverlayHtml($post) {
        if (empty($post['title_on_cover']) || empty($post['cover'])) return '';
        $type = $post['cover_overlay_type'] ?? 'title';
        $cat = Categories::get($post['category'] ?? '');
        $html = '<span class="post-cover-text">';
        if (($type === 'category' || $type === 'both') && $cat) {
            $html .= '<span class="post-cover-category">' . htmlspecialchars($cat['name']) . '</span>';
        }
        if ($type === 'title' || $type === 'both') {
            $html .= '<span class="post-cover-title">' . htmlspecialchars($post['title'] ?? '') . '</span>';
        }
        $html .= '</span>';
        return $html;
    }

    public static function readingTime($post) {
        $words = str_word_count(self::plainText($post['content'] ?? []), 0, "абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ");
        $min = max(1, (int)round($words / 200));
        return $min;
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
        $wasPublished = false;
        $alreadyNotified = false;
        if (!empty($data['id'])) {
            foreach ($posts as $i => $p) {
                if ($p['id'] === $data['id']) {
                    $wasPublished = !empty($p['published']);
                    $alreadyNotified = !empty($p['notified']);
                    $posts[$i] = array_merge($p, $data);
                    Storage::write('posts', $posts);
                    self::maybeNotify($posts[$i], $wasPublished, $alreadyNotified);
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
        self::maybeNotify($data, false, false);
        return $data['id'];
    }

    // Отправляет уведомления подписчикам при первой публикации
    private static function maybeNotify($post, $wasPublished, $alreadyNotified) {
        if (empty($post['published']) || $alreadyNotified) return;
        if (!class_exists('Subscribers') || !class_exists('Mailer')) return;
        $subs = Subscribers::all(true);
        if (empty($subs)) {
            self::markNotified($post['id']);
            return;
        }
        $site = Settings::all();
        $brand = htmlspecialchars($site['site_title'] ?? 'Blog');
        $postUrl = rtrim(BASE_URL, '/') . '/?route=post/' . urlencode($post['slug']);
        $title = htmlspecialchars($post['title'] ?? '');
        $desc  = htmlspecialchars($post['description'] ?? '');
        $cover = !empty($post['cover']) ? htmlspecialchars($post['cover']) : '';

        $coverHtml = $cover ? '<img src="' . $cover . '" alt="" style="width:100%;max-height:280px;object-fit:cover;border-radius:8px;margin-bottom:16px;">' : '';
        $descHtml = $desc ? '<p style="color:#555;margin:0 0 16px;">' . $desc . '</p>' : '';

        foreach ($subs as $sub) {
            $unsubUrl = rtrim(BASE_URL, '/') . '/?route=subscribe&action=unsub&token=' . urlencode($sub['unsub_token']);
            $body = $coverHtml
                  . '<h2 style="margin:0 0 12px;font-size:20px;line-height:1.3;">' . $title . '</h2>'
                  . $descHtml
                  . '<p style="margin:24px 0;"><a href="' . $postUrl . '" style="display:inline-block;background:#3b82f6;color:#fff;padding:12px 22px;border-radius:8px;font-weight:600;text-decoration:none;">Читать статью</a></p>';
            $footer = 'Вы получили это письмо, потому что подписаны на новые статьи ' . $brand . '.<br>'
                    . '<a href="' . $unsubUrl . '" style="color:#888;">Отписаться</a>';
            @Mailer::send($sub['email'], 'Новая статья: ' . $post['title'], Mailer::template($post['title'], $body, $footer));
        }
        self::markNotified($post['id']);
    }

    private static function markNotified($id) {
        $posts = Storage::read('posts');
        foreach ($posts as &$p) {
            if (($p['id'] ?? '') === $id) {
                $p['notified'] = true;
                $p['notified_at'] = date('c');
                break;
            }
        }
        Storage::write('posts', $posts);
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
                    $html .= self::renderList($d['items'] ?? [], $tag);
                    break;
                case 'checklist':
                    $html .= '<ul class="ejs-checklist">';
                    foreach ($d['items'] ?? [] as $it) {
                        $checked = !empty($it['checked']) ? ' checked' : '';
                        $text = $it['text'] ?? '';
                        $html .= '<li class="ejs-check-item' . ($checked ? ' is-checked' : '') . '"><input type="checkbox" disabled' . $checked . '> <span>' . $text . '</span></li>';
                    }
                    $html .= '</ul>';
                    break;
                case 'quote':
                    $html .= '<blockquote><p>' . ($d['text'] ?? '') . '</p>';
                    if (!empty($d['caption'])) $html .= '<cite>' . $d['caption'] . '</cite>';
                    $html .= '</blockquote>';
                    break;
                case 'warning':
                    $html .= '<div class="ejs-warning">';
                    if (!empty($d['title'])) $html .= '<div class="ejs-warning-title">⚠ ' . htmlspecialchars($d['title']) . '</div>';
                    if (!empty($d['message'])) $html .= '<div class="ejs-warning-message">' . $d['message'] . '</div>';
                    $html .= '</div>';
                    break;
                case 'image':
                    $url = $d['file']['url'] ?? ($d['url'] ?? '');
                    if ($url) {
                        $classes = ['ejs-image'];
                        if (!empty($d['withBorder'])) $classes[] = 'with-border';
                        if (!empty($d['stretched'])) $classes[] = 'stretched';
                        if (!empty($d['withBackground'])) $classes[] = 'with-background';
                        $html .= '<figure class="' . implode(' ', $classes) . '"><img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($d['caption'] ?? '') . '">';
                        if (!empty($d['caption'])) $html .= '<figcaption>' . $d['caption'] . '</figcaption>';
                        $html .= '</figure>';
                    }
                    break;
                case 'code':
                    $html .= '<pre><code>' . htmlspecialchars($d['code'] ?? '') . '</code></pre>';
                    break;
                case 'raw':
                    $html .= $d['html'] ?? '';
                    break;
                case 'delimiter':
                    $html .= '<hr>';
                    break;
                case 'table':
                    $rows = $d['content'] ?? [];
                    $withHeads = !empty($d['withHeadings']);
                    $html .= '<div class="ejs-table-wrap"><table class="ejs-table">';
                    foreach ($rows as $idx => $row) {
                        $tag = ($withHeads && $idx === 0) ? 'th' : 'td';
                        $html .= '<tr>';
                        foreach ($row as $cell) $html .= "<{$tag}>" . $cell . "</{$tag}>";
                        $html .= '</tr>';
                    }
                    $html .= '</table></div>';
                    break;
                case 'embed':
                    if (!empty($d['embed'])) {
                        $html .= '<figure class="embed"><div class="embed-frame"><iframe src="' . htmlspecialchars($d['embed']) . '" frameborder="0" allowfullscreen></iframe></div>';
                        if (!empty($d['caption'])) $html .= '<figcaption>' . $d['caption'] . '</figcaption>';
                        $html .= '</figure>';
                    }
                    break;
                case 'linkTool':
                    $meta = $d['meta'] ?? [];
                    $link = $d['link'] ?? '';
                    if ($link) {
                        $html .= '<a class="ejs-link" href="' . htmlspecialchars($link) . '" target="_blank" rel="noopener">';
                        if (!empty($meta['image']['url'])) $html .= '<img src="' . htmlspecialchars($meta['image']['url']) . '">';
                        $html .= '<div class="ejs-link-body">';
                        if (!empty($meta['title'])) $html .= '<div class="ejs-link-title">' . htmlspecialchars($meta['title']) . '</div>';
                        if (!empty($meta['description'])) $html .= '<div class="ejs-link-desc">' . htmlspecialchars($meta['description']) . '</div>';
                        $html .= '<div class="ejs-link-url">' . htmlspecialchars($link) . '</div>';
                        $html .= '</div></a>';
                    }
                    break;
                default:
                    if (!empty($d['text'])) $html .= '<p>' . $d['text'] . '</p>';
            }
        }
        return $html;
    }

    private static function renderList($items, $tag) {
        $html = "<{$tag}>";
        foreach ($items as $li) {
            if (is_array($li)) {
                $text = $li['content'] ?? ($li['text'] ?? '');
                $html .= '<li>' . $text;
                if (!empty($li['items'])) $html .= self::renderList($li['items'], $tag);
                $html .= '</li>';
            } else {
                $html .= '<li>' . $li . '</li>';
            }
        }
        return $html . "</{$tag}>";
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