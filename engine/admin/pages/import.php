<?php
ob_start();
$msg = $err = ''; $report = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $publishAs = $_POST['publish_as'] ?? 'draft'; // draft | published
    $createCats = !empty($_POST['create_cats']);
    $imported = []; $skipped = 0; $errors = [];

    // Markdown файлы
    if (!empty($_FILES['md_files']['name'][0])) {
        foreach ($_FILES['md_files']['name'] as $idx => $name) {
            if (empty($_FILES['md_files']['tmp_name'][$idx])) continue;
            $tmp = $_FILES['md_files']['tmp_name'][$idx];
            $content = @file_get_contents($tmp);
            if ($content === false) { $errors[] = $name . ': не удалось прочитать'; continue; }
            $post = Importer::markdownToPost($content, $name);
            $post['published'] = ($publishAs === 'published');
            if (!empty($post['category']) && $createCats) {
                $catKey = Storage::slug($post['category']);
                if (!Categories::get($catKey)) {
                    Categories::save(['key'=>$catKey, 'name'=>$post['category']]);
                }
                $post['category'] = $catKey;
            } elseif (!empty($post['category'])) {
                $post['category'] = Storage::slug($post['category']);
                if (!Categories::get($post['category'])) $post['category'] = '';
            }
            $id = Posts::save($post);
            if ($id) $imported[] = $post['title'];
            else $skipped++;
        }
    }

    // WordPress XML
    if (!empty($_FILES['wp_xml']['tmp_name']) && is_uploaded_file($_FILES['wp_xml']['tmp_name'])) {
        $xml = file_get_contents($_FILES['wp_xml']['tmp_name']);
        $res = Importer::parseWordPressXml($xml);
        if (!$res['ok']) {
            $errors[] = 'WordPress XML: ' . ($res['error'] ?? 'ошибка парсинга');
        } else {
            foreach ($res['posts'] as $post) {
                if ($publishAs === 'draft') $post['published'] = false;
                if (!empty($post['category_name']) && $createCats) {
                    $catKey = Storage::slug($post['category_name']);
                    if (!Categories::get($catKey)) {
                        Categories::save(['key'=>$catKey, 'name'=>$post['category_name']]);
                    }
                    $post['category'] = $catKey;
                } elseif (!empty($post['category']) && !Categories::get($post['category'])) {
                    $post['category'] = '';
                }
                unset($post['category_name']);
                $id = Posts::save($post);
                if ($id) $imported[] = $post['title'];
                else $skipped++;
            }
        }
    }

    if (empty($imported) && empty($errors)) {
        $err = 'Не выбран файл для импорта';
    } else {
        $report = ['imported'=>$imported, 'skipped'=>$skipped, 'errors'=>$errors];
        $msg = 'Импорт завершён: добавлено ' . count($imported) . ' статей';
    }
}
?>
<div class="admin-header">
    <h1>Импорт статей</h1>
    <a href="<?= BASE_URL ?>?route=admin/posts" class="btn">← К списку статей</a>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<?php if ($report): ?>
<div class="admin-card" style="margin-bottom:1rem;">
    <h3 style="margin-bottom:0.5rem;">Результаты</h3>
    <?php if (!empty($report['imported'])): ?>
        <p style="color:#10b981; margin-bottom:0.5rem;">✓ Добавлено: <?= count($report['imported']) ?></p>
        <ul style="margin-left:1.5rem; font-size:13px; color:var(--muted);">
            <?php foreach (array_slice($report['imported'], 0, 20) as $t): ?>
                <li><?= htmlspecialchars($t) ?></li>
            <?php endforeach; ?>
            <?php if (count($report['imported']) > 20): ?>
                <li>... и ещё <?= count($report['imported']) - 20 ?></li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
    <?php if (!empty($report['errors'])): ?>
        <p style="color:#ef4444; margin-top:0.75rem;">⚠ Ошибки: <?= count($report['errors']) ?></p>
        <ul style="margin-left:1.5rem; font-size:13px; color:#ef4444;">
            <?php foreach ($report['errors'] as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">

    <div class="form-row">
        <label>📝 Markdown-файлы (.md, .markdown, .txt)</label>
        <input type="file" name="md_files[]" accept=".md,.markdown,.txt" multiple>
        <div class="hint">Можно загрузить сразу несколько файлов. Поддерживается front-matter (--- title/date/tags/category/cover ---), заголовки, списки, цитаты, код, изображения.</div>
    </div>

    <div class="form-row">
        <label>📦 WordPress XML (WXR-экспорт)</label>
        <input type="file" name="wp_xml" accept=".xml">
        <div class="hint">Экспортируйте файл из WordPress через Инструменты → Экспорт. Импортируются только посты (страницы, медиа и комментарии не переносятся).</div>
    </div>

    <div class="form-row">
        <label>Статус импортированных статей</label>
        <label style="display:block; margin-top:6px;">
            <input type="radio" name="publish_as" value="draft" checked> Черновик (нужно подтвердить публикацию вручную)
        </label>
        <label style="display:block; margin-top:6px;">
            <input type="radio" name="publish_as" value="published"> Опубликовано сразу
        </label>
    </div>

    <div class="form-row">
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="create_cats" value="1" checked>
            <span>Автоматически создавать недостающие категории</span>
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Импортировать</button>
        <a href="<?= BASE_URL ?>?route=admin/posts" class="btn">Отмена</a>
    </div>
</form>

<div class="admin-card" style="margin-top:1rem; background:var(--secondary);">
    <h3 style="font-size:1rem; margin-bottom:0.5rem;">💡 Подсказки</h3>
    <p style="font-size:13.5px; color:var(--muted); line-height:1.6;">
        <b>Markdown:</b> можно указать метаданные в начале файла:<br>
        <code style="display:block; padding:8px; background:var(--card); border-radius:6px; margin:6px 0; font-size:12.5px;">---<br>title: Моя статья<br>date: 2024-12-01<br>tags: php, веб<br>category: tutorials<br>cover: https://...<br>description: Краткое описание<br>---<br>Текст статьи...</code>
        <b>WordPress:</b> используйте файл <code>wordpress.xxx-xx-xx.000.xml</code> из экспорта.
    </p>
</div>

<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
