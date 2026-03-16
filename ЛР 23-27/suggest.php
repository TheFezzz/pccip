<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS `story_suggestions` (
        `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `suggested_by_name` VARCHAR(255) NOT NULL,
        `suggested_by_email` VARCHAR(255) DEFAULT NULL,
        `title`             VARCHAR(255) NOT NULL,
        `content`           TEXT         NOT NULL,
        `image`             VARCHAR(500) NOT NULL DEFAULT '',
        `user_id`           INT UNSIGNED DEFAULT NULL,
        `status`            ENUM('new','approved','rejected') NOT NULL DEFAULT 'new',
        `reject_reason`     TEXT         DEFAULT NULL,
        `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$message = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim((string)($_POST['name'] ?? ''));
    $title   = trim((string)($_POST['title'] ?? ''));
    $content = trim((string)($_POST['content'] ?? ''));
    $image   = trim((string)($_POST['image'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));

    if ($name === '' || mb_strlen($name) < 3) {
        $errors[] = 'Имя очевидца должно содержать не менее 3 символов.';
    }
    if ($title === '' || mb_strlen($title) < 5) {
        $errors[] = 'Заголовок должен содержать не менее 5 символов.';
    }
    if ($content === '' || mb_strlen($content) < 50) {
        $errors[] = 'Текст истории должен быть не короче 50 символов.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Укажите корректный e-mail или оставьте поле пустым.';
    }
    if ($image !== '') {
        $image = mb_substr($image, 0, 500, 'UTF-8');
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO story_suggestions (suggested_by_name, suggested_by_email, title, content, image, user_id)
             VALUES (:n, :e, :t, :c, :i, :u)'
        );
        $stmt->execute([
            ':n' => $name,
            ':e' => $email !== '' ? $email : null,
            ':t' => $title,
            ':c' => $content,
            ':i' => $image,
            ':u' => $currentUser['id'] ?? null,
        ]);
        $message = 'Спасибо! Ваша история отправлена на модерацию. После проверки администратора она может быть опубликована на сайте.';
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
    <title>Предложить историю</title>
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
        .field-input,.field-textarea{
            width:100%;padding:12px 14px;border-radius:10px;
            border:1px solid #333;background:#111;color:#f0f0f0;
            font-size:14px;outline:none;box-sizing:border-box;
            transition:border-color .18s,box-shadow .18s;
        }
        .field-input:focus,.field-textarea:focus{
            border-color:#ff5722;box-shadow:0 0 0 2px rgba(255,87,34,.25);
        }
        .field-input::placeholder,.field-textarea::placeholder{color:#555;}
        .field-textarea{min-height:180px;resize:vertical;line-height:1.6;}

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
        }
        @media(max-width:480px){
            .page-title{font-size:17px;}
            .nav-link{font-size:12px;padding:6px 12px;}
            .field-input,.field-textarea{font-size:13px;padding:10px 12px;}
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
    <a href="4btn.php" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        Истории очевидцев
    </a>
    <div class="page-title"><span>Предложить</span> историю</div>
</div>

<div class="container">
    <div class="form-card">
        <h2>Расскажите историю очевидца</h2>
        <p class="subtitle">
            Поделитесь историей, связанной с геноцидом в Беларуси. Ваша заявка попадёт к модератору,
            и после проверки может быть опубликована на сайте.
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
                <label class="field-label" for="name">Имя очевидца</label>
                <input class="field-input" type="text" id="name" name="name" required
                    placeholder="Иван Петров"
                    value="<?= e($_POST['name'] ?? ($currentUser['username'] ?? '')) ?>">
            </div>

            <div class="field">
                <label class="field-label" for="email">Ваш e-mail (необязательно)</label>
                <input class="field-input" type="email" id="email" name="email"
                    placeholder="email@example.com"
                    value="<?= e($_POST['email'] ?? ($currentUser['email'] ?? '')) ?>">
            </div>

            <div class="field">
                <label class="field-label" for="title">Заголовок истории</label>
                <input class="field-input" type="text" id="title" name="title" required
                    placeholder="Краткое название истории"
                    value="<?= e($_POST['title'] ?? '') ?>">
            </div>

            <div class="field">
                <label class="field-label" for="image">Ссылка на фото (необязательно)</label>
                <input class="field-input" type="text" id="image" name="image"
                    placeholder="https://example.com/photo.jpg"
                    value="<?= e($_POST['image'] ?? '') ?>">
            </div>

            <div class="field">
                <label class="field-label" for="content">Текст истории</label>
                <textarea class="field-textarea" id="content" name="content" required
                    placeholder="Расскажите историю подробно (не менее 50 символов)..."
                ><?= e($_POST['content'] ?? '') ?></textarea>
            </div>

            <button class="submit-btn" type="submit">Отправить на модерацию</button>
        </form>
    </div>
</div>

</body>
</html>
