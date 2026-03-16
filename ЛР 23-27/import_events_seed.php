<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require_admin();

// Набор реальных событий, связанных с геноцидом и массовыми карательными акциями в Беларуси
$seedEvents = [
    [
        'title'       => 'Сожжение деревни Хатынь',
        'description' => "22 марта 1943 года карательный отряд уничтожил деревню Хатынь Логойского района Минской области. Живьём были сожжены или расстреляны 149 жителей, из них 75 детей. Хатынь стала символом трагедии сожжённых белорусских деревень во время нацистской оккупации.",
        'event_date'  => '1943-03-22',
        'location'    => 'Деревня Хатынь, Логойский район, Минская область',
        'lat'         => 54.3278,
        'lng'         => 27.9619,
        'type'        => 'village',
    ],
    [
        'title'       => 'Нацистский лагерь смерти «Озаричи»',
        'description' => "В марте 1944 года в районе Озаричей немецкие войска создали крупный лагерь смерти под открытым небом. Туда согнали десятки тысяч мирных жителей, в том числе женщин, детей и стариков. Людей держали без укрытий, пищи и медицинской помощи, многие погибали от холода, голода и эпидемий.",
        'event_date'  => '1944-03-15',
        'location'    => 'Район Озаричей, Гомельская область',
        'lat'         => 52.2130,
        'lng'         => 28.6240,
        'type'        => 'camp',
    ],
    [
        'title'       => 'Карательная акция в деревне Борки',
        'description' => "15 июня 1942 года немецкие каратели и их пособники уничтожили деревню Борки в Могилёвской области. Были убиты сотни мирных жителей, дома и хозяйства сожжены. Судьба Борок стала одной из множества трагедий белорусских деревень в годы нацистской оккупации.",
        'event_date'  => '1942-06-15',
        'location'    => 'Деревня Борки, Кировский район, Могилёвская область',
        'lat'         => 53.8420,
        'lng'         => 30.3010,
        'type'        => 'massacre',
    ],
    [
        'title'       => 'Сожжение деревни Красный Берег',
        'description' => "Во время карательных операций нацисты уничтожили деревню Красный Берег в Жлобинском районе. Часть жителей была расстреляна, другие сожжены в домах, оставшиеся угнаны на принудительные работы. Сегодня Красный Берег — один из важных мемориальных комплексов, посвящённых детям-жертвам войны.",
        'event_date'  => '1943-06-01',
        'location'    => 'Красный Берег, Жлобинский район, Гомельская область',
        'lat'         => 52.9383,
        'lng'         => 30.0394,
        'type'        => 'village',
    ],
];

$inserted = 0;
$skipped  = 0;

foreach ($seedEvents as $ev) {
    // Проверяем, нет ли уже такого события (по названию и дате)
    $check = $pdo->prepare('SELECT id FROM events WHERE title = :title AND event_date <=> :event_date LIMIT 1');
    $check->execute([
        ':title'      => $ev['title'],
        ':event_date' => $ev['event_date'],
    ]);

    if ($check->fetch()) {
        $skipped++;
        continue;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO events (title, description, event_date, location, lat, lng, type)
         VALUES (:title, :description, :event_date, :location, :lat, :lng, :type)'
    );

    $stmt->execute([
        ':title'       => $ev['title'],
        ':description' => $ev['description'],
        ':event_date'  => $ev['event_date'],
        ':location'    => $ev['location'],
        ':lat'         => $ev['lat'],
        ':lng'         => $ev['lng'],
        ':type'        => $ev['type'],
    ]);

    $inserted++;
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Импорт событий карты</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: #0e0e0e;
            color: #f0f0f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #181818;
            border-radius: 14px;
            border: 1px solid #262626;
            padding: 24px 28px;
            max-width: 520px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,.7);
        }
        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        p {
            margin: 6px 0;
            font-size: 14px;
            color: #d0d0d0;
        }
        .stats {
            margin: 14px 0 18px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #111;
            border: 1px solid #333;
            font-size: 13px;
        }
        .stats strong {
            color: #ff5722;
        }
        .links {
            margin-top: 10px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid #333;
            background: #1a1a1a;
            color: #e0e0e0;
            text-decoration: none;
            font-size: 13px;
            transition: all .15s;
        }
        .btn-link:hover {
            background: #ff5722;
            border-color: #ff5722;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(255,87,34,.45);
        }
    </style>
</head>
<body>
<div class="card">
    <h1>Импорт событий на карту</h1>
    <p>Скрипт добавил в таблицу <code>events</code> несколько исторически достоверных событий, связанных с геноцидом на территории Беларуси.</p>

    <div class="stats">
        <p><strong>Добавлено:</strong> <?= (int) $inserted ?> записей</p>
        <p><strong>Пропущено (уже были):</strong> <?= (int) $skipped ?> записей</p>
    </div>

    <p>Теперь эти события должны отображаться на странице карты событий.</p>

    <div class="links">
        <a class="btn-link" href="/2btn.php">Открыть карту событий</a>
        <a class="btn-link" href="/admin.php?section=events">Открыть раздел «События» в админ-панели</a>
        <a class="btn-link" href="/index.html">На главную</a>
    </div>
</div>
</body>
</html>

