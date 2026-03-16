<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

function h($str): string {
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

$surname    = trim($_GET['surname'] ?? '');
$name       = trim($_GET['name'] ?? '');
$patronymic = trim($_GET['patronymic'] ?? '');
$birthDate  = trim($_GET['birth_date'] ?? '');
$deathDate  = trim($_GET['death_date'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 30;

$where  = [];
$params = [];

if ($surname !== '') {
    $where[]           = "surname LIKE :surname";
    $params[':surname'] = "%{$surname}%";
}
if ($name !== '') {
    $where[]        = "name LIKE :name";
    $params[':name'] = "%{$name}%";
}
if ($patronymic !== '') {
    $where[]              = "patronymic LIKE :patronymic";
    $params[':patronymic'] = "%{$patronymic}%";
}
if ($birthDate !== '') {
    $where[]              = "birth_date LIKE :birth_date";
    $params[':birth_date'] = "{$birthDate}%";
}
if ($deathDate !== '') {
    $where[]              = "death_date LIKE :death_date";
    $params[':death_date'] = "{$deathDate}%";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM victims {$whereSQL}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$offset     = ($page - 1) * $perPage;
$totalPages = (int) ceil($total / $perPage);

$sql  = "SELECT * FROM victims {$whereSQL} ORDER BY surname, name, patronymic LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Стена памяти</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            background:#0e0e0e;
            color:#f0f0f0;
            min-height:100vh;
        }

        .page-header{
            background:linear-gradient(180deg,rgba(30,30,30,.96) 0%,rgba(14,14,14,.98) 100%);
            border-bottom:1px solid #262626;
            padding:18px 24px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            flex-wrap:wrap;
            gap:12px;
        }
        .page-header-left{display:flex;align-items:center;gap:14px;}
        .home-link{
            display:inline-flex;align-items:center;gap:6px;
            padding:8px 16px;border-radius:999px;
            background:#1e1e1e;border:1px solid #333;
            color:#e0e0e0;text-decoration:none;font-size:13px;font-weight:500;
            transition:all .15s;
        }
        .home-link:hover{background:#ff5722;border-color:#ff5722;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,87,34,.4);}
        .page-title{font-size:20px;font-weight:700;color:#fff;}
        .page-title span{color:#ff5722;}

        .page-stats{
            display:flex;align-items:center;gap:18px;
            font-size:13px;color:#9e9e9e;
        }
        .page-stats strong{color:#ff5722;font-size:15px;}

        .controls-row{
            display:flex;align-items:center;gap:8px;
        }
        .ctrl-btn{
            padding:7px 14px;border-radius:999px;border:1px solid #333;
            background:#1a1a1a;color:#ccc;font-size:12px;cursor:pointer;
            text-decoration:none;transition:all .15s;
        }
        .ctrl-btn:hover{background:#ff5722;border-color:#ff5722;color:#fff;}

        .container{max-width:1100px;margin:0 auto;padding:24px 20px 60px;}

        .search-card{
            background:#181818;border:1px solid #262626;border-radius:14px;
            padding:20px 22px;margin-bottom:28px;
            box-shadow:0 6px 20px rgba(0,0,0,.5);
        }
        .search-card h2{font-size:15px;margin-bottom:14px;color:#bbb;font-weight:500;}
        .search-grid{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(170px,1fr));
            gap:10px;
        }
        .search-input{
            padding:9px 12px;border-radius:10px;
            border:1px solid #333;background:#111;color:#f0f0f0;
            font-size:13px;outline:none;width:100%;
            transition:border-color .18s,box-shadow .18s;
        }
        .search-input:focus{border-color:#ff5722;box-shadow:0 0 0 2px rgba(255,87,34,.25);}
        .search-input::placeholder{color:#666;}
        .search-btn{
            padding:9px 24px;border:none;border-radius:10px;
            background:linear-gradient(135deg,#ff7043,#ff5722);
            color:#fff;font-size:13px;font-weight:600;cursor:pointer;
            text-transform:uppercase;letter-spacing:.04em;
            transition:transform .1s,box-shadow .12s,filter .12s;
        }
        .search-btn:hover{filter:brightness(1.06);box-shadow:0 8px 18px rgba(255,87,34,.5);transform:translateY(-1px);}

        .wall{
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(200px,1fr));
            gap:14px;
        }
        .wall-card{
            background:#181818;border:1px solid #262626;border-radius:12px;
            padding:16px 14px;
            transition:transform .12s,box-shadow .15s,border-color .15s;
            position:relative;overflow:hidden;
        }
        .wall-card::before{
            content:'';position:absolute;top:0;left:0;right:0;height:3px;
            background:linear-gradient(90deg,#ff5722,#ff9800);
            opacity:0;transition:opacity .15s;
        }
        .wall-card:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(0,0,0,.6);border-color:#444;}
        .wall-card:hover::before{opacity:1;}
        .wall-card .card-name{font-size:14px;font-weight:600;margin-bottom:6px;line-height:1.3;}
        .wall-card .card-surname{color:#ff5722;}
        .wall-card .card-dates{font-size:11px;color:#888;margin-top:6px;}
        .wall-card .card-notes{font-size:11px;color:#aaa;margin-top:6px;line-height:1.4;}

        .empty-state{
            text-align:center;padding:60px 20px;color:#666;
        }
        .empty-state p{font-size:15px;margin-top:8px;}

        .pagination{
            display:flex;flex-wrap:wrap;justify-content:center;
            gap:6px;margin-top:30px;
        }
        .pagination a,.pagination span{
            display:inline-flex;align-items:center;justify-content:center;
            min-width:36px;height:36px;padding:0 10px;
            border:1px solid #333;border-radius:10px;
            font-size:13px;color:#ccc;text-decoration:none;
            background:#181818;transition:all .15s;
        }
        .pagination a:hover{background:#ff5722;border-color:#ff5722;color:#fff;}
        .pagination span.current{background:#ff5722;border-color:#ff5722;color:#fff;font-weight:700;box-shadow:0 4px 12px rgba(255,87,34,.5);}

        .accessibility-bar{
            display:flex;align-items:center;gap:8px;
        }

        body.accessibility{background:#000!important;color:#fff!important;font-size:1.3em;line-height:1.7;}
        body.accessibility .wall-card{background:#111!important;border-color:#555!important;}
        body.accessibility a{color:#0ff!important;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:18px 14px 40px;}
            .search-card{padding:16px;}
            .search-grid{grid-template-columns:repeat(auto-fill,minmax(140px,1fr));}
            .wall{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));}
        }
        @media(max-width:480px){
            .page-title{font-size:15px;}
            .home-link,.ctrl-btn{font-size:11px;padding:6px 10px;}
            .search-grid{grid-template-columns:1fr;}
            .wall{grid-template-columns:1fr 1fr;}
            .wall-card{padding:12px 10px;}
            .wall-card .card-name{font-size:13px;}
            .pagination a,.pagination span{min-width:30px;height:30px;font-size:11px;}
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="page-header-left">
        <a href="index.html" class="home-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            На главную
        </a>
        <div class="page-title"><span>Стена</span> памяти</div>
    </div>

    <div class="page-stats">
        Найдено: <strong><?= number_format($total, 0, '', ' ') ?></strong> записей
        <?php if ($totalPages > 1): ?>
            &middot; Страница <?= $page ?> из <?= $totalPages ?>
        <?php endif; ?>
    </div>

    <div class="controls-row">
        <div class="accessibility-bar">
            <button class="ctrl-btn" onclick="toggleAccessibility()" id="accessibilityBtn">Для слабовидящих</button>
            <div id="google_translate_element"></div>
        </div>
    </div>
</div>

<div class="container">
    <div class="search-card">
        <h2>Поиск по списку жертв</h2>
        <form method="get">
            <div class="search-grid">
                <input class="search-input" type="text" name="surname"    placeholder="Фамилия"          value="<?= h($surname) ?>">
                <input class="search-input" type="text" name="name"       placeholder="Имя"               value="<?= h($name) ?>">
                <input class="search-input" type="text" name="patronymic" placeholder="Отчество"          value="<?= h($patronymic) ?>">
                <input class="search-input" type="text" name="birth_date" placeholder="Дата рождения"     value="<?= h($birthDate) ?>">
                <input class="search-input" type="text" name="death_date" placeholder="Дата смерти"       value="<?= h($deathDate) ?>">
                <button class="search-btn" type="submit">Найти</button>
            </div>
        </form>
    </div>

    <?php if (!$results): ?>
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <p>По вашему запросу ничего не найдено</p>
        </div>
    <?php else: ?>
        <div class="wall">
            <?php foreach ($results as $row): ?>
                <div class="wall-card">
                    <div class="card-name">
                        <span class="card-surname"><?= h(mb_convert_case($row['surname'] ?? '', MB_CASE_TITLE, 'UTF-8')) ?></span>
                        <?= h(mb_convert_case($row['name'] ?? '', MB_CASE_TITLE, 'UTF-8')) ?>
                        <?= h(mb_convert_case($row['patronymic'] ?? '', MB_CASE_TITLE, 'UTF-8')) ?>
                    </div>
                    <?php
                        $dates = [];
                        $birthLabel = trim((string)($row['birth_date'] ?? ''));
                        if ($birthLabel === '') {
                            $birthLabel = 'Неизвестно';
                        }
                        $dates[] = $birthLabel;
                        if (!empty($row['death_date'])) {
                            $dates[] = $row['death_date'];
                        }
                    ?>
                    <?php if ($dates): ?>
                        <div class="card-dates"><?= h(implode(' — ', $dates)) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($row['notes'])): ?>
                        <div class="card-notes"><?= h(mb_substr($row['notes'], 0, 120, 'UTF-8')) ?><?= mb_strlen($row['notes'] ?? '') > 120 ? '...' : '' ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
                <?php endif; ?>
                <?php
                    $start = max(1, $page - 4);
                    $end   = min($totalPages, $page + 4);
                    for ($p = $start; $p <= $end; $p++):
                ?>
                    <?php if ($p === $page): ?>
                        <span class="current"><?= $p ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'ru',
      includedLanguages: 'en,ru,be,uk,de,fr,pl',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script>
function toggleAccessibility() {
    document.body.classList.toggle('accessibility');
    const btn = document.getElementById('accessibilityBtn');
    btn.textContent = document.body.classList.contains('accessibility') ? 'Обычная версия' : 'Для слабовидящих';
}
</script>
</body>
</html>
