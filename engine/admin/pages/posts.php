<?php
ob_start();

if (($_GET['delete'] ?? '') !== '' && Auth::checkCsrf($_GET['csrf'] ?? '')) {
    Posts::delete($_GET['delete']);
    header('Location: ' . BASE_URL . '?route=admin/posts'); exit;
}

$allPosts = Posts::all(false);
$now = time();
$filter = $_GET['filter'] ?? 'all';

// Распределяем по группам
$grouped = ['all'=>[], 'published'=>[], 'scheduled'=>[], 'drafts'=>[]];
foreach ($allPosts as $p) {
    $grouped['all'][] = $p;
    $isScheduled = !empty($p['published']) && !empty($p['publish_at']) && strtotime($p['publish_at']) > $now;
    if ($isScheduled) $grouped['scheduled'][] = $p;
    elseif (!empty($p['published'])) $grouped['published'][] = $p;
    else $grouped['drafts'][] = $p;
}
$posts = $grouped[$filter] ?? $grouped['all'];

// Сортировка: запланированные — по publish_at (ближайшие сверху), остальные — по дате создания
if ($filter === 'scheduled') {
    usort($posts, fn($a, $b) => strtotime($a['publish_at']) - strtotime($b['publish_at']));
}
?>
<div class="admin-header">
    <h1>Статьи</h1>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>?route=admin/import" class="btn">Импорт</a>
        <a href="<?= BASE_URL ?>?route=admin/post-edit" class="btn btn-primary">+ Новая статья</a>
    </div>
</div>

<div class="filter-bar" style="margin-bottom:1rem;">
    <a href="?route=admin/posts" class="filter-btn <?= $filter==='all'?'filter-btn-active':'' ?>">
        Все <span style="opacity:0.6;">(<?= count($grouped['all']) ?>)</span>
    </a>
    <a href="?route=admin/posts&filter=published" class="filter-btn <?= $filter==='published'?'filter-btn-active':'' ?>">
        ✓ Опубликованные <span style="opacity:0.6;">(<?= count($grouped['published']) ?>)</span>
    </a>
    <a href="?route=admin/posts&filter=scheduled" class="filter-btn <?= $filter==='scheduled'?'filter-btn-active':'' ?>">
        ⏰ Запланированные <span style="opacity:0.6;">(<?= count($grouped['scheduled']) ?>)</span>
    </a>
    <a href="?route=admin/posts&filter=drafts" class="filter-btn <?= $filter==='drafts'?'filter-btn-active':'' ?>">
        Черновики <span style="opacity:0.6;">(<?= count($grouped['drafts']) ?>)</span>
    </a>
</div>

<div class="admin-card">
    <table class="admin-table">
        <thead><tr><th>Название</th><th>Категория</th><th>Теги</th><th>Статус</th><th>Дата</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): $c = Categories::get($p['category'] ?? ''); ?>
            <tr>
                <td>
                    <?php if (!empty($p['sticky'])): ?><span title="Закреплено">📌</span> <?php endif; ?>
                    <a href="<?= BASE_URL ?>?route=admin/post-edit&id=<?= urlencode($p['id']) ?>"><?= htmlspecialchars($p['title']) ?></a>
                </td>
                <td><?= $c ? htmlspecialchars($c['name']) : '—' ?></td>
                <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars(implode(', ', array_slice($p['tags'] ?? [], 0, 3))) ?></td>
                <td>
                <?php
                $isScheduled = !empty($p['published']) && !empty($p['publish_at']) && strtotime($p['publish_at']) > time();
                if ($isScheduled):
                    $diff = strtotime($p['publish_at']) - time();
                    if ($diff < 3600) $when = 'через ' . max(1, (int)round($diff/60)) . ' мин.';
                    elseif ($diff < 86400) $when = 'через ' . (int)round($diff/3600) . ' ч.';
                    else $when = 'через ' . (int)round($diff/86400) . ' дн.';
                ?>
                    <span style="color:#f59e0b;" title="<?= htmlspecialchars(date('d.m.Y H:i', strtotime($p['publish_at']))) ?>">⏰ Запланировано (<?= $when ?>)</span>
                <?php elseif (!empty($p['published'])): ?>
                    <span style="color:#10b981">✓ Опубликовано</span>
                <?php else: ?>
                    <span style="color:var(--muted)">Черновик</span>
                <?php endif; ?>
                </td>
                <td>
                    <?php if ($isScheduled): ?>
                        <?= date('d.m.Y H:i', strtotime($p['publish_at'])) ?>
                    <?php else: ?>
                        <?= date('d.m.Y', strtotime($p['date'])) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= BASE_URL ?>?route=post/<?= urlencode($p['slug']) ?>" target="_blank">Открыть</a> ·
                    <a href="?route=admin/posts&delete=<?= urlencode($p['id']) ?>&csrf=<?= Auth::csrf() ?>" onclick="return confirm('Удалить статью?')" style="color:#ef4444">Удалить</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?>
            <tr><td colspan="6" style="color:var(--muted);text-align:center;padding:2rem">
                <?php if ($filter === 'scheduled'): ?>
                    Нет запланированных публикаций. Откройте любой пост и задайте дату публикации.
                <?php elseif ($filter === 'drafts'): ?>
                    Нет черновиков. Все статьи опубликованы.
                <?php elseif ($filter === 'published'): ?>
                    Нет опубликованных статей.
                <?php else: ?>
                    Статей пока нет. <a href="<?= BASE_URL ?>?route=admin/post-edit">Создать первую</a>
                <?php endif; ?>
            </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';