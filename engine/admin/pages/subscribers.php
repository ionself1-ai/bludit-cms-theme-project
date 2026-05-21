<?php
ob_start();

// Удалить подписчика
if (($_GET['delete'] ?? '') !== '' && Auth::checkCsrf($_GET['csrf'] ?? '')) {
    Subscribers::delete($_GET['delete']);
    header('Location: ' . BASE_URL . '?route=admin/subscribers'); exit;
}

// Экспорт CSV
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM для Excel
    fputcsv($out, ['email', 'status', 'created_at', 'confirmed_at']);
    foreach (Subscribers::all() as $s) {
        $status = !empty($s['unsubscribed']) ? 'unsubscribed'
                : (!empty($s['confirmed']) ? 'confirmed' : 'pending');
        fputcsv($out, [
            $s['email'],
            $status,
            $s['created_at'] ?? '',
            $s['confirmed_at'] ?? '',
        ]);
    }
    fclose($out);
    exit;
}

$stats = Subscribers::stats();
$all = Subscribers::all();
$filter = $_GET['filter'] ?? 'all';
if ($filter === 'confirmed') $all = array_filter($all, fn($s) => !empty($s['confirmed']) && empty($s['unsubscribed']));
elseif ($filter === 'pending') $all = array_filter($all, fn($s) => empty($s['confirmed']) && empty($s['unsubscribed']));
elseif ($filter === 'unsub') $all = array_filter($all, fn($s) => !empty($s['unsubscribed']));
// Новые сверху
usort($all, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
?>
<div class="admin-header">
    <h1>Подписчики</h1>
    <a href="<?= BASE_URL ?>?route=admin/subscribers&export=csv" class="btn">Экспорт CSV</a>
</div>

<div class="admin-card" style="display:flex; gap:1rem; flex-wrap:wrap;">
    <div style="flex:1; min-width:120px; padding:1rem; background:var(--secondary); border-radius:8px;">
        <div style="font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Всего</div>
        <div style="font-size:1.75rem; font-weight:700; margin-top:4px;"><?= $stats['total'] ?></div>
    </div>
    <div style="flex:1; min-width:120px; padding:1rem; background:var(--secondary); border-radius:8px;">
        <div style="font-size:12px; color:#10b981; text-transform:uppercase; letter-spacing:0.05em;">Подтверждены</div>
        <div style="font-size:1.75rem; font-weight:700; margin-top:4px;"><?= $stats['confirmed'] ?></div>
    </div>
    <div style="flex:1; min-width:120px; padding:1rem; background:var(--secondary); border-radius:8px;">
        <div style="font-size:12px; color:#f59e0b; text-transform:uppercase; letter-spacing:0.05em;">Ожидают</div>
        <div style="font-size:1.75rem; font-weight:700; margin-top:4px;"><?= $stats['pending'] ?></div>
    </div>
    <div style="flex:1; min-width:120px; padding:1rem; background:var(--secondary); border-radius:8px;">
        <div style="font-size:12px; color:#ef4444; text-transform:uppercase; letter-spacing:0.05em;">Отписались</div>
        <div style="font-size:1.75rem; font-weight:700; margin-top:4px;"><?= $stats['unsubscribed'] ?></div>
    </div>
</div>

<div class="filter-bar" style="margin:1rem 0;">
    <a href="?route=admin/subscribers" class="filter-btn <?= $filter==='all'?'filter-btn-active':'' ?>">Все</a>
    <a href="?route=admin/subscribers&filter=confirmed" class="filter-btn <?= $filter==='confirmed'?'filter-btn-active':'' ?>">Подтверждены</a>
    <a href="?route=admin/subscribers&filter=pending" class="filter-btn <?= $filter==='pending'?'filter-btn-active':'' ?>">Ожидают</a>
    <a href="?route=admin/subscribers&filter=unsub" class="filter-btn <?= $filter==='unsub'?'filter-btn-active':'' ?>">Отписались</a>
</div>

<div class="admin-card">
    <?php if (empty($all)): ?>
        <p style="color:var(--muted); text-align:center; padding:2rem;">Подписчиков пока нет.</p>
    <?php else: ?>
        <table class="admin-table" style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid var(--border);">
                    <th style="padding:10px 8px;">Email</th>
                    <th style="padding:10px 8px;">Статус</th>
                    <th style="padding:10px 8px;">Подписался</th>
                    <th style="padding:10px 8px; text-align:right;">Действия</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($all as $s): ?>
                <?php
                $status = !empty($s['unsubscribed']) ? ['Отписался','#ef4444']
                        : (!empty($s['confirmed']) ? ['Подтверждён','#10b981'] : ['Ожидает','#f59e0b']);
                ?>
                <tr style="border-bottom:1px solid var(--border);">
                    <td style="padding:10px 8px; font-family:'SF Mono',Menlo,monospace; font-size:13px;"><?= htmlspecialchars($s['email']) ?></td>
                    <td style="padding:10px 8px;">
                        <span style="display:inline-block; padding:2px 8px; border-radius:999px; font-size:11.5px; font-weight:600; background:<?= $status[1] ?>22; color:<?= $status[1] ?>;"><?= $status[0] ?></span>
                    </td>
                    <td style="padding:10px 8px; color:var(--muted); font-size:13px;">
                        <?= !empty($s['created_at']) ? date('d.m.Y H:i', strtotime($s['created_at'])) : '—' ?>
                    </td>
                    <td style="padding:10px 8px; text-align:right;">
                        <a href="?route=admin/subscribers&delete=<?= urlencode($s['id']) ?>&csrf=<?= Auth::csrf() ?>"
                           onclick="return confirm('Удалить подписчика <?= htmlspecialchars($s['email']) ?>?')"
                           class="btn btn-danger" style="font-size:12px;">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php $body = ob_get_clean(); require __DIR__ . '/../layout.php';
