<?php
// Установка демо-контента: категории и статьи
header('Content-Type: application/json');

// Демо-категории
$demoCats = [
    ['key' => 'tech', 'name' => 'Технологии', 'description' => 'О языках программирования и инструментах'],
    ['key' => 'design', 'name' => 'Дизайн', 'description' => 'UX/UI, типографика, цвета'],
    ['key' => 'life', 'name' => 'Жизнь', 'description' => 'Личные заметки и наблюдения'],
    ['key' => 'guides', 'name' => 'Руководства', 'description' => 'Подробные туториалы'],
];

$existing = Categories::all();
$existingKeys = array_column($existing, 'key');
foreach ($demoCats as $c) {
    if (!in_array($c['key'], $existingKeys)) {
        Categories::save($c);
    }
}

// Демо-статьи
$demoPosts = [
    [
        'title' => 'Добро пожаловать в новый блог',
        'slug' => 'welcome',
        'description' => 'Этот сайт собран на собственном движке — без зависимостей, на чистом PHP и JSON-хранилище.',
        'category' => 'life',
        'cover' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/6940b991-d711-405b-85a3-41275420d606.jpg',
        'tags' => ['блог', 'старт', 'php'],
        'sticky' => true,
        'published' => true,
        'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
        'content' => [
            'blocks' => [
                ['type' => 'header', 'data' => ['text' => 'Привет!', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Этот блог работает на собственном движке. Здесь будут статьи о технологиях, дизайне и просто заметки.']],
                ['type' => 'paragraph', 'data' => ['text' => 'Можешь редактировать всё через панель администратора, добавлять категории, теги и статьи с богатым форматированием.']],
                ['type' => 'header', 'data' => ['text' => 'Что внутри', 'level' => 3]],
                ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                    'Редактор Editor.js с русской локализацией',
                    'Категории, теги и закреплённые посты',
                    'Поиск, RSS, sitemap и тёмная тема',
                    'Автосохранение черновиков'
                ]]],
                ['type' => 'delimiter', 'data' => []],
                ['type' => 'paragraph', 'data' => ['text' => 'Заходи в админку и начинай писать.']],
            ]
        ]
    ],
    [
        'title' => 'Editor.js — современный блочный редактор',
        'slug' => 'editorjs-overview',
        'description' => 'Краткий обзор возможностей Editor.js и почему мы его выбрали.',
        'category' => 'tech',
        'cover' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/21cf4044-869f-4842-aee9-5124c5a5c65b.jpg',
        'tags' => ['editor.js', 'js', 'веб'],
        'sticky' => false,
        'published' => true,
        'date' => date('Y-m-d H:i:s', strtotime('-3 days')),
        'content' => [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Editor.js — это блочный WYSIWYG-редактор от команды CodeX. Он сохраняет данные в чистый JSON, а не в HTML, что делает его удобным для дальнейшей обработки.']],
                ['type' => 'header', 'data' => ['text' => 'Главные плюсы', 'level' => 2]],
                ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [
                    'Чистый JSON на выходе — легко парсить и рендерить',
                    'Гибкая система плагинов — каждый блок отдельная библиотека',
                    'Хорошая мобильная поддержка',
                    'Открытый исходный код, MIT-лицензия'
                ]]],
                ['type' => 'quote', 'data' => ['text' => 'Editor.js — это не просто редактор, это API для создания контента.', 'caption' => 'CodeX Team']],
                ['type' => 'header', 'data' => ['text' => 'Поддерживаемые блоки', 'level' => 3]],
                ['type' => 'paragraph', 'data' => ['text' => 'В нашем движке подключены: заголовки, списки, чек-листы, цитаты, предупреждения, код, таблицы, картинки, видео, разделители и сырой HTML.']],
                ['type' => 'warning', 'data' => ['title' => 'Совет', 'message' => 'Используй сочетание клавиш / для быстрого вызова меню блоков.']],
            ]
        ]
    ],
    [
        'title' => 'Минимализм в веб-дизайне',
        'slug' => 'minimalism-web',
        'description' => 'Почему меньше иногда — это больше, и как это применять на практике.',
        'category' => 'design',
        'cover' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/050c6c32-02dd-4943-bcc7-f73d999c2df3.jpg',
        'tags' => ['дизайн', 'ux', 'минимализм'],
        'sticky' => false,
        'published' => true,
        'date' => date('Y-m-d H:i:s', strtotime('-5 days')),
        'content' => [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Минимализм в дизайне — это не про отсутствие, это про присутствие только нужного. Каждый элемент должен иметь смысл и цель.']],
                ['type' => 'header', 'data' => ['text' => 'Принципы', 'level' => 2]],
                ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                    'Один шрифт, максимум два веса',
                    'Ограниченная палитра — 2-3 цвета',
                    'Много воздуха между элементами',
                    'Иерархия через размер и контраст, а не цвет'
                ]]],
                ['type' => 'paragraph', 'data' => ['text' => 'Хорошие примеры — сайты Stripe, Vercel, Linear. Они доказывают, что строгость и красота прекрасно сочетаются.']],
                ['type' => 'quote', 'data' => ['text' => 'Совершенство достигнуто не тогда, когда нечего больше добавить, а когда нечего убрать.', 'caption' => 'Антуан де Сент-Экзюпери']],
            ]
        ]
    ],
    [
        'title' => 'Как настроить свой PHP-хостинг за 10 минут',
        'slug' => 'php-hosting-setup',
        'description' => 'Пошаговая инструкция: от загрузки FTP до публикации сайта.',
        'category' => 'guides',
        'cover' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/48e4c8f2-69a3-4dfe-a15d-b131d8bce67d.jpg',
        'tags' => ['php', 'хостинг', 'туториал'],
        'sticky' => false,
        'published' => true,
        'date' => date('Y-m-d H:i:s', strtotime('-7 days')),
        'content' => [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Запустить свой блог на собственном хостинге проще, чем кажется. Разберёмся за 10 минут.']],
                ['type' => 'header', 'data' => ['text' => 'Шаг 1. Выбор хостинга', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Подойдёт любой PHP-хостинг с поддержкой PHP 7.4+ и .htaccess (Apache). Например: TimeWeb, Beget, REG.RU.']],
                ['type' => 'header', 'data' => ['text' => 'Шаг 2. Загрузка файлов', 'level' => 2]],
                ['type' => 'list', 'data' => ['style' => 'ordered', 'items' => [
                    'Подключись по FTP/SFTP через FileZilla или встроенный файл-менеджер',
                    'Загрузи папку движка в корень сайта (обычно public_html или www)',
                    'Открой свой домен в браузере — увидишь сайт'
                ]]],
                ['type' => 'header', 'data' => ['text' => 'Шаг 3. Безопасность', 'level' => 2]],
                ['type' => 'warning', 'data' => ['title' => 'Важно', 'message' => 'Сразу зайди в админку и смени дефолтный пароль admin/admin на свой.']],
                ['type' => 'code', 'data' => ['code' => "# Проверь, что .htaccess загрузился\nls -la /public_html/.htaccess"]],
                ['type' => 'paragraph', 'data' => ['text' => 'Готово! У тебя свой блог под полным контролем.']],
            ]
        ]
    ],
    [
        'title' => 'Тёмная тема — не блажь, а необходимость',
        'slug' => 'dark-theme-importance',
        'description' => 'Зачем сайту переключатель тем и как его правильно сделать.',
        'category' => 'design',
        'cover' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/f88ef3b8-3ce4-4754-8dfd-3695eef207ea.jpg',
        'tags' => ['дизайн', 'css', 'тёмная тема'],
        'sticky' => false,
        'published' => true,
        'date' => date('Y-m-d H:i:s', strtotime('-10 days')),
        'content' => [
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'По статистике 80% пользователей включают тёмную тему системно. Если ваш сайт игнорирует это — вы теряете аудиторию.']],
                ['type' => 'header', 'data' => ['text' => 'Как сделать правильно', 'level' => 2]],
                ['type' => 'paragraph', 'data' => ['text' => 'Использовать CSS-переменные и data-атрибут на html:']],
                ['type' => 'code', 'data' => ['code' => ":root { --bg: #fff; --fg: #000; }\n[data-theme='dark'] { --bg: #111; --fg: #eee; }\nbody { background: var(--bg); color: var(--fg); }"]],
                ['type' => 'header', 'data' => ['text' => 'Что учесть', 'level' => 3]],
                ['type' => 'list', 'data' => ['style' => 'unordered', 'items' => [
                    'Уважать prefers-color-scheme системы',
                    'Сохранять выбор пользователя в localStorage',
                    'Не использовать чистый чёрный — лучше #0f0f0f',
                    'Снижать яркость акцентных цветов'
                ]]],
                ['type' => 'quote', 'data' => ['text' => 'Тёмная тема — это уважение к зрению пользователя.', 'caption' => 'UX-сообщество']],
            ]
        ]
    ],
];

$created = 0;
$skipped = 0;
foreach ($demoPosts as $dp) {
    $existing = Posts::bySlug($dp['slug']);
    if ($existing) { $skipped++; continue; }
    Posts::save($dp);
    $created++;
}

// Обновляем профиль автора, если он пустой
$users = Storage::read('users');
$profileUpdated = false;
if (!empty($users) && empty($users[0]['avatar'])) {
    $users[0]['avatar'] = 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/281daa50-b47c-480f-aa67-b8e87e151ea3.jpg';
    if (empty($users[0]['bio'])) {
        $users[0]['bio'] = 'Автор и редактор блога. Пишу о технологиях, дизайне и жизни.';
    }
    Storage::write('users', $users);
    $profileUpdated = true;
}

echo json_encode([
    'ok' => true,
    'categories_added' => count(array_diff(array_column($demoCats, 'key'), $existingKeys)),
    'posts_added' => $created,
    'posts_skipped' => $skipped,
    'profile_updated' => $profileUpdated,
]);