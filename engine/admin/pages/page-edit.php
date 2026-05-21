<?php
ob_start();
$slug = $_GET['slug'] ?? '';
$page = $slug ? Pages::get($slug) : null;
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $content = json_decode($_POST['content'] ?? '', true);
    $newSlug = Storage::slug($_POST['slug'] ?? $_POST['title'] ?? '');
    $data = [
        'slug' => $newSlug,
        'title' => trim($_POST['title'] ?? ''),
        'content' => is_array($content) ? $content : ['blocks' => []],
    ];
    if (!empty($_POST['_oldSlug'])) $data['_oldSlug'] = $_POST['_oldSlug'];
    if (empty($data['title'])) { $err = 'Заполните название'; }
    else { Pages::save($data); header('Location: '.BASE_URL.'?route=admin/page-edit&slug='.urlencode($newSlug).'&saved=1'); exit; }
}

$page = $page ?: ['slug'=>'','title'=>'','content'=>['blocks'=>[]]];
?>
<div class="admin-header">
    <h1><?= $slug ? 'Редактирование' : 'Новая страница' ?></h1>
    <a href="<?= BASE_URL ?>?route=admin/pages" class="btn">← Все страницы</a>
</div>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Сохранено</div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<form method="post" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">
    <?php if ($slug): ?><input type="hidden" name="_oldSlug" value="<?= htmlspecialchars($slug) ?>"><?php endif; ?>

    <div class="form-row"><label>Название *</label><input type="text" name="title" value="<?= htmlspecialchars($page['title']) ?>" required></div>
    <div class="form-row"><label>URL (slug)</label><input type="text" name="slug" value="<?= htmlspecialchars($page['slug']) ?>" placeholder="about, rules, privacy..."></div>
    <div class="form-row">
        <label>Содержимое</label>
        <div id="editorjs" class="editor-wrap"></div>
        <input type="hidden" name="content" id="content-json">
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="<?= BASE_URL ?>?route=admin/pages" class="btn">Отмена</a>
    </div>
</form>

<?php
$ejsBase = BASE_URL . 'assets/editorjs/';
$ejsDir  = ROOT_PATH . '/assets/editorjs/';
$ejsReady = file_exists($ejsDir . 'editorjs.js') && file_exists($ejsDir . 'header.js');
?>
<?php if (!$ejsReady): ?>
<div class="alert alert-error">
    Файлы Editor.js не установлены локально.
    <a href="#" id="install-editor-btn" class="btn" style="margin-left:10px;">Установить сейчас</a>
</div>
<script>
document.getElementById('install-editor-btn').addEventListener('click', async (e) => {
    e.preventDefault();
    e.target.textContent = 'Загрузка...';
    const r = await fetch('<?= BASE_URL ?>?route=admin/install-editor');
    const d = await r.json();
    if (d.ok) location.reload();
    else alert('Ошибка установки');
});
</script>
<?php else: ?>
<script src="<?= $ejsBase ?>editorjs.js"></script>
<script src="<?= $ejsBase ?>header.js"></script>
<script src="<?= $ejsBase ?>list.js"></script>
<script src="<?= $ejsBase ?>checklist.js"></script>
<script src="<?= $ejsBase ?>quote.js"></script>
<script src="<?= $ejsBase ?>warning.js"></script>
<script src="<?= $ejsBase ?>image.js"></script>
<script src="<?= $ejsBase ?>code.js"></script>
<script src="<?= $ejsBase ?>delimiter.js"></script>
<script src="<?= $ejsBase ?>table.js"></script>
<script src="<?= $ejsBase ?>embed.js"></script>
<script src="<?= $ejsBase ?>raw.js"></script>
<script src="<?= $ejsBase ?>marker.js"></script>
<script src="<?= $ejsBase ?>inline-code.js"></script>
<script src="<?= $ejsBase ?>underline.js"></script>
<script src="<?= $ejsBase ?>i18n-ru.js"></script>
<script>
const editor = new EditorJS({
    holder: 'editorjs',
    data: <?= json_encode($page['content'] ?: ['blocks'=>[]], JSON_UNESCAPED_UNICODE) ?>,
    i18n: window.EDITORJS_I18N_RU,
    tools: {
        header: { class: Header, inlineToolbar: ['marker','bold','italic','link'], config: { placeholder: 'Заголовок', levels: [2,3,4], defaultLevel: 2 } },
        list: { class: (window.EditorjsList || window.List), inlineToolbar: true },
        checklist: { class: Checklist, inlineToolbar: true },
        quote: { class: Quote, inlineToolbar: true },
        warning: { class: Warning, inlineToolbar: true },
        code: CodeTool,
        delimiter: Delimiter,
        table: { class: Table, inlineToolbar: true, config: { rows: 2, cols: 3 } },
        embed: Embed,
        raw: RawTool,
        marker: Marker,
        inlineCode: InlineCode,
        underline: Underline,
        image: { class: ImageTool, config: { endpoints: { byFile: '<?= BASE_URL ?>?route=admin/upload' }, captionPlaceholder: 'Подпись' } }
    },
    placeholder: 'Начните писать или нажмите «+»...'
});
document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const d = await editor.save();
    document.getElementById('content-json').value = JSON.stringify(d);
    e.target.submit();
});
</script>
<?php endif; ?>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';