<?php
// Простой почтовый отправитель: PHP mail() или SMTP (на чистом PHP, без зависимостей)
class Mailer {
    public static function send($to, $subject, $htmlBody, $textBody = null) {
        $settings = Settings::all();
        $fromEmail = $settings['mail_from'] ?? ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $fromName = $settings['mail_from_name'] ?? ($settings['site_title'] ?? 'Blog');
        $textBody = $textBody ?: trim(strip_tags($htmlBody));

        // Готовим письмо
        $boundary = '=_Part_' . bin2hex(random_bytes(8));
        $headers = [
            'From' => self::encodeAddress($fromName, $fromEmail),
            'Reply-To' => $fromEmail,
            'MIME-Version' => '1.0',
            'Content-Type' => 'multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer' => 'EngineMail/1.0',
        ];
        $body = "--{$boundary}\r\n"
              . "Content-Type: text/plain; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: 8bit\r\n\r\n"
              . $textBody . "\r\n\r\n"
              . "--{$boundary}\r\n"
              . "Content-Type: text/html; charset=UTF-8\r\n"
              . "Content-Transfer-Encoding: 8bit\r\n\r\n"
              . $htmlBody . "\r\n\r\n"
              . "--{$boundary}--";

        // SMTP или mail()?
        if (!empty($settings['smtp_host'])) {
            return self::sendSmtp($to, $subject, $body, $headers, $settings);
        }
        $headerStr = '';
        foreach ($headers as $k => $v) $headerStr .= $k . ': ' . $v . "\r\n";
        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return @mail($to, $subjectEncoded, $body, $headerStr);
    }

    private static function encodeAddress($name, $email) {
        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $email . '>';
    }

    private static function sendSmtp($to, $subject, $body, $headers, $s) {
        $host = $s['smtp_host'];
        $port = (int)($s['smtp_port'] ?? 587);
        $user = $s['smtp_user'] ?? '';
        $pass = $s['smtp_pass'] ?? '';
        $secure = $s['smtp_secure'] ?? 'tls'; // tls, ssl, none
        $timeout = 15;
        $host_full = ($secure === 'ssl' ? 'ssl://' : '') . $host;
        $fp = @stream_socket_client($host_full . ':' . $port, $errno, $errstr, $timeout);
        if (!$fp) return false;
        $read = function() use ($fp) {
            $data = '';
            while ($line = fgets($fp, 515)) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $data;
        };
        $cmd = function($c) use ($fp, $read) {
            fwrite($fp, $c . "\r\n");
            return $read();
        };
        $read();
        $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        if ($secure === 'tls') {
            $cmd('STARTTLS');
            @stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        }
        if ($user) {
            $cmd('AUTH LOGIN');
            $cmd(base64_encode($user));
            $cmd(base64_encode($pass));
        }
        // From
        $from = $s['mail_from'] ?? $user;
        $cmd('MAIL FROM:<' . $from . '>');
        $cmd('RCPT TO:<' . $to . '>');
        $cmd('DATA');
        $msg = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=' . "\r\n"
             . 'To: ' . $to . "\r\n";
        foreach ($headers as $k => $v) $msg .= $k . ': ' . $v . "\r\n";
        $msg .= "\r\n" . $body . "\r\n.";
        $resp = $cmd($msg);
        $cmd('QUIT');
        fclose($fp);
        return strpos($resp, '250') !== false;
    }

    // Шаблон письма
    public static function template($title, $content, $footer = '') {
        $site = Settings::all();
        $brand = htmlspecialchars($site['site_title'] ?? 'Blog');
        $accent = '#3b82f6';
        return '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title></head>'
            . '<body style="margin:0;padding:0;background:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;color:#1a1a1a;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f6f6f6;padding:24px 12px;">'
            . '<tr><td align="center">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background:#fff;border-radius:12px;overflow:hidden;">'
            . '<tr><td style="padding:24px 28px 12px;border-bottom:1px solid #eee;">'
            . '<div style="font-size:14px;font-weight:600;color:' . $accent . ';letter-spacing:0.04em;text-transform:uppercase;">' . $brand . '</div>'
            . '</td></tr>'
            . '<tr><td style="padding:24px 28px;font-size:15px;line-height:1.6;">' . $content . '</td></tr>'
            . '<tr><td style="padding:16px 28px 24px;border-top:1px solid #eee;font-size:12px;color:#888;">' . $footer . '</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
}
