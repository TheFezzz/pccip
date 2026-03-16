<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

$stmt = $pdo->query('SELECT id, title, description, event_date, location, lat, lng, type FROM events WHERE lat IS NOT NULL AND lng IS NOT NULL ORDER BY event_date ASC, id ASC');
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$typeLabels = [
    'massacre' => 'Карательная акция',
    'camp'     => 'Лагерь',
    'village'  => 'Сожжённая деревня',
    'other'    => 'Другое',
];
$typeColors = [
    'massacre' => '#e53935',
    'camp'     => '#fb8c00',
    'village'  => '#43a047',
    'other'    => '#1e88e5',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карта событий</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            background:#0e0e0e;color:#f0f0f0;min-height:100vh;
        }

        .page-header{
            background:linear-gradient(180deg,rgba(30,30,30,.96),rgba(14,14,14,.98));
            border-bottom:1px solid #262626;
            padding:16px 24px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
        }
        .page-header-left{display:flex;align-items:center;gap:14px;}
        .nav-link{
            display:inline-flex;align-items:center;gap:6px;
            padding:8px 16px;border-radius:999px;
            background:#1e1e1e;border:1px solid #333;
            color:#e0e0e0;text-decoration:none;font-size:13px;font-weight:500;
            transition:all .15s;
        }
        .nav-link:hover{background:#ff5722;border-color:#ff5722;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,87,34,.4);}
        .page-title{font-size:20px;font-weight:700;color:#fff;}
        .page-title span{color:#ff5722;}

        .page-stats{font-size:13px;color:#9e9e9e;display:flex;align-items:center;gap:14px;}
        .page-stats strong{color:#ff5722;font-size:15px;}

        .controls-row{display:flex;align-items:center;gap:8px;}
        .ctrl-btn{
            padding:7px 14px;border-radius:999px;border:1px solid #333;
            background:#1a1a1a;color:#ccc;font-size:12px;cursor:pointer;text-decoration:none;
            transition:all .15s;
        }
        .ctrl-btn:hover{background:#ff5722;border-color:#ff5722;color:#fff;}

        .container{max-width:1200px;margin:0 auto;padding:20px 20px 40px;}

        #map{
            width:100%;height:560px;
            border-radius:14px;border:1px solid #262626;
            box-shadow:0 8px 24px rgba(0,0,0,.5);
        }

        .legend{
            display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;
            justify-content:center;
        }
        .legend-item{
            display:flex;align-items:center;gap:6px;font-size:13px;color:#ccc;
        }
        .legend-dot{
            width:12px;height:12px;border-radius:50%;flex-shrink:0;
        }

        .events-list{
            margin-top:24px;
            display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
            gap:14px;
        }
        .event-card{
            background:#181818;border:1px solid #262626;border-radius:12px;
            padding:16px 14px;transition:transform .12s,box-shadow .15s;
            cursor:pointer;position:relative;overflow:hidden;
        }
        .event-card::before{
            content:'';position:absolute;top:0;left:0;right:0;height:3px;
            opacity:0;transition:opacity .15s;
        }
        .event-card:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,0,0,.6);}
        .event-card:hover::before{opacity:1;}
        .event-card .ec-title{font-size:14px;font-weight:600;margin-bottom:4px;color:#fff;}
        .event-card .ec-date{font-size:11px;color:#888;margin-bottom:6px;}
        .event-card .ec-desc{font-size:12px;color:#aaa;line-height:1.5;}
        .event-card .ec-type{
            display:inline-block;padding:2px 8px;border-radius:999px;
            font-size:10px;color:#fff;margin-bottom:6px;
        }

        .empty-state{text-align:center;padding:40px 20px;color:#666;}

        body.accessibility{background:#000!important;color:#fff!important;font-size:1.3em;line-height:1.7;}
        body.accessibility .event-card{background:#111!important;border-color:#555!important;}
        body.accessibility a{color:#0ff!important;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:18px 14px 40px;}
            #map{height:400px;border-radius:10px;}
            .events-list{grid-template-columns:repeat(auto-fill,minmax(220px,1fr));}
        }
        @media(max-width:480px){
            .page-title{font-size:15px;}
            .nav-link,.ctrl-btn{font-size:11px;padding:6px 10px;}
            #map{height:280px;}
            .events-list{grid-template-columns:1fr;}
            .event-card{padding:12px 10px;}
            .event-card .ec-title{font-size:13px;}
            .legend{gap:8px;flex-wrap:wrap;}
            .legend-item{font-size:12px;}
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="page-header-left">
        <a href="index.html" class="nav-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            На главную
        </a>
        <div class="page-title"><span>Карта</span> событий</div>
    </div>

    <div class="page-stats">
        Событий на карте: <strong><?= count($events) ?></strong>
    </div>

    <div class="controls-row">
        <a href="suggest_event.php" class="ctrl-btn" style="background:linear-gradient(135deg,#ff7043,#ff5722);border-color:#ff5722;color:#fff;font-weight:600;">+ Предложить событие</a>
        <button class="ctrl-btn" onclick="toggleAccessibility()" id="accessibilityBtn">Для слабовидящих</button>
        <div id="google_translate_element"></div>
    </div>
</div>

<div class="container">
    <?php if (!$events): ?>
        <div class="empty-state">
            <p>Пока нет событий на карте. Добавьте события через <a href="/admin.php?section=events" style="color:#ff5722">админ-панель</a> с указанием координат (широта и долгота).</p>
        </div>
        <div id="map"></div>
    <?php else: ?>
        <div id="map"></div>

        <div class="legend">
            <?php foreach ($typeLabels as $key => $label): ?>
                <div class="legend-item">
                    <span class="legend-dot" style="background:<?= $typeColors[$key] ?>"></span>
                    <?= e($label) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="events-list">
            <?php foreach ($events as $ev): ?>
                <div class="event-card" onclick="flyTo(<?= (float)$ev['lat'] ?>,<?= (float)$ev['lng'] ?>)" style="border-top:3px solid <?= $typeColors[$ev['type']] ?? '#1e88e5' ?>">
                    <span class="ec-type" style="background:<?= $typeColors[$ev['type']] ?? '#1e88e5' ?>"><?= e($typeLabels[$ev['type']] ?? $ev['type']) ?></span>
                    <div class="ec-title"><?= e($ev['title']) ?></div>
                    <?php if ($ev['event_date']): ?>
                        <div class="ec-date"><?= e($ev['event_date']) ?></div>
                    <?php endif; ?>
                    <?php if ($ev['location']): ?>
                        <div class="ec-date"><?= e($ev['location']) ?></div>
                    <?php endif; ?>
                    <?php if ($ev['description']): ?>
                        <div class="ec-desc"><?= e(mb_substr($ev['description'], 0, 120, 'UTF-8')) ?><?= mb_strlen($ev['description']) > 120 ? '...' : '' ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([53.9, 27.5667], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var typeColors = <?= json_encode($typeColors) ?>;
var events = <?= json_encode(array_map(function($ev) use ($typeLabels) {
    return [
        'lat'   => (float)$ev['lat'],
        'lng'   => (float)$ev['lng'],
        'title' => $ev['title'],
        'desc'  => $ev['description'] ?? '',
        'date'  => $ev['event_date'] ?? '',
        'loc'   => $ev['location'] ?? '',
        'type'  => $ev['type'],
        'label' => $typeLabels[$ev['type']] ?? $ev['type'],
    ];
}, $events), JSON_UNESCAPED_UNICODE) ?>;

function createIcon(color) {
    return L.divIcon({
        className: '',
        html: '<div style="width:14px;height:14px;border-radius:50%;background:'+color+';border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.6);"></div>',
        iconSize: [14, 14],
        iconAnchor: [7, 7],
        popupAnchor: [0, -10],
    });
}

events.forEach(function(ev) {
    var color = typeColors[ev.type] || '#1e88e5';
    var popup = '<strong>' + ev.title + '</strong>';
    if (ev.label) popup += '<br><span style="color:'+color+';font-size:12px;">' + ev.label + '</span>';
    if (ev.date)  popup += '<br><small>' + ev.date + '</small>';
    if (ev.loc)   popup += '<br><small>' + ev.loc + '</small>';
    if (ev.desc)  popup += '<br><span style="font-size:12px;color:#666;">' + ev.desc.substring(0,200) + '</span>';

    L.marker([ev.lat, ev.lng], { icon: createIcon(color) })
        .addTo(map)
        .bindPopup(popup);
});

function flyTo(lat, lng) {
    map.flyTo([lat, lng], 13, { duration: 0.8 });
}
</script>

<script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'ru', includedLanguages: 'en,ru,be,uk,de,fr,pl',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
}
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script>
function toggleAccessibility() {
    document.body.classList.toggle('accessibility');
    var btn = document.getElementById('accessibilityBtn');
    btn.textContent = document.body.classList.contains('accessibility') ? 'Обычная версия' : 'Для слабовидящих';
}
</script>
</body>
</html>
