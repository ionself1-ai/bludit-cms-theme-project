<?php
// Статистика посещений: храним по дням в data/stats/YYYY-MM-DD.json
// Уникальность считаем по хешу IP+UA (без хранения личных данных)
class Stats {
    private static function dir() {
        $d = DATA_PATH . '/stats';
        if (!is_dir($d)) @mkdir($d, 0775, true);
        return $d;
    }

    private static function dayFile($date) {
        return self::dir() . '/' . $date . '.json';
    }

    public static function readDay($date) {
        $f = self::dayFile($date);
        if (!file_exists($f)) {
            return [
                'date' => $date,
                'views' => 0,
                'visitors' => [],
                'paths' => [],
                'posts' => [],
                'referers' => [],
                'devices' => ['desktop'=>0, 'mobile'=>0, 'tablet'=>0, 'bot'=>0],
                'hours' => array_fill(0, 24, 0),
            ];
        }
        $d = json_decode(file_get_contents($f), true);
        return is_array($d) ? $d : [];
    }

    private static function writeDay($date, $data) {
        $f = self::dayFile($date);
        return file_put_contents($f, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
    }

    // Записывает посещение. $payload = ['path','referer','ua','ip','postId','isBot']
    public static function track($payload) {
        $date = date('Y-m-d');
        $hour = (int)date('G');
        $day = self::readDay($date);

        $ip = $payload['ip'] ?? '';
        $ua = $payload['ua'] ?? '';
        $isBot = !empty($payload['isBot']) || self::detectBot($ua);

        // Уникальный хеш посетителя на день (соль = дата, чтобы каждый день уникальные считались заново)
        $visitorHash = substr(md5($date . '|' . $ip . '|' . $ua), 0, 16);

        $day['views'] = ($day['views'] ?? 0) + 1;
        if (!isset($day['visitors'][$visitorHash])) {
            $day['visitors'][$visitorHash] = 1;
        } else {
            $day['visitors'][$visitorHash]++;
        }

        // Путь
        $path = $payload['path'] ?? '/';
        $path = mb_substr($path, 0, 200);
        $day['paths'][$path] = ($day['paths'][$path] ?? 0) + 1;

        // Пост (если указан)
        if (!empty($payload['postId'])) {
            $pid = $payload['postId'];
            $day['posts'][$pid] = ($day['posts'][$pid] ?? 0) + 1;
        }

        // Реферер (только домен)
        $ref = $payload['referer'] ?? '';
        if ($ref) {
            $host = parse_url($ref, PHP_URL_HOST);
            if ($host) {
                $host = preg_replace('/^www\./', '', $host);
                $selfHost = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? '');
                if ($host !== $selfHost) {
                    $day['referers'][$host] = ($day['referers'][$host] ?? 0) + 1;
                }
            }
        } else {
            $day['referers']['(прямой заход)'] = ($day['referers']['(прямой заход)'] ?? 0) + 1;
        }

        // Устройство
        $device = self::detectDevice($ua, $isBot);
        $day['devices'][$device] = ($day['devices'][$device] ?? 0) + 1;

        // Час
        $day['hours'][$hour] = ($day['hours'][$hour] ?? 0) + 1;

        self::writeDay($date, $day);
        return true;
    }

    public static function detectBot($ua) {
        if (!$ua) return true;
        return (bool)preg_match('/bot|crawl|spider|slurp|yandex|google|bing|duckduck|facebook|telegram|whatsapp|preview|fetch|monitor|uptime/i', $ua);
    }

    public static function detectDevice($ua, $isBot = false) {
        if ($isBot) return 'bot';
        if (preg_match('/iPad|Tablet|PlayBook/i', $ua)) return 'tablet';
        if (preg_match('/Mobile|iPhone|Android.*Mobile|Phone|Opera Mini/i', $ua)) return 'mobile';
        return 'desktop';
    }

    // === АГРЕГАЦИЯ ===

    // Сводка за период (последние N дней)
    public static function summary($days = 30) {
        $today = date('Y-m-d');
        $totalViews = 0;
        $totalUnique = 0;
        $byDay = [];
        $paths = [];
        $posts = [];
        $referers = [];
        $devices = ['desktop'=>0, 'mobile'=>0, 'tablet'=>0, 'bot'=>0];
        $hours = array_fill(0, 24, 0);

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $d = self::readDay($date);
            $u = count($d['visitors'] ?? []);
            $v = (int)($d['views'] ?? 0);
            $totalViews += $v;
            $totalUnique += $u;
            $byDay[] = ['date' => $date, 'views' => $v, 'unique' => $u];

            foreach ($d['paths'] ?? [] as $p => $c) $paths[$p] = ($paths[$p] ?? 0) + $c;
            foreach ($d['posts'] ?? [] as $p => $c) $posts[$p] = ($posts[$p] ?? 0) + $c;
            foreach ($d['referers'] ?? [] as $r => $c) $referers[$r] = ($referers[$r] ?? 0) + $c;
            foreach ($d['devices'] ?? [] as $k => $c) $devices[$k] = ($devices[$k] ?? 0) + $c;
            foreach ($d['hours'] ?? [] as $h => $c) $hours[$h] = ($hours[$h] ?? 0) + $c;
        }

        arsort($paths); arsort($posts); arsort($referers);

        return [
            'period_days' => $days,
            'total_views' => $totalViews,
            'total_unique' => $totalUnique,
            'by_day' => $byDay,
            'top_paths' => array_slice($paths, 0, 10, true),
            'top_posts' => array_slice($posts, 0, 10, true),
            'top_referers' => array_slice($referers, 0, 10, true),
            'devices' => $devices,
            'hours' => $hours,
        ];
    }

    public static function today() {
        return self::readDay(date('Y-m-d'));
    }

    public static function uniqueToday() {
        return count(self::today()['visitors'] ?? []);
    }

    public static function viewsToday() {
        return (int)(self::today()['views'] ?? 0);
    }

    // Очистка старых файлов (старше N дней)
    public static function cleanup($keepDays = 365) {
        $cutoff = strtotime("-$keepDays days");
        $files = glob(self::dir() . '/*.json') ?: [];
        $deleted = 0;
        foreach ($files as $f) {
            $name = basename($f, '.json');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $name) && strtotime($name) < $cutoff) {
                @unlink($f); $deleted++;
            }
        }
        return $deleted;
    }
}
