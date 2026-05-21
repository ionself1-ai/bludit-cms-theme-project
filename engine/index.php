<?php
require __DIR__ . '/config.php';
require __DIR__ . '/lib/Storage.php';
require __DIR__ . '/lib/Auth.php';
require __DIR__ . '/lib/Posts.php';
require __DIR__ . '/lib/Categories.php';
require __DIR__ . '/lib/Pages.php';
require __DIR__ . '/lib/Settings.php';
require __DIR__ . '/lib/Uploader.php';
require __DIR__ . '/theme/icons.php';

// Первый запуск — создаём админа
$users = Storage::read('users');
if (empty($users)) {
    Storage::write('users', [[
        'username' => 'admin',
        'password' => password_hash('admin', PASSWORD_DEFAULT),
        'name' => 'Администратор',
        'bio' => 'Автор и редактор блога. Пишу о технологиях, дизайне и жизни.',
        'avatar' => 'https://cdn.poehali.dev/projects/9f6e2264-476d-4c4b-a9c4-d0d1ec9ddf3f/files/281daa50-b47c-480f-aa67-b8e87e151ea3.jpg',
    ]]);
    Storage::write('settings', [
        'site_title' => 'Мой блог',
        'site_description' => 'Личный блог об интересном',
        'posts_per_page' => 9,
    ]);
    Storage::write('categories', [
        ['key' => 'novosti', 'name' => 'Новости', 'description' => 'Свежие материалы'],
    ]);
    Storage::write('pages', [
        ['slug' => 'about', 'title' => 'Обо мне', 'content' => ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Здесь расскажите о себе.']]]]],
        ['slug' => 'rules', 'title' => 'Правила', 'content' => ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Здесь правила сайта.']]]]],
    ]);
}

// Роутинг
$route = $_GET['route'] ?? 'home';
$parts = explode('/', trim($route, '/'));
$section = $parts[0] ?? 'home';

if ($section === 'admin') {
    require __DIR__ . '/admin/router.php';
    exit;
}

// Публичный роутинг
$publicRoutes = [
    'home', 'category', 'post', 'page', 'search',
];

// Спец-роуты без layout
if ($section === 'rss') {
    require THEME_PATH . '/rss.php'; exit;
}
if ($section === 'sitemap') {
    require THEME_PATH . '/sitemap.php'; exit;
}

if ($section === 'category' && !empty($parts[1])) {
    $category = Categories::get($parts[1]);
    if (!$category) { http_response_code(404); $template = '404'; $pageTitle = 'Не найдено'; }
    else {
        $allPosts = Posts::byCategory($category['key']);
        $pag = Posts::paginate($allPosts, (int)($_GET['page'] ?? 1), Settings::get('posts_per_page', 9));
        $pageTitle = $category['name'];
        $template = 'category';
    }
} elseif ($section === 'tag' && !empty($parts[1])) {
    $tag = urldecode($parts[1]);
    $allPosts = Posts::byTag($tag);
    $pag = Posts::paginate($allPosts, (int)($_GET['page'] ?? 1), Settings::get('posts_per_page', 9));
    $pageTitle = '#' . $tag;
    $template = 'tag';
} elseif ($section === 'post' && !empty($parts[1])) {
    $post = Posts::bySlug($parts[1]);
    if (!$post || empty($post['published'])) { http_response_code(404); $template = '404'; $pageTitle = 'Не найдено'; }
    else {
        $pageTitle = $post['title'];
        $template = 'post';
    }
} elseif ($section === 'page' && !empty($parts[1])) {
    $page = Pages::get($parts[1]);
    if (!$page) { http_response_code(404); $template = '404'; $pageTitle = 'Не найдено'; }
    else { $pageTitle = $page['title']; $template = 'page'; }
} elseif ($section === 'author') {
    $author = Auth::user();
    $allPosts = Posts::all(true);
    $pag = Posts::paginate($allPosts, (int)($_GET['page'] ?? 1), Settings::get('posts_per_page', 9));
    $pageTitle = $author['name'] ?? 'Автор';
    $template = 'author';
} elseif ($section === 'search') {
    $query = $_GET['q'] ?? '';
    $allPosts = Posts::search($query);
    $pag = Posts::paginate($allPosts, (int)($_GET['page'] ?? 1), Settings::get('posts_per_page', 9));
    $pageTitle = 'Поиск';
    $template = 'search';
} elseif ($section === 'tags') {
    $tagsCloud = Posts::allTags();
    $pageTitle = 'Все теги';
    $template = 'tags';
} elseif (!in_array($section, ['home', '']) && $section !== '') {
    http_response_code(404);
    $template = '404'; $pageTitle = 'Не найдено';
} else {
    $allPosts = Posts::all(true);
    $pag = Posts::paginate($allPosts, (int)($_GET['page'] ?? 1), Settings::get('posts_per_page', 9));
    $pageTitle = Settings::get('site_title');
    $template = 'home';
}

require THEME_PATH . '/layout.php';