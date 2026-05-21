<?php
// Подписчики на новые статьи (email-рассылка)
class Subscribers {
    public static function all($confirmedOnly = false) {
        $list = Storage::read('subscribers');
        if ($confirmedOnly) {
            $list = array_values(array_filter($list, fn($s) => !empty($s['confirmed']) && empty($s['unsubscribed'])));
        }
        return $list;
    }

    public static function findByEmail($email) {
        $email = mb_strtolower(trim($email));
        foreach (self::all() as $s) {
            if (mb_strtolower($s['email']) === $email) return $s;
        }
        return null;
    }

    public static function findByToken($token, $type = 'confirm') {
        $field = $type === 'unsub' ? 'unsub_token' : 'confirm_token';
        foreach (self::all() as $s) {
            if (!empty($s[$field]) && hash_equals($s[$field], $token)) return $s;
        }
        return null;
    }

    // Подписать (новый или re-subscribe)
    public static function subscribe($email) {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'Некорректный email'];
        }
        $list = self::all();
        $existing = self::findByEmail($email);
        if ($existing) {
            // Уже подтверждён и активен
            if (!empty($existing['confirmed']) && empty($existing['unsubscribed'])) {
                return ['ok' => true, 'already' => true, 'subscriber' => $existing];
            }
            // Восстанавливаем
            foreach ($list as &$s) {
                if (mb_strtolower($s['email']) === $email) {
                    $s['unsubscribed'] = false;
                    $s['confirm_token'] = bin2hex(random_bytes(16));
                    $s['confirm_sent_at'] = date('c');
                    $resub = $s;
                }
            }
            Storage::write('subscribers', $list);
            return ['ok' => true, 'resub' => true, 'subscriber' => $resub];
        }
        $sub = [
            'id' => Storage::uuid(),
            'email' => $email,
            'confirmed' => false,
            'unsubscribed' => false,
            'confirm_token' => bin2hex(random_bytes(16)),
            'unsub_token' => bin2hex(random_bytes(16)),
            'created_at' => date('c'),
            'confirm_sent_at' => date('c'),
        ];
        $list[] = $sub;
        Storage::write('subscribers', $list);
        return ['ok' => true, 'created' => true, 'subscriber' => $sub];
    }

    public static function confirm($token) {
        $list = self::all();
        foreach ($list as &$s) {
            if (!empty($s['confirm_token']) && hash_equals($s['confirm_token'], $token)) {
                $s['confirmed'] = true;
                $s['confirmed_at'] = date('c');
                $s['unsubscribed'] = false;
                Storage::write('subscribers', $list);
                return $s;
            }
        }
        return null;
    }

    public static function unsubscribe($token) {
        $list = self::all();
        foreach ($list as &$s) {
            if (!empty($s['unsub_token']) && hash_equals($s['unsub_token'], $token)) {
                $s['unsubscribed'] = true;
                $s['unsubscribed_at'] = date('c');
                Storage::write('subscribers', $list);
                return $s;
            }
        }
        return null;
    }

    public static function delete($id) {
        $list = self::all();
        $list = array_values(array_filter($list, fn($s) => ($s['id'] ?? '') !== $id));
        return Storage::write('subscribers', $list);
    }

    public static function stats() {
        $all = self::all();
        $confirmed = 0; $pending = 0; $unsubs = 0;
        foreach ($all as $s) {
            if (!empty($s['unsubscribed'])) $unsubs++;
            elseif (!empty($s['confirmed'])) $confirmed++;
            else $pending++;
        }
        return ['total' => count($all), 'confirmed' => $confirmed, 'pending' => $pending, 'unsubscribed' => $unsubs];
    }
}
