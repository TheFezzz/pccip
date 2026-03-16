<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

if ($currentUser) {
    header('Location: /index.html');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usernameOrEmail === '' || $password === '') {
        $errors[] = 'Введите логин (или e-mail) и пароль.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, email, password_hash, role, is_active FROM users WHERE username = :u OR email = :u LIMIT 1');
        $stmt->execute([':u' => $usernameOrEmail]);
        $user = $stmt->fetch();

        if (!$user || !(int) $user['is_active']) {
            $errors[] = 'Пользователь не найден или заблокирован.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'Неверный пароль.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];

            $stmt = $pdo->prepare('INSERT INTO sessions (id, user_id, ip, user_agent, created_at, expires_at) VALUES (:id, :user_id, :ip, :ua, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))');
            $stmt->execute([
                ':id'      => session_id(),
                ':user_id' => (int) $user['id'],
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250),
            ]);

            setcookie('auth_logged_in', '1', time() + 60 * 60 * 24 * 7, '/');

            header('Location: /index.html');
            exit;
        }
    }
}

$registered = isset($_GET['registered']);

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
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

        .container{max-width:460px;margin:0 auto;padding:48px 20px 60px;}

        .form-card{
            background:#181818;border:1px solid #262626;border-radius:16px;
            padding:28px 26px;box-shadow:0 10px 30px rgba(0,0,0,.6);
        }
        .form-card h2{font-size:18px;margin-bottom:4px;text-align:center;}
        .form-card .subtitle{font-size:13px;color:#999;margin-bottom:22px;line-height:1.5;text-align:center;}

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
        .field-input::placeholder{color:#555;}

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

        .form-links{margin-top:16px;text-align:center;font-size:13px;color:#bdbdbd;}
        .form-links a{color:#ff5722;text-decoration:none;}
        .form-links a:hover{text-decoration:underline;}

        @media(max-width:768px){
            .page-header{flex-direction:column;align-items:flex-start;padding:14px 16px;gap:10px;}
            .container{padding:24px 14px 40px;}
            .form-card{padding:22px 18px;}
        }
        @media(max-width:480px){
            .page-title{font-size:17px;}
            .nav-link{font-size:12px;padding:6px 12px;}
            .field-input{font-size:13px;padding:10px 12px;}
            .submit-btn{font-size:13px;padding:10px 16px;}
            .form-card h2{font-size:16px;}
        }
    </style>
</head>
<body>

<div class="page-header">
    <a href="index.html" class="nav-link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
        На главную
    </a>
    <div class="page-title"><span>Вход</span> в аккаунт</div>
</div>

<div class="container">
    <div class="form-card">
        <h2>Авторизация</h2>
        <p class="subtitle">Войдите, чтобы добавлять материалы и управлять профилем</p>

        <?php if ($registered): ?>
            <div class="msg-success">Регистрация успешно завершена. Теперь вы можете войти.</div>
        <?php endif; ?>

        <?php if ($errors): ?>
            <div class="msg-errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="field">
                <label class="field-label" for="username">Логин или e-mail</label>
                <input class="field-input" type="text" id="username" name="username" required
                    placeholder="Введите логин или e-mail"
                    value="<?= e($_POST['username'] ?? '') ?>">
            </div>

            <div class="field">
                <label class="field-label" for="password">Пароль</label>
                <input class="field-input" type="password" id="password" name="password" required
                    placeholder="Введите пароль">
            </div>

            <button class="submit-btn" type="submit">Войти</button>
        </form>

        <div class="form-links">
            Нет аккаунта? <a href="/register.php">Зарегистрироваться</a>
        </div>
    </div>
</div>

</body>
</html>
