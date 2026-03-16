<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require_admin();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'html';

if (!in_array($type, ['victims', 'people'], true)) {
    header('Location: /admin.php');
    exit;
}

if ($type === 'victims') {
    $stmt = $pdo->query('SELECT surname, name, patronymic, birth_date, death_date, notes FROM victims ORDER BY surname, name, patronymic');
    $rows = $stmt->fetchAll();
    $title = 'Список жертв — Геноцид в Беларуси';
    $cols = ['Фамилия', 'Имя', 'Отчество', 'Дата рождения', 'Дата смерти', 'Примечание'];
} else {
    $stmt = $pdo->query('SELECT title, section, content, created_at FROM people ORDER BY id DESC');
    $rows = $stmt->fetchAll();
    $title = 'Истории очевидцев — Геноцид в Беларуси';
    $cols = ['Заголовок', 'Очевидец', 'Текст', 'Дата'];
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ($type === 'victims' ? 'victims' : 'stories') . '_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8

    if ($type === 'victims') {
        fputcsv($out, $cols, ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['surname'] ?? '',
                $r['name'] ?? '',
                $r['patronymic'] ?? '',
                $r['birth_date'] ?? '',
                $r['death_date'] ?? '',
                $r['notes'] ?? '',
            ], ';');
        }
    } else {
        fputcsv($out, ['Заголовок', 'Очевидец', 'Дата'], ';');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['title'] ?? '',
                $r['section'] ?? '',
                $r['created_at'] ?? '',
            ], ';');
        }
    }
    fclose($out);
    exit;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= e($title) ?></title>
    <style>
        @media print {
            body { background:#fff !important; color:#000 !important; }
            .no-print { display:none !important; }
            .print-header { margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #333; }
            table { width:100%; border-collapse:collapse; font-size:11px; }
            th, td { border:1px solid #333; padding:6px 8px; text-align:left; }
            th { background:#f0f0f0; font-weight:700; }
            tr:nth-child(even) td { background:#f9f9f9; }
            .content-cell { max-width:300px; }
        }
        body {
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            background:#0e0e0e; color:#f0f0f0; padding:24px; max-width:1000px; margin:0 auto;
        }
        .print-header { margin-bottom:24px; }
        .print-header h1 { font-size:22px; margin:0 0 4px; color:#ff5722; }
        .print-header p { font-size:13px; color:#999; margin:0; }
        .no-print {
            margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap;
        }
        .btn {
            padding:10px 18px; border-radius:999px; border:none; cursor:pointer;
            font-size:13px; font-weight:600; text-decoration:none; display:inline-block;
            transition:all .15s;
        }
        .btn-primary {
            background:linear-gradient(135deg,#ff7043,#ff5722); color:#fff;
        }
        .btn-primary:hover { filter:brightness(1.06); box-shadow:0 6px 16px rgba(255,87,34,.5); }
        .btn-secondary {
            background:#333; color:#ccc; border:1px solid #444;
        }
        .btn-secondary:hover { background:#444; color:#fff; }
        table {
            width:100%; border-collapse:collapse; font-size:13px;
            background:#181818; border:1px solid #262626; border-radius:12px; overflow:hidden;
        }
        th, td { border:1px solid #2a2a2a; padding:10px 12px; text-align:left; }
        th { background:#1e1e1e; color:#ff5722; font-weight:600; }
        tr:nth-child(even) td { background:#151515; }
        .content-cell { max-width:320px; font-size:12px; line-height:1.5; }
    </style>
</head>
<body>

<div class="no-print">
    <a href="/admin.php" class="btn btn-secondary">← В админ-панель</a>
    <button class="btn btn-primary" onclick="window.print()">Печать / Сохранить в PDF</button>
    <a href="?type=<?= e($type) ?>&format=csv" class="btn btn-secondary">Скачать CSV (Excel)</a>
</div>

<div class="print-header">
    <h1><?= e($title) ?></h1>
    <p>Дата экспорта: <?= date('d.m.Y H:i') ?></p>
</div>

<?php if ($type === 'victims'): ?>
<table>
    <thead>
        <tr>
            <?php foreach ($cols as $c): ?>
                <th><?= e($c) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['surname'] ?? '') ?></td>
                <td><?= e($r['name'] ?? '') ?></td>
                <td><?= e($r['patronymic'] ?? '') ?></td>
                <td><?= e($r['birth_date'] ?? '') ?></td>
                <td><?= e($r['death_date'] ?? '') ?></td>
                <td class="content-cell"><?= e(mb_substr($r['notes'] ?? '', 0, 200, 'UTF-8')) ?><?= mb_strlen($r['notes'] ?? '') > 200 ? '...' : '' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Заголовок</th>
            <th>Очевидец</th>
            <th>Текст (начало)</th>
            <th>Дата</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= e($r['title'] ?? '') ?></td>
                <td><?= e($r['section'] ?? '') ?></td>
                <td class="content-cell"><?= e(mb_substr($r['content'] ?? '', 0, 150, 'UTF-8')) ?>...</td>
                <td><?= e($r['created_at'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>
