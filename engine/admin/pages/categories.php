<?php
ob_start();
$msg = $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::checkCsrf($_POST['csrf'] ?? '')) {
    $data = [
        'key' => Storage::slug($_POST['key'] ?? $_POST['name'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];
    if (!empty($_POST['_oldKey'])) $data['_oldKey'] = $_POST['_oldKey'];
    if (empty($data['name'])) { $err = 'Введите название'; }
    else { Categories::save($data); header('Location: '.BASE_URL.'?route=admin/categories&saved=1'); exit; }
}

if (($_GET['delete'] ?? '') !== '' && Auth::checkCsrf($_GET['csrf'] ?? '')) {
    Categories::delete($_GET['delete']);
    header('Location: '.BASE_URL.'?route=admin/categories'); exit;
}

$editKey = $_GET['edit'] ?? '';
$editCat = $editKey ? Categories::get($editKey) : null;
$cats = Categories::all();
?>
<div class="admin-header"><h1>Категории</h1></div>

<?php if (!empty($_GET['saved'])): ?><div class="alert alert-success">Сохранено</div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?= $err ?></div><?php endif; ?>

<div class="admin-card">
    <h2 style="font-size:1.1rem;margin-bottom:1rem;"><?= $editCat ? 'Редактирование' : 'Новая категория' ?></h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= Auth::csrf() ?>">
        <?php if ($editCat): ?><input type="hidden" name="_oldKey" value="<?= htmlspecialchars($editCat['key']) ?>"><?php endif; ?>
        <div class="form-row"><label>Название *</label><input type="text" name="name" value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" required></div>
        <div class="form-row"><label>Ключ (URL)</label><input type="text" name="key" value="<?= htmlspecialchars($editCat['key'] ?? '') ?>" placeholder="авто из названия"></div>
        <div class="form-row"><label>Описание</label><textarea name="description"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea></div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editCat ? 'Сохранить' : 'Создать' ?></button>
            <?php if ($editCat): ?><a href="<?= BASE_URL ?>?route=admin/categories" class="btn">Отмена</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Название</th><th>Ключ</th><th>Статей</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($cats as $c): $count = count(Posts::byCategory($c['key'])); ?>
            <tr>
                <td><?= htmlspecialchars($c['name']) ?></td>
                <td><code><?= htmlspecialchars($c['key']) ?></code></td>
                <td><?= $count ?></td>
                <td>
                    <a href="?route=admin/categories&edit=<?= urlencode($c['key']) ?>">Изменить</a> ·
                    <a href="?route=admin/categories&delete=<?= urlencode($c['key']) ?>&csrf=<?= Auth::csrf() ?>" onclick="return confirm('Удалить?')" style="color:#ef4444">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
