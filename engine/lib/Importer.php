<?php
// Импорт постов: Markdown и WordPress XML → Editor.js JSON
class Importer {
    // === MARKDOWN ===
    // Принимает текст MD + опц. front-matter (--- title/date/... ---)
    public static function markdownToPost($text, $filename = '') {
        $meta = [];
        // Front-matter в формате YAML-light
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $text, $m)) {
            foreach (preg_split('/\n/', $m[1]) as $line) {
                if (preg_match('/^([a-zA-Z_]+):\s*(.+)$/', $line, $mm)) {
                    $meta[strtolower(trim($mm[1]))] = trim($mm[2], " \"'");
                }
            }
            $text = $m[2];
        }
        $title = $meta['title'] ?? '';
        if (!$title) {
            // первый # заголовок
            if (preg_match('/^#\s+(.+)$/m', $text, $mm)) {
                $title = trim($mm[1]);
                $text = preg_replace('/^#\s+.+$/m', '', $text, 1);
            } else {
                $title = $filename ? pathinfo($filename, PATHINFO_FILENAME) : 'Без названия';
            }
        }
        $tags = [];
        if (!empty($meta['tags'])) {
            $tags = array_map('trim', explode(',', $meta['tags']));
        }
        $blocks = self::mdToBlocks($text);
        return [
            'title' => $title,
            'description' => $meta['description'] ?? '',
            'cover' => $meta['cover'] ?? ($meta['image'] ?? ''),
            'category' => $meta['category'] ?? '',
            'tags' => $tags,
            'date' => !empty($meta['date']) ? date('Y-m-d H:i:s', strtotime($meta['date'])) : date('Y-m-d H:i:s'),
            'published' => true,
            'content' => ['blocks' => $blocks],
        ];
    }

    private static function mdToBlocks($text) {
        $blocks = [];
        $lines = preg_split("/\r?\n/", $text);
        $i = 0; $n = count($lines);
        while ($i < $n) {
            $line = $lines[$i];
            $trim = trim($line);
            if ($trim === '') { $i++; continue; }
            // Заголовки
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trim, $m)) {
                $blocks[] = ['type'=>'header', 'data'=>['text'=>self::inlineMd($m[2]), 'level'=>strlen($m[1])]];
                $i++; continue;
            }
            // Цитата
            if (preg_match('/^>\s?(.*)$/', $trim, $m)) {
                $buf = [$m[1]];
                $i++;
                while ($i < $n && preg_match('/^>\s?(.*)$/', trim($lines[$i]), $mm)) { $buf[] = $mm[1]; $i++; }
                $blocks[] = ['type'=>'quote', 'data'=>['text'=>self::inlineMd(implode("\n", $buf)), 'caption'=>'', 'alignment'=>'left']];
                continue;
            }
            // Код-блок
            if (preg_match('/^```/', $trim)) {
                $i++; $buf = [];
                while ($i < $n && !preg_match('/^```/', trim($lines[$i]))) { $buf[] = $lines[$i]; $i++; }
                if ($i < $n) $i++;
                $blocks[] = ['type'=>'code', 'data'=>['code'=>implode("\n", $buf)]];
                continue;
            }
            // Разделитель
            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trim)) {
                $blocks[] = ['type'=>'delimiter', 'data'=>[]];
                $i++; continue;
            }
            // Список ненумерованный
            if (preg_match('/^[-*+]\s+(.+)$/', $trim, $m)) {
                $items = [['content'=>self::inlineMd($m[1]), 'items'=>[]]];
                $i++;
                while ($i < $n && preg_match('/^[-*+]\s+(.+)$/', trim($lines[$i]), $mm)) {
                    $items[] = ['content'=>self::inlineMd($mm[1]), 'items'=>[]];
                    $i++;
                }
                $blocks[] = ['type'=>'list', 'data'=>['style'=>'unordered', 'items'=>$items]];
                continue;
            }
            // Список нумерованный
            if (preg_match('/^\d+\.\s+(.+)$/', $trim, $m)) {
                $items = [['content'=>self::inlineMd($m[1]), 'items'=>[]]];
                $i++;
                while ($i < $n && preg_match('/^\d+\.\s+(.+)$/', trim($lines[$i]), $mm)) {
                    $items[] = ['content'=>self::inlineMd($mm[1]), 'items'=>[]];
                    $i++;
                }
                $blocks[] = ['type'=>'list', 'data'=>['style'=>'ordered', 'items'=>$items]];
                continue;
            }
            // Изображение в строке: ![alt](url)
            if (preg_match('/^!\[([^\]]*)\]\(([^)]+)\)$/', $trim, $m)) {
                $blocks[] = ['type'=>'image', 'data'=>['file'=>['url'=>$m[2]], 'caption'=>$m[1], 'withBorder'=>false, 'stretched'=>false, 'withBackground'=>false]];
                $i++; continue;
            }
            // Параграф (собираем подряд идущие непустые строки)
            $buf = [$line];
            $i++;
            while ($i < $n && trim($lines[$i]) !== '' && !preg_match('/^(#|>|\d+\.|[-*+]\s|```|---|===|\!\[)/', trim($lines[$i]))) {
                $buf[] = $lines[$i]; $i++;
            }
            $blocks[] = ['type'=>'paragraph', 'data'=>['text'=>self::inlineMd(implode(' ', $buf))]];
        }
        return $blocks;
    }

    // Инлайн-разметка → HTML (b/i/code/link)
    private static function inlineMd($s) {
        $s = htmlspecialchars($s, ENT_NOQUOTES, 'UTF-8');
        // [текст](url)
        $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function($m){
            return '<a href="' . htmlspecialchars($m[2]) . '">' . $m[1] . '</a>';
        }, $s);
        // **жирный**
        $s = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $s);
        $s = preg_replace('/__(.+?)__/', '<b>$1</b>', $s);
        // *курсив*
        $s = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<i>$1</i>', $s);
        $s = preg_replace('/(?<!_)_([^_]+)_(?!_)/', '<i>$1</i>', $s);
        // `код`
        $s = preg_replace('/`([^`]+)`/', '<code class="inline-code">$1</code>', $s);
        return $s;
    }

    // === HTML (для WP) → Editor.js blocks (упрощённо) ===
    public static function htmlToBlocks($html) {
        if (!$html) return [];
        // Удалим лишние \r
        $html = str_replace("\r", '', $html);
        // Разбиваем по двойным переводам строк (WP так структурирует автопараграфы)
        $chunks = preg_split("/\n\s*\n/", trim($html));
        $blocks = [];
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') continue;
            // <h1..h6>
            if (preg_match('/^<h([1-6])[^>]*>(.*?)<\/h\1>$/is', $chunk, $m)) {
                $blocks[] = ['type'=>'header', 'data'=>['text'=>trim($m[2]), 'level'=>(int)$m[1]]];
                continue;
            }
            // blockquote
            if (preg_match('/^<blockquote[^>]*>(.*?)<\/blockquote>$/is', $chunk, $m)) {
                $blocks[] = ['type'=>'quote', 'data'=>['text'=>trim(strip_tags($m[1], '<a><b><i><strong><em>')), 'caption'=>'', 'alignment'=>'left']];
                continue;
            }
            // pre/code
            if (preg_match('/^<pre[^>]*>(?:<code[^>]*>)?(.*?)(?:<\/code>)?<\/pre>$/is', $chunk, $m)) {
                $blocks[] = ['type'=>'code', 'data'=>['code'=>html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8')]];
                continue;
            }
            // ul/ol
            if (preg_match('/^<(ul|ol)[^>]*>(.*?)<\/\1>$/is', $chunk, $m)) {
                preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $m[2], $lis);
                $items = array_map(fn($t) => ['content' => trim($t), 'items' => []], $lis[1]);
                $blocks[] = ['type'=>'list', 'data'=>['style'=> ($m[1]==='ol'?'ordered':'unordered'), 'items'=>$items]];
                continue;
            }
            // img одиночное
            if (preg_match('/^<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*\/?>$/i', $chunk, $m)) {
                $blocks[] = ['type'=>'image', 'data'=>['file'=>['url'=>$m[1]], 'caption'=>'']];
                continue;
            }
            // <hr>
            if (preg_match('/^<hr\s*\/?>$/i', $chunk)) {
                $blocks[] = ['type'=>'delimiter', 'data'=>[]];
                continue;
            }
            // По умолчанию — параграф
            // Убираем оборачивающий <p>
            $text = preg_replace('/^<p[^>]*>(.*)<\/p>$/is', '$1', $chunk);
            // Заменяем <br> на пробел
            $text = preg_replace('/<br\s*\/?>/i', ' ', $text);
            $blocks[] = ['type'=>'paragraph', 'data'=>['text'=>trim($text)]];
        }
        return $blocks;
    }

    // === WordPress XML (WXR) ===
    public static function parseWordPressXml($xml) {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$doc) return ['ok'=>false, 'error'=>'XML parse error', 'posts'=>[]];
        $ns = $doc->getNamespaces(true);
        $items = $doc->channel->item ?? [];
        $posts = [];
        foreach ($items as $it) {
            $wp = $it->children($ns['wp'] ?? 'http://wordpress.org/export/1.2/');
            $content = $it->children($ns['content'] ?? 'http://purl.org/rss/1.0/modules/content/');
            $dc = $it->children($ns['dc'] ?? 'http://purl.org/dc/elements/1.1/');

            $type = isset($wp->post_type) ? (string)$wp->post_type : 'post';
            if ($type !== 'post') continue;
            $status = isset($wp->status) ? (string)$wp->status : '';
            if ($status === 'trash' || $status === 'auto-draft' || $status === 'inherit') continue;

            $title = trim((string)$it->title);
            $bodyHtml = (string)($content->encoded ?? '');
            $date = (string)($wp->post_date ?? $it->pubDate ?? 'now');

            $cats = []; $tags = [];
            if (isset($it->category)) {
                foreach ($it->category as $c) {
                    $domain = (string)$c['domain'];
                    $name = trim((string)$c);
                    if ($domain === 'category') $cats[] = $name;
                    elseif ($domain === 'post_tag') $tags[] = $name;
                }
            }

            $blocks = self::htmlToBlocks($bodyHtml);
            // Первая картинка → обложка
            $cover = '';
            foreach ($blocks as $b) {
                if ($b['type'] === 'image' && !empty($b['data']['file']['url'])) { $cover = $b['data']['file']['url']; break; }
            }
            $posts[] = [
                'title' => $title ?: 'Без названия',
                'description' => '',
                'cover' => $cover,
                'category' => !empty($cats) ? Storage::slug($cats[0]) : '',
                'category_name' => $cats[0] ?? '',
                'tags' => $tags,
                'date' => $date ? date('Y-m-d H:i:s', strtotime($date)) : date('Y-m-d H:i:s'),
                'published' => ($status === 'publish'),
                'content' => ['blocks' => $blocks],
            ];
        }
        return ['ok'=>true, 'posts'=>$posts];
    }
}
