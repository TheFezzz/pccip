<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require_admin();

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$jsonPath = __DIR__ . '/../python/victims.json';
$total = $inserted = $skippedDup = 0;
$error = '';

if (!is_file($jsonPath)) {
    $error = 'Файл victims.json не найден по пути: ' . $jsonPath;
} else {
    try {
        $raw = file_get_contents($jsonPath);
        if ($raw === false) {
            throw new RuntimeException('Не удалось прочитать victims.json');
        }

        /** @var array<int,array<string,mixed>> $data */
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data)) {
            throw new RuntimeException('Некорректный формат JSON');
        }

        $insert = $pdo->prepare(
            'INSERT INTO victims (surname, name, patronymic, birth_date, death_date, notes)
             VALUES (:surname, :name, :patronymic, :birth_date, :death_date, :notes)'
        );

        $exists = $pdo->prepare(
            'SELECT COUNT(*) FROM victims
             WHERE surname = :surname
               AND name = :name
               AND patronymic = :patronymic
               AND birth_date = :birth_date
               AND death_date = :death_date'
        );

        $total = count($data);

        foreach ($data as $row) {
            if (!is_array($row)) {
                continue;
            }

            $fio = trim((string)($row['fio'] ?? ''));
            if ($fio === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $fio);
            $surname    = $parts[0] ?? '';
            $name       = $parts[1] ?? '';
            $patronymic = '';
            if (count($parts) > 2) {
                $patronymic = implode(' ', array_slice($parts, 2));
            }

            $birthYearRaw = trim((string)($row['birth_year'] ?? ''));
            $deathDateRaw = trim((string)($row['death_date'] ?? ''));

            // Ограничиваем длину под размер VARCHAR(20) и обрабатываем пустые значения
            if ($birthYearRaw === '') {
                $birthYear = 'Неизвестно';
            } else {
                $birthYear = mb_substr($birthYearRaw, 0, 20, 'UTF-8');
            }

            $deathDate = $deathDateRaw === ''
                ? ''
                : mb_substr($deathDateRaw, 0, 20, 'UTF-8');

            $rank       = trim((string)($row['rank_unit'] ?? ''));
            $burial     = trim((string)($row['burial_place'] ?? ''));
            $settlement = trim((string)($row['settlement'] ?? ''));

            $notesParts = [];
            if ($rank !== '') {
                $notesParts[] = "Звание/часть: {$rank}";
            }
            if ($burial !== '') {
                $notesParts[] = "Место захоронения: {$burial}";
            }
            if ($settlement !== '') {
                $notesParts[] = "Населённый пункт: {$settlement}";
            }
            $notes = implode(' | ', $notesParts);

            $exists->execute([
                ':surname'    => $surname,
                ':name'       => $name,
                ':patronymic' => $patronymic,
                ':birth_date' => $birthYear,
                ':death_date' => $deathDate,
            ]);

            if ((int)$exists->fetchColumn() > 0) {
                $skippedDup++;
                continue;
            }

            $insert->execute([
                ':surname'    => $surname,
                ':name'       => $name,
                ':patronymic' => $patronymic,
                ':birth_date' => $birthYear,
                ':death_date' => $deathDate,
                ':notes'      => $notes,
            ]);

            $inserted++;
        }
    } catch (Throwable $e) {
        $error = 'Ошибка импорта: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Импорт жертв из JSON</title>
    <style>
        body {
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            background:#0e0e0e;
            color:#f0f0f0;
            margin:0;
        }
        .wrap {
            max-width:720px;
            margin:40px auto;
            padding:24px 22px;
            background:#181818;
            border-radius:16px;
            border:1px solid #262626;
            box-shadow:0 12px 32px rgba(0,0,0,.7);
        }
        h1 {
            margin:0 0 10px;
            font-size:22px;
            color:#ff5722;
        }
        p { margin:4px 0; font-size:14px; }
        .stats { margin-top:16px; font-size:14px; }
        .error {
            margin-top:12px;
            padding:10px 14px;
            border-radius:10px;
            background:rgba(183,28,28,.85);
            border:1px solid #ef5350;
            font-size:13px;
        }
        .ok {
            margin-top:12px;
            padding:10px 14px;
            border-radius:10px;
            background:rgba(46,125,50,.85);
            border:1px solid #66bb6a;
            font-size:13px;
        }
        .links { margin-top:18px; font-size:13px; }
        .links a {
            color:#ffb74d;
            text-decoration:none;
            margin-right:12px;
        }
        .links a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="wrap">
    <h1>Импорт жертв из JSON</h1>
    <p>Файл: <code><?= e($jsonPath) ?></code></p>

    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php else: ?>
        <div class="ok">
            Импорт завершён.
        </div>
        <div class="stats">
            <p>Всего записей в JSON: <strong><?= $total ?></strong></p>
            <p>Добавлено в базу: <strong><?= $inserted ?></strong></p>
            <p>Пропущено как дубли: <strong><?= $skippedDup ?></strong></p>
        </div>
    <?php endif; ?>

    <div class="links">
        <a href="/6btn.php">Перейти к стене памяти</a>
        <a href="/admin.php?section=victims">Открыть раздел «Жертвы» в админке</a>
    </div>
</div>
</body>
</html>

