<?php
ob_start();

$id = $_GET['id'] ?? '';
$post = $id ? Posts::byId($id) : null;
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $contentJson = $_POST['content'] ?? '';
    $contentArr = json_decode($contentJson, true);
    $data = [
        'id' => $_POST['id'] ?? null,
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category' => $_POST['category'] ?? '',
        'cover' => trim($_POST['cover'] ?? ''),
        'content' => is_array($contentArr) ? $contentArr : ['blocks' => []],
        'published' => !empty($_POST['published']),
    ];
    if (empty($data['title'])) {
        $err = 'Заполните название';
    } else {
        $newId = Posts::save($data);
        header('Location: ' . BASE_URL . '?route=admin/post-edit&id=' . urlencode($newId) . '&saved=1');
        exit;
    }
}

$post = $post ?: ['title'=>'','slug'=>'','description'=>'','category'=>'','cover'=>'','content'=>['blocks'=>[]],'published'=>false,'id'=>''];
$cats = Categories::all();
?>
<div class="admin-header">
    <h1><?= $id ? 'Редактирование' : 'Новая статья' ?></h1>
    <a href="<?= BASE_URL ?>?route=admin/posts" class="btn">← Все статьи</a>
</div>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Сохранено</div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<form method="post" class="admin-card">
    <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">

    <div class="form-row">
        <label>Заголовок *</label>
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
    </div>

    <div class="form-row">
        <label>URL (slug)</label>
        <input type="text" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" placeholder="оставьте пустым для авто">
        <div class="hint">Латиница, цифры и дефисы. Если пусто — сгенерируется из заголовка.</div>
    </div>

    <div class="form-row">
        <label>Описание (краткое)</label>
        <textarea name="description"><?= htmlspecialchars($post['description']) ?></textarea>
    </div>

    <div class="form-row">
        <label>Категория</label>
        <select name="category">
            <option value="">— Без категории —</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= htmlspecialchars($c['key']) ?>" <?= $post['category']===$c['key']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <label>Обложка</label>
        <div style="display:flex; gap:8px; align-items:center;">
            <input type="text" name="cover" id="cover-url" value="<?= htmlspecialchars($post['cover']) ?>" placeholder="URL или загрузите">
            <input type="file" id="cover-file" accept="image/*" style="display:none;">
            <button type="button" class="btn" onclick="document.getElementById('cover-file').click()">Загрузить</button>
        </div>
        <?php if (!empty($post['cover'])): ?><img src="<?= htmlspecialchars($post['cover']) ?>" style="max-width:200px;margin-top:.5rem;border-radius:8px;"><?php endif; ?>
    </div>

    <div class="form-row">
        <label>Содержимое</label>
        <div id="editorjs" class="editor-wrap"></div>
        <input type="hidden" name="content" id="content-json">
    </div>

    <div class="form-row">
        <label><input type="checkbox" name="published" value="1" <?= !empty($post['published'])?'checked':'' ?>> Опубликовать</label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="save-btn">Сохранить</button>
        <a href="<?= BASE_URL ?>?route=admin/posts" class="btn">Отмена</a>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest"></script>
<script>
const editor = new EditorJS({
    holder: 'editorjs',
    data: <?= json_encode($post['content'] ?: ['blocks'=>[]], JSON_UNESCAPED_UNICODE) ?>,
    tools: {
        header: Header,
        list: List,
        quote: Quote,
        code: CodeTool,
        delimiter: Delimiter,
        embed: Embed,
        image: {
            class: ImageTool,
            config: {
                endpoints: {
                    byFile: '<?= BASE_URL ?>?route=admin/upload',
                }
            }
        }
    },
    placeholder: 'Начните писать...'
});

document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = await editor.save();
    document.getElementById('content-json').value = JSON.stringify(data);
    e.target.submit();
});

// Загрузка обложки
document.getElementById('cover-file').addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('image', file);
    fd.append('csrf', '<?= Auth::csrf() ?>');
    const res = await fetch('<?= BASE_URL ?>?route=admin/upload-cover', { method:'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        document.getElementById('cover-url').value = data.url;
    } else {
        alert(data.error || 'Ошибка');
    }
});
</script>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
