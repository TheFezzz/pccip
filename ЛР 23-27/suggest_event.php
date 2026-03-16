<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `event_suggestions` (
        `id`                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
        `suggested_by_name`  VARCHAR(255)  NOT NULL,
        `suggested_by_email` VARCHAR(255)  DEFAULT NULL,
        `title`              VARCHAR(255)  NOT NULL,
        `description`        TEXT          DEFAULT NULL,
        `event_date`         VARCHAR(30)   DEFAULT NULL,
        `location`           VARCHAR(255)  DEFAULT NULL,
        `lat`                DECIMAL(10,7) DEFAULT NULL,
        `lng`                DECIMAL(10,7) DEFAULT NULL,
        `type`               ENUM('massacre','camp','village','other') NOT NULL DEFAULT 'other',
        `user_id`            INT UNSIGNED  DEFAULT NULL,
        `status`             ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
        `reject_reason`      TEXT          DEFAULT NULL,
        `created_at`         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$message = '';
$errors  = [];

$typeLabels = [
    'massacre' => 'Карательная акция',
    'camp'     => 'Лагерь',
    'village'  => 'Сожжённая деревня',
    'other'    => 'Другое',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim((string)($_POST['name'] ?? ''));
    $email       = trim((string)($_POST['email'] ?? ''));
    $title       = trim((string)($_POST['title'] ?? ''));
    $description = trim((string)($_POST['description'] ?? ''));
    $eventDate   = trim((string)($_POST['event_date'] ?? ''));
    $location    = trim((string)($_POST['location'] ?? ''));
    $lat         = trim((string)($_POST['lat'] ?? ''));
    $lng         = trim((string)($_POST['lng'] ?? ''));
    $type        = trim((string)($_POST['type'] ?? 'other'));

    if ($name === '' || mb_strlen($name) < 2) {
        $errors[] = 'Укажите ваше имя (не менее 2 символов).';
    }
    if ($email === '') {
        $errors[] = 'Укажите ваш e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный e-mail.';
    }
    if ($title === '' || mb_strlen($title) < 5) {
        $errors[] = 'Название события должно содержать не менее 5 символов.';
    }
    if ($eventDate === '') {
        $errors[] = 'Укажите дату события (можно примерную).';
    }
    if ($location === '') {
        $errors[] = 'Укажите место / населённый пункт.';
    }
    if ($description === '' || mb_strlen($description) < 20) {
        $errors[] = 'Описание события должно быть не короче 20 символов.';
    }
    if ($lat === '') {
        $errors[] = 'Укажите широту (координату).';
    } elseif (!is_numeric($lat)) {
        $errors[] = 'Широта должна быть числом (например 53.9).';
    }
    if ($lng === '') {
        $errors[] = 'Укажите долготу (координату).';
    } elseif (!is_numeric($lng)) {
        $errors[] = 'Долгота должна быть числом (например 27.56).';
    }
    if (!isset($typeLabels[$type])) {
        $type = 'other';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO event_suggestions
                (suggested_by_name, suggested_by_email, title, description, event_date, location, lat, lng, type, user_id)
             VALUES (:n, :e, :t, :d, :ed, :loc, :lat, :lng, :tp, :u)'
        );
        $stmt->execute([
            ':n'   => $name,
            ':e'   => $email,
            ':t'   => $title,
            ':d'   => $description,
            ':ed'  => $eventDate,
            ':loc' => $location,
            ':lat' => (float)$lat,
            ':lng' => (float)$lng,
            ':tp'  => $type,
            ':u'   => $currentUser['id'] ?? null,
        ]);
        $message = 'Спасибо! Ваше событие отправлено на модерацию. После проверки администратора оно может появиться на карте.';
        $_POST = [];
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предложить событие на карту</title>
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
            display:flex;align-items:center;gap:14px;flex-wrap:wrap;
        }
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

        .container{max-width:640px;margin:0 auto;padding:36px 20px 60px;}

        .form-card{
            background:#181818;border:1px solid #262626;border-radius:16px;
            padding:28px 26px;box-shadow:0 10px 30px rgba(0,0,0,.6);
        }
        .form-card h2{font-size:18px;margin-bottom:4px;}
        .form-card .subtitle{font-size:13px;color:#999;margin-bottom:22px;line-height:1.5;}

        .field{margin-top:20px;}
        .field:first-of-type{margin-top:0;}
        .field-label{
            display:block;font-size:11px;margin-bottom:8px;
            color:#ff5722;text-transform:uppercase;letter-spacing:.06em;font-weight:500;
        }
        .field-input,.field-textarea,.field-select{
            width:100%;padding:12px 14px;border-radius:10px;
            border:1px solid #333;background:#111;color:#f0f0f0;
            font-size:14px;outline:none;box-sizing:border-box;
            transition:border-color .18s,box-shadow .18s;
        }
        .field-input:focus,.field-textarea:focus,.field-select:focus{
            border-color:#ff5722;box-shadow:0 0 0 2px rgba(255,87,34,.25);
        }
        .field-input::placeholder,.field-textarea::placeholder{color:#555;}
        .field-textarea{min-height:140px;resize:vertical;line-height:1.6;}
        .field-select{cursor:pointer;}
        .field-select option{background:#181818;color:#f0f0f0;}

        .field-row{display:flex;gap:14px;}
        .field-row .field{flex:1;margin-top:0;}

        .field-hint{font-size:11px;color:#777;margin-top:4px;line-height:1.4;}

        .submit-btn{
            margin-top:24px;width:100%;padding:12px 20px;border:none;
            border-radius:999px;
            background:linear-gradient(135deg,#ff7043,#ff5722);
            color:#fff;font-size:14px;font-weight:600;cursor:pointer;
            text-transform:uppercase;letter-spacing:.04em;
            transition:transform .1s,box-shadow .12s,filter .12s;
        }
        .submit-btn:hover{filter:brightness(1.06);box-shadow:0 8px 20px rgba(255,87,34,.5);transform:translateY(-1px);}
        .submit-btn:active{transform:translateY(0);box-shadow:none;}

        .msg-success{
            margin-bottom:16px;padding:12px 16px;border-radius:12px;
            background:rgba(46,125,50,.8);border:1px solid #66bb6a;font-size:13px;line-height:1.5;
        }
        .msg-errors{
            margin-bottom:16px;padding:12px 16px;border-radius:12px;
            background:rgba(183,28,28,.8);border:1px solid #ef5350;font-size:13px;
        }
        .msg-errors ul{margin:0;padding-left:18px;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:24px 14px 40px;}
            .form-card{padding:22px 18px;}
            .field-row{flex-direction:column;gap:0;}
            .field-row .field{margin-top:20px;}
        }
        @media(max-width:480px){
            .page-title{font-size:17px;}
            .nav-link{font-size:12px;padding:6px 12px;}
            .field-input,.field-textarea,.field-select{font-size:13px;padding:10px 12px;}
            .submit-btn{font-size:13px;padding:10px 16px;}
            .form-card h2{font-size:16px;}
        }
    </style>
</head>
<body>

<div class="page-header">
    <a href="index.html" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        Главная
    </a>
    <a href="2btn.php" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        Карта событий
    </a>
    <div class="page-title"><span>Предложить</span> событие</div>
</div>

<div class="container">
    <div class="form-card">
        <h2>Предложить событие для карты</h2>
        <p class="subtitle">
            Если вам известно о событии, связанном с геноцидом на территории Беларуси, —
            заполните форму ниже. После проверки модератора событие может появиться на карте.
        </p>

        <?php if ($message): ?>
            <div class="msg-success"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="msg-errors">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label class="field-label" for="name">Ваше имя</label>
                <input class="field-input" type="text" id="name" name="name" required
                    placeholder="Иван Петров"
                    value="<?= e($_POST['name'] ?? ($currentUser['username'] ?? '')) ?>">
            </div>

            <div class="field">
                <label class="field-label" for="email">Ваш e-mail</label>
                <input class="field-input" type="email" id="email" name="email" required
                    placeholder="email@example.com"
                    value="<?= e($_POST['email'] ?? ($currentUser['email'] ?? '')) ?>">
                <div class="field-hint">На этот адрес придёт уведомление о решении модератора.</div>
            </div>

            <div class="field">
                <label class="field-label" for="title">Название события</label>
                <input class="field-input" type="text" id="title" name="title" required
                    placeholder="Например: Сожжение деревни Ола"
                    value="<?= e($_POST['title'] ?? '') ?>">
            </div>

            <div class="field">
                <label class="field-label" for="type">Тип события</label>
                <select class="field-select" id="type" name="type" required>
                    <?php foreach ($typeLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= ($_POST['type'] ?? 'other') === $key ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label class="field-label" for="event_date">Дата события</label>
                <input class="field-input" type="text" id="event_date" name="event_date" required
                    placeholder="Например: 1943-03-22 или март 1943"
                    value="<?= e($_POST['event_date'] ?? '') ?>">
            </div>

            <div class="field">
                <label class="field-label" for="location">Место / населённый пункт</label>
                <input class="field-input" type="text" id="location" name="location" required
                    placeholder="Деревня Ола, Паричский район, Гомельская область"
                    value="<?= e($_POST['location'] ?? '') ?>">
            </div>

            <div class="field-row" style="margin-top:20px;">
                <div class="field">
                    <label class="field-label" for="lat">Широта</label>
                    <input class="field-input" type="text" id="lat" name="lat" required
                        placeholder="53.9278"
                        value="<?= e($_POST['lat'] ?? '') ?>">
                </div>
                <div class="field">
                    <label class="field-label" for="lng">Долгота</label>
                    <input class="field-input" type="text" id="lng" name="lng" required
                        placeholder="27.5619"
                        value="<?= e($_POST['lng'] ?? '') ?>">
                </div>
            </div>
            <div class="field-hint">
                Координаты можно узнать: правый клик на <a href="https://maps.google.com" target="_blank" style="color:#ff5722">Google Maps</a> → «Что здесь?» → скопировать широту и долготу.
            </div>

            <div class="field">
                <label class="field-label" for="description">Описание события</label>
                <textarea class="field-textarea" id="description" name="description" required
                    placeholder="Подробно опишите событие (не менее 20 символов)..."
                ><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <button class="submit-btn" type="submit">Отправить на модерацию</button>
        </form>
    </div>
</div>

</body>
</html>
