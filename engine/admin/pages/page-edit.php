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

<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script>
<script>
const editor = new EditorJS({
    holder: 'editorjs',
    data: <?= json_encode($page['content'] ?: ['blocks'=>[]], JSON_UNESCAPED_UNICODE) ?>,
    tools: { header: Header, list: List, quote: Quote, code: CodeTool, delimiter: Delimiter,
        image: { class: ImageTool, config: { endpoints: { byFile: '<?= BASE_URL ?>?route=admin/upload' } } } },
});
document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const d = await editor.save();
    document.getElementById('content-json').value = JSON.stringify(d);
    e.target.submit();
});
</script>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
