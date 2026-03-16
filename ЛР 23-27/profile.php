<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require_login();

$message = '';
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный e-mail.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :e AND id <> :id');
            $stmt->execute([':e' => $email, ':id' => $currentUser['id']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Такой e-mail уже используется другим пользователем.';
            } else {
                $stmt = $pdo->prepare('UPDATE users SET email = :e WHERE id = :id');
                $stmt->execute([':e' => $email, ':id' => $currentUser['id']]);
                $currentUser['email'] = $email;
                $message = 'Профиль обновлён.';
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';
        if (mb_strlen($password) < 6) {
            $errors[] = 'Новый пароль должен содержать не менее 6 символов.';
        } elseif ($password !== $password2) {
            $errors[] = 'Пароли не совпадают.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :p WHERE id = :id');
            $stmt->execute([':p' => $hash, ':id' => $currentUser['id']]);
            $message = 'Пароль успешно изменён.';
        }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
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
        .nav-link{
            display:inline-flex;align-items:center;gap:6px;
            padding:8px 16px;border-radius:999px;
            background:#1e1e1e;border:1px solid #333;
            color:#e0e0e0;text-decoration:none;font-size:13px;font-weight:500;
            transition:all .15s;
        }
        .nav-link:hover{background:#ff5722;border-color:#ff5722;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(255,87,34,.4);}
        .nav-link.logout{border-color:#555;color:#ff8a80;}
        .nav-link.logout:hover{background:#c62828;border-color:#c62828;color:#fff;}
        .page-title{font-size:20px;font-weight:700;color:#fff;}
        .page-title span{color:#ff5722;}

        .container{max-width:560px;margin:0 auto;padding:36px 20px 60px;}

        .profile-card{
            background:#181818;border:1px solid #262626;border-radius:16px;
            padding:28px 26px;box-shadow:0 10px 30px rgba(0,0,0,.6);
        }
        .profile-header{
            display:flex;align-items:center;gap:16px;margin-bottom:24px;
            padding-bottom:20px;border-bottom:1px solid #2a2a2a;
        }
        .profile-avatar{
            width:56px;height:56px;border-radius:50%;
            background:linear-gradient(135deg,#ff7043,#ff5722);
            display:flex;align-items:center;justify-content:center;
            color:#fff;font-size:24px;font-weight:700;flex-shrink:0;
        }
        .profile-info{}
        .profile-name{font-size:18px;font-weight:700;margin:0 0 4px;}
        .profile-role{font-size:12px;color:#999;text-transform:uppercase;letter-spacing:.08em;}

        .field{margin-top:20px;}
        .field:first-of-type{margin-top:0;}
        .field-label{
            display:block;font-size:11px;margin-bottom:8px;
            color:#ff5722;text-transform:uppercase;letter-spacing:.06em;font-weight:500;
        }
        .field-input{
            width:100%;padding:12px 14px;border-radius:10px;
            border:1px solid #333;background:#111;color:#f0f0f0;
            font-size:14px;outline:none;box-sizing:border-box;
            transition:border-color .18s,box-shadow .18s;
        }
        .field-input:focus{
            border-color:#ff5722;box-shadow:0 0 0 2px rgba(255,87,34,.25);
        }

        .section-title{
            font-size:15px;font-weight:600;color:#ff5722;
            margin:28px 0 16px;padding-top:20px;border-top:1px solid #2a2a2a;
        }
        .section-title:first-of-type{margin-top:0;padding-top:0;border-top:none;}

        .submit-btn{
            margin-top:20px;width:100%;padding:12px 20px;border:none;
            border-radius:999px;
            background:linear-gradient(135deg,#ff7043,#ff5722);
            color:#fff;font-size:14px;font-weight:600;cursor:pointer;
            text-transform:uppercase;letter-spacing:.04em;
            transition:transform .1s,box-shadow .12s,filter .12s;
        }
        .submit-btn:hover{filter:brightness(1.06);box-shadow:0 8px 20px rgba(255,87,34,.5);transform:translateY(-1px);}

        .msg-success{
            margin-bottom:16px;padding:12px 16px;border-radius:12px;
            background:rgba(46,125,50,.8);border:1px solid #66bb6a;font-size:13px;
        }
        .msg-errors{
            margin-bottom:16px;padding:12px 16px;border-radius:12px;
            background:rgba(183,28,28,.8);border:1px solid #ef5350;font-size:13px;
        }
        .msg-errors ul{margin:0;padding-left:18px;}

        .profile-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:24px;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:24px 14px 40px;}
            .profile-card{padding:22px 18px;}
        }
        @media(max-width:480px){
            .page-title{font-size:17px;}
            .nav-link{font-size:12px;padding:6px 12px;}
            .field-input{font-size:13px;padding:10px 12px;}
            .submit-btn{font-size:13px;padding:10px 16px;}
            .profile-avatar{width:44px;height:44px;font-size:20px;}
            .profile-name{font-size:16px;}
            .profile-actions{gap:6px;}
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
        <?php if ($currentUser['role'] === 'admin'): ?>
            <a href="/admin.php" class="nav-link">Админ-панель</a>
        <?php endif; ?>
        <a href="/logout.php" class="nav-link logout">Выйти</a>
    </div>
    <div class="page-title"><span>Профиль</span></div>
</div>

<div class="container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= e(mb_strtoupper(mb_substr($currentUser['username'], 0, 1, 'UTF-8'))) ?>
            </div>
            <div class="profile-info">
                <div class="profile-name"><?= e($currentUser['username']) ?></div>
                <div class="profile-role"><?= $currentUser['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></div>
            </div>
        </div>

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

        <div class="section-title">Основные данные</div>
        <form method="post">
            <input type="hidden" name="update_profile" value="1">
            <div class="field">
                <label class="field-label" for="email">E-mail</label>
                <input class="field-input" type="email" id="email" name="email" required
                    value="<?= e($currentUser['email']) ?>">
            </div>
            <button class="submit-btn" type="submit">Сохранить</button>
        </form>

        <div class="section-title">Смена пароля</div>
        <form method="post">
            <input type="hidden" name="change_password" value="1">
            <div class="field">
                <label class="field-label" for="password">Новый пароль</label>
                <input class="field-input" type="password" id="password" name="password" required
                    placeholder="Минимум 6 символов">
            </div>
            <div class="field">
                <label class="field-label" for="password_confirm">Повторите новый пароль</label>
                <input class="field-input" type="password" id="password_confirm" name="password_confirm" required
                    placeholder="Повторите пароль">
            </div>
            <button class="submit-btn" type="submit">Изменить пароль</button>
        </form>

        <div class="profile-actions">
            <a href="index.html" class="nav-link">← На главную</a>
        </div>
    </div>
</div>

</body>
</html>
