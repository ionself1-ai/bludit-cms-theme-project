<?php
// Обработчик подписки/подтверждения/отписки
// /?route=subscribe&action=...

$action = $parts[1] ?? ($_GET['action'] ?? 'new');
$site = Settings::all();

function renderInfoPage($title, $message, $isError = false) {
    global $site;
    $pageTitle = $title;
    // Inline-страница без шаблонов сайта
    ?>
    <!DOCTYPE html><html lang="ru"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= htmlspecialchars($title) ?> — <?= htmlspecialchars($site['site_title'] ?? '') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>theme/style.css">
    </head><body>
    <main style="max-width:520px;margin:6vh auto;padding:2rem;background:var(--card);border:1px solid var(--border);border-radius:12px;text-align:center;">
        <h1 style="font-size:1.5rem;margin-bottom:1rem;"><?= htmlspecialchars($title) ?></h1>
        <p style="color:var(--muted);line-height:1.6;"><?= $message ?></p>
        <p style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>" style="color:var(--accent);">← На главную</a></p>
    </main>
    </body></html>
    <?php
}

if ($action === 'new' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $email = trim($_POST['email'] ?? '');
    if (!$email) { echo json_encode(['ok'=>false,'error'=>'Введите email']); exit; }

    $res = Subscribers::subscribe($email);
    if (!$res['ok']) { echo json_encode($res); exit; }

    $sub = $res['subscriber'];

    if (!empty($res['already'])) {
        echo json_encode(['ok'=>true,'message'=>'Вы уже подписаны']);
        exit;
    }

    // Письмо подтверждения
    $confirmUrl = rtrim(BASE_URL, '/') . '/?route=subscribe&action=confirm&token=' . urlencode($sub['confirm_token']);
    $brand = htmlspecialchars($site['site_title'] ?? 'Blog');
    $body = '<h2 style="margin:0 0 12px;font-size:18px;">Подтвердите подписку</h2>'
          . '<p>Вы подписываетесь на новые статьи блога <b>' . $brand . '</b>.</p>'
          . '<p style="margin:24px 0;"><a href="' . htmlspecialchars($confirmUrl) . '" style="display:inline-block;background:#3b82f6;color:#fff;padding:12px 22px;border-radius:8px;font-weight:600;text-decoration:none;">Подтвердить подписку</a></p>'
          . '<p style="color:#888;font-size:13px;">Если вы не подписывались — просто проигнорируйте это письмо.</p>';
    $footer = 'Это письмо отправлено автоматически.';
    Mailer::send($sub['email'], 'Подтвердите подписку — ' . $brand, Mailer::template('Подтвердите подписку', $body, $footer));

    echo json_encode(['ok'=>true,'message'=>'Проверьте почту — мы отправили письмо для подтверждения']);
    exit;
}

if ($action === 'confirm') {
    $token = $_GET['token'] ?? '';
    $sub = Subscribers::confirm($token);
    if ($sub) {
        renderInfoPage('Подписка подтверждена', 'Спасибо! Теперь вы будете получать уведомления о новых статьях на ' . htmlspecialchars($sub['email']) . '.');
    } else {
        renderInfoPage('Ссылка недействительна', 'Возможно, вы уже подтвердили подписку или ссылка устарела.', true);
    }
    exit;
}

if ($action === 'unsub') {
    $token = $_GET['token'] ?? '';
    $sub = Subscribers::unsubscribe($token);
    if ($sub) {
        renderInfoPage('Вы отписались', 'Мы больше не будем присылать письма на ' . htmlspecialchars($sub['email']) . '. Жаль расставаться!');
    } else {
        renderInfoPage('Ссылка недействительна', 'Ссылка отписки устарела или некорректна.', true);
    }
    exit;
}

http_response_code(400);
echo 'Bad request';
