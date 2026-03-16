<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /4btn.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM people WHERE id = :id');
$stmt->execute([':id' => $id]);
$story = $stmt->fetch();

if (!$story) {
    header('Location: /4btn.php');
    exit;
}

$commentError = '';
$commentSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    if (!$currentUser) {
        $commentError = 'Войдите, чтобы оставить комментарий.';
    } else {
        $body = trim((string)($_POST['body'] ?? ''));
        if ($body === '' || mb_strlen($body) < 3) {
            $commentError = 'Комментарий должен содержать не менее 3 символов.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO comments (user_id, entity_type, entity_id, body) VALUES (:u, :t, :e, :b)');
            $stmt->execute([
                ':u' => $currentUser['id'],
                ':t' => 'people',
                ':e' => $id,
                ':b' => $body,
            ]);
            $commentSuccess = 'Комментарий добавлен.';
            $_POST['body'] = '';
        }
    }
}

$stmt = $pdo->prepare('
    SELECT c.id, c.body, c.created_at, u.username
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.entity_type = "people" AND c.entity_id = :id
    ORDER BY c.created_at ASC
');
$stmt->execute([':id' => $id]);
$comments = $stmt->fetchAll();

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($story['title']) ?> — Истории очевидцев</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#0e0e0e;color:#f0f0f0;min-height:100vh;}
        .page-header{
            background:linear-gradient(180deg,rgba(30,30,30,.96),rgba(14,14,14,.98));
            border-bottom:1px solid #262626;padding:16px 24px;
            display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;
        }
        .nav-link{
            display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;
            background:#1e1e1e;border:1px solid #333;color:#e0e0e0;text-decoration:none;font-size:13px;font-weight:500;
            transition:all .15s;
        }
        .nav-link:hover{background:#ff5722;border-color:#ff5722;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,87,34,.4);}
        .page-title{font-size:18px;font-weight:700;color:#fff;}
        .page-title span{color:#ff5722;}

        .container{max-width:720px;margin:0 auto;padding:28px 20px 60px;}

        .story-card{
            background:#181818;border:1px solid #262626;border-radius:16px;
            padding:28px 26px;margin-bottom:28px;box-shadow:0 8px 24px rgba(0,0,0,.55);
        }
        .story-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px;}
        .story-title{font-size:22px;font-weight:700;color:#fff;line-height:1.35;}
        .story-section{
            flex-shrink:0;padding:6px 12px;border-radius:999px;
            background:#262626;border:1px solid #333;font-size:12px;color:#ff9800;white-space:nowrap;
        }
        .story-text{font-size:15px;line-height:1.75;color:#ccc;}
        .story-date{font-size:12px;color:#666;margin-top:16px;}
        .story-img{
            max-width:320px;max-height:280px;width:auto;height:auto;
            object-fit:cover;border-radius:12px;margin-top:16px;display:block;
        }

        .comments-section{
            background:#181818;border:1px solid #262626;border-radius:16px;
            padding:24px 26px;margin-top:28px;box-shadow:0 8px 24px rgba(0,0,0,.55);
        }
        .comments-title{font-size:18px;font-weight:700;color:#ff5722;margin-bottom:20px;}

        .comment{
            background:#1a1a1a;border:1px solid #2a2a2a;border-radius:12px;
            padding:14px 16px;margin-bottom:12px;
        }
        .comment-header{display:flex;align-items:center;gap:10px;margin-bottom:8px;}
        .comment-author{font-weight:600;color:#ff5722;font-size:14px;}
        .comment-date{font-size:11px;color:#666;}
        .comment-body{font-size:14px;line-height:1.6;color:#ccc;}

        .comment-form{margin-top:20px;}
        .field-label{
            display:block;font-size:11px;margin-bottom:8px;
            color:#ff5722;text-transform:uppercase;letter-spacing:.06em;font-weight:500;
        }
        .field-textarea{
            width:100%;padding:12px 14px;border-radius:10px;
            border:1px solid #333;background:#111;color:#f0f0f0;
            font-size:14px;outline:none;box-sizing:border-box;
            min-height:100px;resize:vertical;line-height:1.5;
            transition:border-color .18s,box-shadow .18s;
        }
        .field-textarea:focus{border-color:#ff5722;box-shadow:0 0 0 2px rgba(255,87,34,.25);}
        .submit-btn{
            margin-top:12px;padding:10px 20px;border:none;border-radius:999px;
            background:linear-gradient(135deg,#ff7043,#ff5722);
            color:#fff;font-size:13px;font-weight:600;cursor:pointer;
            text-transform:uppercase;letter-spacing:.04em;
            transition:transform .1s,box-shadow .12s,filter .12s;
        }
        .submit-btn:hover{filter:brightness(1.06);box-shadow:0 6px 16px rgba(255,87,34,.5);transform:translateY(-1px);}

        .msg-success{margin-bottom:12px;padding:10px 14px;border-radius:10px;background:rgba(46,125,50,.8);border:1px solid #66bb6a;font-size:13px;}
        .msg-error{margin-bottom:12px;padding:10px 14px;border-radius:10px;background:rgba(183,28,28,.8);border:1px solid #ef5350;font-size:13px;}
        .login-hint{font-size:13px;color:#999;margin-top:12px;}
        .login-hint a{color:#ff5722;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:18px 14px 40px;}
            .story-card,.comments-section{padding:20px 18px;}
            .story-header{flex-direction:column;gap:8px;}
            .story-title{font-size:18px;}
        }
        @media(max-width:480px){
            .page-title{font-size:15px;}
            .nav-link{font-size:12px;padding:6px 12px;}
            .story-title{font-size:16px;}
            .story-text{font-size:14px;}
            .story-img{max-width:100%;max-height:220px;}
            .comment{padding:10px 12px;}
            .field-textarea{font-size:13px;padding:10px 12px;}
        }
    </style>
</head>
<body>

<div class="page-header">
    <div style="display:flex;align-items:center;gap:14px;">
        <a href="index.html" class="nav-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
            Главная
        </a>
        <a href="4btn.php" class="nav-link">Истории очевидцев</a>
    </div>
    <div class="page-title"><span>История</span> очевидца</div>
</div>

<div class="container">
    <div class="story-card">
        <div class="story-header">
            <h1 class="story-title"><?= e($story['title']) ?></h1>
            <span class="story-section"><?= e($story['section']) ?></span>
        </div>

        <?php if (!empty($story['image'])): ?>
            <img class="story-img" src="<?= e($story['image']) ?>" alt="<?= e($story['title']) ?>">
        <?php endif; ?>

        <div class="story-text"><?= nl2br(e($story['content'])) ?></div>
        <div class="story-date"><?= e($story['created_at']) ?></div>
    </div>

    <div class="comments-section">
        <h2 class="comments-title">Комментарии (<?= count($comments) ?>)</h2>

        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="comment-header">
                    <span class="comment-author"><?= e($c['username']) ?></span>
                    <span class="comment-date"><?= e($c['created_at']) ?></span>
                </div>
                <div class="comment-body"><?= nl2br(e($c['body'])) ?></div>
            </div>
        <?php endforeach; ?>

        <?php if ($commentSuccess): ?>
            <div class="msg-success"><?= e($commentSuccess) ?></div>
        <?php endif; ?>
        <?php if ($commentError): ?>
            <div class="msg-error"><?= e($commentError) ?></div>
        <?php endif; ?>

        <div class="comment-form">
            <?php if ($currentUser): ?>
                <form method="post">
                    <input type="hidden" name="add_comment" value="1">
                    <label class="field-label" for="body">Оставить комментарий</label>
                    <textarea class="field-textarea" id="body" name="body" required
                        placeholder="Напишите ваш комментарий..."
                    ><?= e($_POST['body'] ?? '') ?></textarea>
                    <button class="submit-btn" type="submit">Отправить</button>
                </form>
            <?php else: ?>
                <p class="login-hint">
                    <a href="/login.php">Войдите</a>, чтобы оставить комментарий.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
