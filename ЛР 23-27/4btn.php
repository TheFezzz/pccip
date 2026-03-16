<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

function ensure_story_suggestions_table(PDO $pdo): void
{
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
            INDEX `idx_status` (`status`),
            CONSTRAINT `fk_story_suggestions_user`
                FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    try {
        $pdo->exec("ALTER TABLE `story_suggestions` ADD COLUMN `reject_reason` TEXT DEFAULT NULL AFTER `status`");
    } catch (PDOException $ex) {}
}
ensure_story_suggestions_table($pdo);

$perPage = 5;
$page    = max(1, (int)($_GET['page'] ?? 1));

$total      = (int)$pdo->query('SELECT COUNT(*) FROM people')->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$stmt = $pdo->prepare('SELECT id, title, content, image, section, created_at FROM people ORDER BY id DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$stories = $stmt->fetchAll();

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Истории очевидцев</title>
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
        .home-link,.header-btn{
            display:inline-flex;align-items:center;gap:6px;
            padding:8px 16px;border-radius:999px;
            background:#1e1e1e;border:1px solid #333;
            color:#e0e0e0;text-decoration:none;font-size:13px;font-weight:500;
            transition:all .15s;cursor:pointer;
        }
        .home-link:hover,.header-btn:hover{
            background:#ff5722;border-color:#ff5722;color:#fff;
            transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,87,34,.4);
        }
        .header-btn-suggest{
            background:linear-gradient(135deg,#ff7043,#ff5722);border-color:#ff5722;color:#fff;font-weight:600;
        }
        .header-btn-suggest:hover{filter:brightness(1.08);box-shadow:0 8px 22px rgba(255,87,34,.55);}
        .page-title{font-size:20px;font-weight:700;color:#fff;}
        .page-title span{color:#ff5722;}

        .page-stats{font-size:13px;color:#9e9e9e;display:flex;align-items:center;gap:14px;}
        .page-stats strong{color:#ff5722;font-size:15px;}

        .controls-row{display:flex;align-items:center;gap:8px;}
        .ctrl-btn{
            padding:7px 14px;border-radius:999px;border:1px solid #333;
            background:#1a1a1a;color:#ccc;font-size:12px;cursor:pointer;
            text-decoration:none;transition:all .15s;
        }
        .ctrl-btn:hover{background:#ff5722;border-color:#ff5722;color:#fff;}

        .container{max-width:860px;margin:0 auto;padding:28px 20px 60px;}

        .story-card{
            background:#181818;border:1px solid #262626;border-radius:14px;
            padding:24px 22px;margin-bottom:22px;
            box-shadow:0 6px 20px rgba(0,0,0,.5);
            transition:transform .12s,box-shadow .15s;
            position:relative;overflow:hidden;
        }
        .story-card::before{
            content:'';position:absolute;top:0;left:0;right:0;height:3px;
            background:linear-gradient(90deg,#ff5722,#ff9800);opacity:0;transition:opacity .15s;
        }
        .story-card:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(0,0,0,.65);}
        .story-card:hover::before{opacity:1;}

        .story-header{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:10px;}
        .story-title{font-size:18px;font-weight:700;color:#fff;line-height:1.35;}
        .story-section{
            flex-shrink:0;
            padding:4px 10px;border-radius:999px;
            background:#262626;border:1px solid #333;
            font-size:11px;color:#ff9800;white-space:nowrap;
        }
        .story-text{font-size:14px;line-height:1.7;color:#ccc;}
        .story-date{font-size:11px;color:#666;margin-top:10px;}

        .story-img{
            max-width:260px;max-height:220px;
            width:auto;height:auto;
            object-fit:cover;border-radius:10px;
            margin-top:14px;display:block;
            transition:transform .2s;
        }
        .story-img:hover{transform:scale(1.03);}

        .read-more-link{
            display:inline-block;margin-top:10px;
            color:#ff5722;font-size:13px;font-weight:500;
            text-decoration:none;transition:color .15s;
        }
        .read-more-link:hover{color:#ff9800;text-decoration:underline;}

        .empty-state{text-align:center;padding:60px 20px;color:#666;}
        .empty-state p{font-size:15px;margin-top:8px;}

        .pagination{
            display:flex;flex-wrap:wrap;justify-content:center;
            gap:6px;margin-top:30px;
        }
        .pagination a,.pagination span{
            display:inline-flex;align-items:center;justify-content:center;
            min-width:38px;height:38px;padding:0 12px;
            border:1px solid #333;border-radius:10px;
            font-size:13px;color:#ccc;text-decoration:none;
            background:#181818;transition:all .15s;
        }
        .pagination a:hover{background:#ff5722;border-color:#ff5722;color:#fff;}
        .pagination span.current{
            background:#ff5722;border-color:#ff5722;color:#fff;
            font-weight:700;box-shadow:0 4px 12px rgba(255,87,34,.5);
        }
        .pagination .page-info{
            display:flex;align-items:center;
            font-size:12px;color:#888;padding:0 8px;
        }

        body.accessibility{background:#000!important;color:#fff!important;font-size:1.3em;line-height:1.7;}
        body.accessibility .story-card{background:#111!important;border-color:#555!important;}
        body.accessibility a{color:#0ff!important;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:18px 14px 40px;}
            .story-card{padding:18px 16px;}
            .story-header{flex-direction:column;gap:8px;}
        }
        @media(max-width:480px){
            .page-title{font-size:15px;}
            .home-link,.header-btn,.ctrl-btn{font-size:11px;padding:6px 10px;}
            .story-title{font-size:15px;}
            .story-img{max-width:100%;max-height:200px;}
            .story-text{font-size:13px;}
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
        <div class="page-title"><span>Истории</span> очевидцев</div>
    </div>

    <div class="page-stats">
        Всего историй: <strong><?= $total ?></strong>
        <?php if ($totalPages > 1): ?>
            &middot; Страница <?= $page ?> из <?= $totalPages ?>
        <?php endif; ?>
    </div>

    <div class="controls-row">
        <a href="suggest.php" class="header-btn header-btn-suggest">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Предложить историю
        </a>
        <button class="ctrl-btn" onclick="toggleAccessibility()" id="accessibilityBtn">Для слабовидящих</button>
        <div id="google_translate_element"></div>
    </div>
</div>

<div class="container">
    <?php if (!$stories): ?>
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <p>Пока нет добавленных историй</p>
        </div>
    <?php else: ?>
        <?php foreach ($stories as $story): ?>
            <div class="story-card">
                <div class="story-header">
                    <div class="story-title"><?= e($story['title']) ?></div>
                    <div class="story-section"><?= e($story['section']) ?></div>
                </div>

                <?php if (!empty($story['image'])): ?>
                    <img class="story-img" src="<?= e($story['image']) ?>" alt="<?= e($story['title']) ?>">
                <?php endif; ?>

                <div class="story-text">
                    <?php
                        $text = $story['content'];
                        $preview = mb_strlen($text) > 400 ? mb_substr($text, 0, 400, 'UTF-8') . '...' : $text;
                    ?>
                    <?= nl2br(e($preview)) ?>
                </div>

                <a class="read-more-link" href="story.php?id=<?= (int)$story['id'] ?>">Читать полностью и комментировать &rarr;</a>

                <div class="story-date"><?= e($story['created_at']) ?></div>
            </div>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>">&laquo; Назад</a>
                <?php endif; ?>

                <?php
                    $start = max(1, $page - 3);
                    $end   = min($totalPages, $page + 3);
                    for ($p = $start; $p <= $end; $p++):
                ?>
                    <?php if ($p === $page): ?>
                        <span class="current"><?= $p ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $p ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>">Вперёд &raquo;</a>
                <?php endif; ?>

                <span class="page-info"><?= $page ?> / <?= $totalPages ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage:'ru', includedLanguages:'en,ru,be,uk,de,fr,pl',
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
