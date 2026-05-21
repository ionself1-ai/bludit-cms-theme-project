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
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <input type="text" name="cover" id="cover-url" value="<?= htmlspecialchars($post['cover']) ?>" placeholder="URL или загрузите" style="flex:1; min-width:200px;">
            <input type="file" id="cover-file" accept="image/*" style="display:none;">
            <button type="button" class="btn" onclick="document.getElementById('cover-file').click()">Загрузить</button>
            <button type="button" class="btn" id="media-gallery-btn">Из загруженных</button>
        </div>
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

        <!-- Мини-превью карточки -->
        <div class="card-preview-wrap" style="margin-top:1rem;">
            <div class="card-preview-label" style="font-size:12px; color:var(--muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:0.05em;">Превью карточки <span style="text-transform:none; letter-spacing:0; opacity:0.7;">— перетащите картинку, чтобы загрузить обложку</span></div>
            <div class="card-preview" id="card-preview" style="max-width:340px; border:1px solid var(--border); border-radius:12px; overflow:hidden; background:var(--card); transition:border-color 0.15s, box-shadow 0.15s;">
                <div class="cp-cover" id="cp-cover" style="position:relative; aspect-ratio:16/9; background:var(--secondary); overflow:hidden; cursor:pointer;" title="Нажмите или перетащите картинку">
                    <img id="cp-img" src="<?= htmlspecialchars($post['cover'] ?? '') ?>" alt="" style="width:100%; height:100%; object-fit:cover; display:<?= !empty($post['cover'])?'block':'none' ?>;">
                    <div id="cp-empty" style="display:<?= empty($post['cover'])?'flex':'none' ?>; position:absolute; inset:0; align-items:center; justify-content:center; color:var(--muted); font-size:13px;">Обложка не задана</div>
                    <div id="cp-gradient" style="display:none; position:absolute; inset:0; background:linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.15) 45%, rgba(0,0,0,0.75) 100%); pointer-events:none;"></div>
                    <div id="cp-overlay" style="display:none; position:absolute; left:14px; right:14px; bottom:12px; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,0.5);">
                        <span id="cp-cat" style="display:none; padding:3px 10px; background:rgba(255,255,255,0.18); backdrop-filter:blur(6px); border-radius:999px; font-size:10.5px; font-weight:600; letter-spacing:0.02em; text-transform:uppercase; margin-bottom:6px;"></span>
                        <div id="cp-title-overlay" style="display:none; font-size:0.95rem; font-weight:700; line-height:1.3; margin-top:6px;"></div>
                    </div>
                    <div id="cp-drop" style="display:none; position:absolute; inset:0; background:rgba(59,130,246,0.85); color:#fff; align-items:center; justify-content:center; font-size:14px; font-weight:600; pointer-events:none; z-index:10; flex-direction:column; gap:6px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <div>Отпустите, чтобы загрузить</div>
                    </div>
                    <div id="cp-uploading" style="display:none; position:absolute; inset:0; background:rgba(0,0,0,0.55); color:#fff; align-items:center; justify-content:center; font-size:13px; z-index:11;">Загрузка...</div>
                    <button type="button" id="cp-remove" title="Удалить обложку" aria-label="Удалить обложку" style="display:none; position:absolute; top:8px; right:8px; width:32px; height:32px; border-radius:50%; border:none; background:rgba(0,0,0,0.6); color:#fff; cursor:pointer; align-items:center; justify-content:center; opacity:0; transition:opacity 0.15s, background 0.15s; z-index:12; backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>
                <style>
                #card-preview:hover #cp-remove[data-has-cover="1"] { opacity: 1; }
                #cp-remove[data-has-cover="1"] { display: inline-flex !important; }
                #cp-remove:hover { background: rgba(220,38,38,0.85) !important; }
                #cp-remove:focus-visible { opacity: 1; outline: 2px solid var(--accent); outline-offset: 2px; }
                </style>
                <div class="cp-body" style="padding:0.85rem 1rem 1rem;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:11.5px; color:var(--muted); margin-bottom:6px;">
                        <span id="cp-body-cat" style="color:var(--accent); font-weight:600;"></span>
                        <span><?= date('d.m.Y') ?></span>
                    </div>
                    <div id="cp-body-title" style="font-size:1rem; font-weight:600; line-height:1.35; margin-bottom:6px;">Заголовок статьи</div>
                    <div id="cp-body-desc" style="font-size:13px; color:var(--muted); line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"></div>
                </div>
            </div>
        </div>

        <script>
        (function(){
            const $ = (id) => document.getElementById(id);
            const cb = $('title-on-cover');
            const opts = $('cover-overlay-options');
            const coverInput = $('cover-url');
            const titleInput = document.querySelector('input[name="title"]');
            const descInput = document.querySelector('textarea[name="description"]');
            const catSelect = document.querySelector('select[name="category"]');

            function getCatName() {
                if (!catSelect || !catSelect.value) return '';
                const opt = catSelect.options[catSelect.selectedIndex];
                return opt ? opt.textContent.trim() : '';
            }
            function getOverlayType() {
                const r = document.querySelector('input[name="cover_overlay_type"]:checked');
                return r ? r.value : 'title';
            }

            function refresh() {
                const url = (coverInput.value || '').trim();
                const title = (titleInput.value || '').trim() || 'Заголовок статьи';
                const desc = (descInput.value || '').trim();
                const catName = getCatName();
                const hasOverlay = cb.checked && url;
                const type = getOverlayType();

                // Изображение
                if (url) {
                    $('cp-img').src = url;
                    $('cp-img').style.display = 'block';
                    $('cp-empty').style.display = 'none';
                } else {
                    $('cp-img').style.display = 'none';
                    $('cp-empty').style.display = 'flex';
                }

                // Кнопка удаления — только если есть обложка
                const rmBtn = $('cp-remove');
                if (rmBtn) rmBtn.setAttribute('data-has-cover', url ? '1' : '0');

                // Оверлей
                $('cp-gradient').style.display = hasOverlay ? 'block' : 'none';
                $('cp-overlay').style.display = hasOverlay ? 'block' : 'none';

                const showCat = hasOverlay && (type === 'category' || type === 'both') && catName;
                const showTitle = hasOverlay && (type === 'title' || type === 'both');
                $('cp-cat').style.display = showCat ? 'inline-block' : 'none';
                $('cp-cat').textContent = catName;
                $('cp-title-overlay').style.display = showTitle ? 'block' : 'none';
                $('cp-title-overlay').textContent = title;

                // Тело карточки
                $('cp-body-cat').textContent = catName;
                $('cp-body-cat').style.display = catName ? 'inline' : 'none';
                $('cp-body-title').textContent = title;
                $('cp-body-desc').textContent = desc;
                $('cp-body-desc').style.display = desc ? '-webkit-box' : 'none';

                // Опции оверлея
                opts.style.display = cb.checked ? '' : 'none';
            }

            // Слушатели
            [coverInput, titleInput, descInput].forEach(el => el && el.addEventListener('input', refresh));
            catSelect && catSelect.addEventListener('change', refresh);
            cb.addEventListener('change', refresh);
            document.querySelectorAll('input[name="cover_overlay_type"]').forEach(r => r.addEventListener('change', refresh));

            // Глобальный хук — чтобы загрузка обложки тоже обновляла превью
            window.refreshCardPreview = refresh;
            refresh();

            // === Drag & Drop / Click для загрузки обложки прямо в превью ===
            const card = $('card-preview');
            const dropZone = $('cp-cover');
            const dropOverlay = $('cp-drop');
            const uploadingOverlay = $('cp-uploading');
            const fileInput = $('cover-file');

            async function uploadFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                    alert('Это не изображение');
                    return;
                }
                uploadingOverlay.style.display = 'flex';
                try {
                    const fd = new FormData();
                    fd.append('image', file);
                    fd.append('csrf', '<?= Auth::csrf() ?>');
                    const res = await fetch('<?= BASE_URL ?>?route=admin/upload-cover', { method:'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        coverInput.value = data.url;
                        coverInput.dispatchEvent(new Event('input', { bubbles: true }));
                        refresh();
                    } else {
                        alert(data.error || 'Ошибка загрузки');
                    }
                } catch (e) {
                    alert('Ошибка сети');
                } finally {
                    uploadingOverlay.style.display = 'none';
                }
            }

            // Клик по превью — открыть выбор файла
            dropZone.addEventListener('click', (e) => {
                e.preventDefault();
                fileInput.click();
            });

            // Drag & Drop
            let dragDepth = 0;
            function isFileDrag(e) {
                return e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files');
            }
            card.addEventListener('dragenter', (e) => {
                if (!isFileDrag(e)) return;
                e.preventDefault();
                dragDepth++;
                dropOverlay.style.display = 'flex';
                card.style.borderColor = 'var(--accent)';
                card.style.boxShadow = '0 0 0 3px rgba(59,130,246,0.15)';
            });
            card.addEventListener('dragover', (e) => {
                if (!isFileDrag(e)) return;
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
            });
            card.addEventListener('dragleave', (e) => {
                if (!isFileDrag(e)) return;
                dragDepth--;
                if (dragDepth <= 0) {
                    dragDepth = 0;
                    dropOverlay.style.display = 'none';
                    card.style.borderColor = '';
                    card.style.boxShadow = '';
                }
            });
            card.addEventListener('drop', (e) => {
                if (!isFileDrag(e)) return;
                e.preventDefault();
                dragDepth = 0;
                dropOverlay.style.display = 'none';
                card.style.borderColor = '';
                card.style.boxShadow = '';
                const file = e.dataTransfer.files && e.dataTransfer.files[0];
                if (file) uploadFile(file);
            });

            // Вставка из буфера обмена (Ctrl+V на скриншот)
            card.addEventListener('paste', (e) => {
                const items = e.clipboardData && e.clipboardData.items;
                if (!items) return;
                for (const item of items) {
                    if (item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        if (file) uploadFile(file);
                        break;
                    }
                }
            });
            card.tabIndex = 0;

            // Глобально, чтобы старый обработчик cover-file тоже мог использовать
            window.uploadCoverFile = uploadFile;

            // === Удаление обложки ===
            const removeBtn = $('cp-remove');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (!coverInput.value) return;
                    if (!confirm('Удалить обложку?')) return;
                    coverInput.value = '';
                    if (fileInput) fileInput.value = '';
                    coverInput.dispatchEvent(new Event('input', { bubbles: true }));
                    refresh();
                });
            }
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
        if (typeof window.refreshCardPreview === 'function') window.refreshCardPreview();
    } else {
        alert(data.error || 'Ошибка');
    }
});
</script>

<!-- Модалка галереи медиа -->
<div id="media-modal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.55); align-items:center; justify-content:center; padding:20px;">
    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; width:100%; max-width:880px; max-height:88vh; display:flex; flex-direction:column; overflow:hidden;">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.25rem; border-bottom:1px solid var(--border);">
            <div style="font-size:1.05rem; font-weight:600;">Выберите изображение</div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <input type="search" id="media-search" placeholder="Поиск по имени..." style="background:var(--secondary); border:none; outline:none; border-radius:8px; padding:8px 12px; font-size:13px;">
                <button type="button" class="btn" id="media-upload-btn">+ Загрузить новое</button>
                <button type="button" class="btn" id="media-close" aria-label="Закрыть">✕</button>
            </div>
        </div>
        <div id="media-body" style="padding:1rem 1.25rem; overflow-y:auto; flex:1; min-height:200px;">
            <div id="media-loading" style="text-align:center; padding:2rem; color:var(--muted);">Загрузка...</div>
            <div id="media-empty" style="display:none; text-align:center; padding:2rem; color:var(--muted);">Пока нет загруженных изображений. Загрузите первое — оно появится здесь.</div>
            <div id="media-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:10px;"></div>
        </div>
        <div style="padding:0.75rem 1.25rem; border-top:1px solid var(--border); font-size:12px; color:var(--muted); display:flex; justify-content:space-between; align-items:center;">
            <span id="media-count"></span>
            <span>Esc — закрыть</span>
        </div>
    </div>
</div>
<style>
.media-tile { position:relative; aspect-ratio:1/1; border-radius:8px; overflow:hidden; cursor:pointer; background:var(--secondary); border:2px solid transparent; transition:border-color 0.15s, transform 0.15s; }
.media-tile:hover { border-color:var(--accent); }
.media-tile.is-selected { border-color:var(--accent); }
.media-tile img { width:100%; height:100%; object-fit:cover; display:block; }
.media-tile-meta { position:absolute; left:0; right:0; bottom:0; padding:6px 8px; background:linear-gradient(to top, rgba(0,0,0,0.75), transparent); color:#fff; font-size:11px; line-height:1.3; opacity:0; transition:opacity 0.15s; pointer-events:none; }
.media-tile:hover .media-tile-meta { opacity:1; }
</style>
<script>
(function(){
    const modal = document.getElementById('media-modal');
    const openBtn = document.getElementById('media-gallery-btn');
    const closeBtn = document.getElementById('media-close');
    const grid = document.getElementById('media-grid');
    const loading = document.getElementById('media-loading');
    const empty = document.getElementById('media-empty');
    const search = document.getElementById('media-search');
    const counter = document.getElementById('media-count');
    const uploadBtn = document.getElementById('media-upload-btn');
    const coverInput = document.getElementById('cover-url');
    const fileInput = document.getElementById('cover-file');

    let allItems = [];
    let loaded = false;

    function fmtSize(b) {
        if (b < 1024) return b + ' Б';
        if (b < 1024*1024) return (b/1024).toFixed(1) + ' КБ';
        return (b/1024/1024).toFixed(1) + ' МБ';
    }
    function fmtDate(ts) {
        const d = new Date(ts * 1000);
        return d.toLocaleDateString('ru-RU', { day:'2-digit', month:'2-digit', year:'numeric' });
    }

    function render(items) {
        grid.innerHTML = '';
        const current = (coverInput.value || '').trim();
        if (!items.length) {
            empty.style.display = 'block';
            counter.textContent = '';
            return;
        }
        empty.style.display = 'none';
        counter.textContent = items.length + ' изобр.';
        const frag = document.createDocumentFragment();
        items.forEach(it => {
            const tile = document.createElement('div');
            tile.className = 'media-tile' + (it.url === current ? ' is-selected' : '');
            tile.title = it.name;
            tile.innerHTML =
                '<img loading="lazy" src="' + it.url + '" alt="">' +
                '<div class="media-tile-meta">' + fmtDate(it.mtime) + ' · ' + fmtSize(it.size) + '</div>';
            tile.addEventListener('click', () => selectItem(it));
            frag.appendChild(tile);
        });
        grid.appendChild(frag);
    }

    function selectItem(it) {
        coverInput.value = it.url;
        coverInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof window.refreshCardPreview === 'function') window.refreshCardPreview();
        closeModal();
    }

    function applyFilter() {
        const q = (search.value || '').trim().toLowerCase();
        if (!q) return render(allItems);
        render(allItems.filter(it => it.name.toLowerCase().includes(q)));
    }

    async function loadMedia(force) {
        if (loaded && !force) return;
        loading.style.display = 'block';
        empty.style.display = 'none';
        grid.innerHTML = '';
        try {
            const r = await fetch('<?= BASE_URL ?>?route=admin/media-list');
            const d = await r.json();
            allItems = (d && d.items) || [];
            loaded = true;
            applyFilter();
        } catch (e) {
            empty.textContent = 'Не удалось загрузить список';
            empty.style.display = 'block';
        } finally {
            loading.style.display = 'none';
        }
    }

    function openModal() {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        loadMedia();
        setTimeout(() => search.focus(), 100);
    }
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
    search.addEventListener('input', applyFilter);

    // Кнопка "Загрузить новое" внутри модалки — открывает файловый диалог,
    // после успешной загрузки обновляем список
    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        // Если модалка открыта — перезагрузим список после короткой задержки
        if (modal.style.display === 'flex') {
            setTimeout(() => loadMedia(true), 1500);
        }
    });
})();
</script>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';