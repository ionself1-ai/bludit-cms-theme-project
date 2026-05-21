<?php
ob_start();

$id = $_GET['id'] ?? '';
$post = $id ? Posts::byId($id) : null;
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $contentJson = $_POST['content'] ?? '';
    $contentArr = json_decode($contentJson, true);
    $tagsRaw = trim($_POST['tags'] ?? '');
    $tags = $tagsRaw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));
    $data = [
        'id' => $_POST['id'] ?? null,
        'title' => trim($_POST['title'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category' => $_POST['category'] ?? '',
        'cover' => trim($_POST['cover'] ?? ''),
        'tags' => $tags,
        'sticky' => !empty($_POST['sticky']),
        'title_on_cover' => !empty($_POST['title_on_cover']),
        'cover_overlay_type' => $_POST['cover_overlay_type'] ?? 'title',
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

$post = $post ?: ['title'=>'','slug'=>'','description'=>'','category'=>'','cover'=>'','tags'=>[],'sticky'=>false,'title_on_cover'=>false,'cover_overlay_type'=>'title','content'=>['blocks'=>[]],'published'=>false,'id'=>''];
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

        <div style="margin-top:.75rem; padding:.75rem; background:var(--secondary); border-radius:8px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="title_on_cover" id="title-on-cover" value="1" <?= !empty($post['title_on_cover'])?'checked':'' ?>>
                <span>Показать текст поверх обложки</span>
            </label>
            <div id="cover-overlay-options" style="margin-top:.5rem; padding-left:24px; <?= empty($post['title_on_cover'])?'display:none;':'' ?>">
                <label style="display:flex; align-items:center; gap:8px; margin-bottom:6px; cursor:pointer;">
                    <input type="radio" name="cover_overlay_type" value="title" <?= ($post['cover_overlay_type'] ?? 'title')==='title'?'checked':'' ?>>
                    <span>Заголовок статьи</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; margin-bottom:6px; cursor:pointer;">
                    <input type="radio" name="cover_overlay_type" value="category" <?= ($post['cover_overlay_type'] ?? '')==='category'?'checked':'' ?>>
                    <span>Категория</span>
                </label>
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="radio" name="cover_overlay_type" value="both" <?= ($post['cover_overlay_type'] ?? '')==='both'?'checked':'' ?>>
                    <span>Категория + заголовок</span>
                </label>
            </div>
        </div>
        <script>
        (function(){
            const cb = document.getElementById('title-on-cover');
            const opts = document.getElementById('cover-overlay-options');
            if (cb && opts) cb.addEventListener('change', () => { opts.style.display = cb.checked ? '' : 'none'; });
        })();
        </script>
    </div>

    <div class="form-row">
        <label>Содержимое</label>
        <div id="editorjs" class="editor-wrap"></div>
        <input type="hidden" name="content" id="content-json">
    </div>

    <div class="form-row">
        <label>Теги (через запятую)</label>
        <input type="text" name="tags" value="<?= htmlspecialchars(implode(', ', $post['tags'] ?? [])) ?>" placeholder="например: php, веб, дизайн">
    </div>

    <div class="form-row" style="display:flex; gap:1.5rem; flex-wrap:wrap;">
        <label><input type="checkbox" name="published" value="1" <?= !empty($post['published'])?'checked':'' ?>> Опубликовать</label>
        <label><input type="checkbox" name="sticky" value="1" <?= !empty($post['sticky'])?'checked':'' ?>> 📌 Закрепить наверху</label>
    </div>

    <div id="autosave-status" style="font-size:12px;color:var(--muted);margin-bottom:1rem;"></div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" id="save-btn">Сохранить</button>
        <a href="<?= BASE_URL ?>?route=admin/posts" class="btn">Отмена</a>
    </div>
</form>

<?php
// Локальный путь к Editor.js
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
    data: <?= json_encode($post['content'] ?: ['blocks'=>[]], JSON_UNESCAPED_UNICODE) ?>,
    i18n: window.EDITORJS_I18N_RU,
    tools: {
        header: { class: Header, inlineToolbar: ['marker','bold','italic','link'], config: { placeholder: 'Заголовок', levels: [2,3,4], defaultLevel: 2 } },
        list: { class: (window.EditorjsList || window.List), inlineToolbar: true },
        checklist: { class: Checklist, inlineToolbar: true },
        quote: { class: Quote, inlineToolbar: true },
        warning: { class: Warning, inlineToolbar: true, config: { titlePlaceholder: 'Заголовок', messagePlaceholder: 'Сообщение' } },
        code: CodeTool,
        delimiter: Delimiter,
        table: { class: Table, inlineToolbar: true, config: { rows: 2, cols: 3 } },
        embed: { class: Embed, config: { services: { youtube: true, vimeo: true, coub: true, twitter: true } } },
        raw: RawTool,
        marker: { class: Marker, shortcut: 'CMD+SHIFT+M' },
        inlineCode: { class: InlineCode, shortcut: 'CMD+SHIFT+C' },
        underline: Underline,
        image: {
            class: ImageTool,
            config: { endpoints: { byFile: '<?= BASE_URL ?>?route=admin/upload' }, captionPlaceholder: 'Подпись' }
        }
    },
    placeholder: 'Начните писать или нажмите «+»...'
});

document.querySelector('form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = await editor.save();
    document.getElementById('content-json').value = JSON.stringify(data);
    e.target.submit();
});

/* === АВТОСОХРАНЕНИЕ === */
const POST_ID_KEY = 'post-draft-<?= htmlspecialchars($post['id'] ?: 'new') ?>';
const statusEl = document.getElementById('autosave-status');
let lastSave = '';
let saveTimer = null;

function readForm() {
    const f = document.querySelector('form');
    const tags = (f.tags.value || '').split(',').map(s => s.trim()).filter(Boolean);
    return {
        id: f.id.value || null,
        csrf: f.csrf.value,
        title: f.title.value,
        slug: f.slug.value,
        description: f.description.value,
        category: f.category.value,
        cover: f['cover'].value,
        tags,
        sticky: f.sticky.checked,
        title_on_cover: f['title_on_cover'] ? f['title_on_cover'].checked : false,
        cover_overlay_type: (function(){
            const r = f.querySelector('input[name="cover_overlay_type"]:checked');
            return r ? r.value : 'title';
        })(),
        published: f.published.checked
    };
}

async function autosave() {
    if (!editor) return;
    try {
        const content = await editor.save();
        const body = { ...readForm(), content };
        const payload = JSON.stringify(body);
        if (payload === lastSave) return;
        statusEl.textContent = 'Сохраняю...';
        const r = await fetch('<?= BASE_URL ?>?route=admin/autosave', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: payload
        });
        const d = await r.json();
        if (d.ok) {
            lastSave = payload;
            localStorage.setItem(POST_ID_KEY, payload);
            statusEl.textContent = '✓ Сохранено автоматически в ' + d.time;
            // Подменяем id для последующих сохранений
            if (d.id && !document.querySelector('[name=id]').value) {
                document.querySelector('[name=id]').value = d.id;
                history.replaceState(null, '', '?route=admin/post-edit&id=' + encodeURIComponent(d.id));
            }
        } else {
            statusEl.textContent = '⚠ Ошибка автосохранения';
        }
    } catch (e) {
        statusEl.textContent = '⚠ Не удалось сохранить';
    }
}

function scheduleSave() {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(autosave, 4000);
}

// Слушаем изменения
['title','slug','description','category','cover','tags'].forEach(n => {
    const el = document.querySelector('[name="'+n+'"]');
    if (el) el.addEventListener('input', scheduleSave);
});
['published','sticky'].forEach(n => {
    const el = document.querySelector('[name="'+n+'"]');
    if (el) el.addEventListener('change', scheduleSave);
});
// Editor.js — следим за изменением блоков
const ejs = document.getElementById('editorjs');
if (ejs) new MutationObserver(scheduleSave).observe(ejs, {subtree:true, childList:true, characterData:true});

// Восстановление из localStorage, если в БД пусто
window.addEventListener('load', () => {
    try {
        const saved = localStorage.getItem(POST_ID_KEY);
        if (saved && !document.querySelector('[name=title]').value) {
            const d = JSON.parse(saved);
            if (confirm('Найден локальный черновик. Восстановить?')) {
                document.querySelector('[name=title]').value = d.title || '';
                document.querySelector('[name=description]').value = d.description || '';
                document.querySelector('[name=tags]').value = (d.tags || []).join(', ');
                if (editor && d.content) editor.render(d.content);
            }
        }
    } catch (e) {}
});
</script>
<?php endif; ?>

<script>
// Загрузка обложки — работает независимо от Editor.js
const coverFile = document.getElementById('cover-file');
if (coverFile) coverFile.addEventListener('change', async (e) => {
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