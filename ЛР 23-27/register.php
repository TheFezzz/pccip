<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';
require __DIR__ . '/mailer.php';

if ($currentUser) {
    header('Location: /index.html');
    exit;
}

$errors  = [];
$step    = 'form';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['reg_action'] ?? 'register';

    if ($action === 'register') {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        if ($username === '' || mb_strlen($username) < 3) {
            $errors[] = 'Логин должен содержать не менее 3 символов.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный e-mail.';
        }
        if (mb_strlen($password) < 6) {
            $errors[] = 'Пароль должен содержать не менее 6 символов.';
        }
        if ($password !== $password2) {
            $errors[] = 'Пароли не совпадают.';
        }

        if (!$errors) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :u OR email = :e');
            $stmt->execute([':u' => $username, ':e' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Пользователь с таким логином или e-mail уже существует.';
            }
        }

        if (!$errors) {
            $code = (string) random_int(100000, 999999);
            $sent = send_registration_code_email($email, $username, $code);

            if (!$sent) {
                $errors[] = 'Не удалось отправить код подтверждения на указанный e-mail. Проверьте адрес и попробуйте позже.';
            } else {
                $_SESSION['reg_pending'] = [
                    'username' => $username,
                    'email'    => $email,
                    'password' => $password,
                    'code'     => $code,
                    'expires'  => time() + 600,
                ];
                $step = 'code';
            }
        }

    } elseif ($action === 'verify_code') {
        $enteredCode = trim($_POST['code'] ?? '');
        $pending     = $_SESSION['reg_pending'] ?? null;

        if (!$pending) {
            $errors[] = 'Сессия истекла. Пройдите регистрацию заново.';
        } elseif ($pending['expires'] < time()) {
            unset($_SESSION['reg_pending']);
            $errors[] = 'Код подтверждения истёк (10 минут). Пройдите регистрацию заново.';
        } elseif ($enteredCode !== $pending['code']) {
            $errors[] = 'Неверный код подтверждения. Попробуйте ещё раз.';
            $step = 'code';
        } else {
            $hash = password_hash($pending['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:u, :e, :p)');
            $stmt->execute([
                ':u' => $pending['username'],
                ':e' => $pending['email'],
                ':p' => $hash,
            ]);

            unset($_SESSION['reg_pending']);
            header('Location: /login.php?registered=1');
            exit;
        }

    } elseif ($action === 'resend') {
        $pending = $_SESSION['reg_pending'] ?? null;
        if (!$pending) {
            $errors[] = 'Сессия истекла. Пройдите регистрацию заново.';
        } else {
            $code = (string) random_int(100000, 999999);
            $sent = send_registration_code_email($pending['email'], $pending['username'], $code);

            if (!$sent) {
                $errors[] = 'Не удалось повторно отправить код. Попробуйте позже.';
                $step = 'code';
            } else {
                $_SESSION['reg_pending']['code']    = $code;
                $_SESSION['reg_pending']['expires']  = time() + 600;
                $step    = 'code';
                $success = 'Новый код отправлен на ' . htmlspecialchars($pending['email'], ENT_QUOTES, 'UTF-8');
            }
        }
    }
}

if (isset($_SESSION['reg_pending']) && $step === 'form' && empty($errors)) {
    $step = 'code';
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
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
        .field-input-code{
            text-align:center;font-size:22px;letter-spacing:8px;font-weight:700;
        }

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
        .submit-btn-secondary{
            background:#333;margin-top:10px;
        }
        .submit-btn-secondary:hover{background:#444;box-shadow:0 4px 12px rgba(0,0,0,.4);}

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
    <div class="page-title"><span>Регистрация</span></div>
</div>

<div class="container">
    <div class="form-card">

<?php if ($step === 'code'): ?>
    <?php $pending = $_SESSION['reg_pending'] ?? null; ?>
    <h2>Подтверждение e-mail</h2>
    <p class="subtitle">
        Мы отправили 6-значный код на<br>
        <strong><?= e($pending['email'] ?? '') ?></strong>
    </p>

    <?php if ($errors): ?>
        <div class="msg-errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="msg-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="reg_action" value="verify_code">
        <div class="field">
            <label class="field-label" for="code">Код подтверждения</label>
            <input class="field-input field-input-code" type="text" id="code" name="code"
                maxlength="6" pattern="\d{6}" required autofocus placeholder="------">
        </div>
        <button class="submit-btn" type="submit">Подтвердить</button>
    </form>

    <form method="post" style="margin-top:6px;">
        <input type="hidden" name="reg_action" value="resend">
        <button class="submit-btn submit-btn-secondary" type="submit">Отправить код повторно</button>
    </form>

    <div class="form-links">
        <a href="/register.php?reset=1">Вернуться к форме регистрации</a>
    </div>

    <?php if (isset($_GET['reset'])): unset($_SESSION['reg_pending']); ?>
        <script>location.href='/register.php';</script>
    <?php endif; ?>

<?php else: ?>
    <h2>Создание аккаунта</h2>
    <p class="subtitle">Заполните форму, чтобы зарегистрироваться на сайте</p>

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
        <input type="hidden" name="reg_action" value="register">
        <div class="field">
            <label class="field-label" for="username">Логин</label>
            <input class="field-input" type="text" id="username" name="username" required
                placeholder="Минимум 3 символа"
                value="<?= e($_POST['username'] ?? '') ?>">
        </div>

        <div class="field">
            <label class="field-label" for="email">E-mail</label>
            <input class="field-input" type="email" id="email" name="email" required
                placeholder="email@example.com"
                value="<?= e($_POST['email'] ?? '') ?>">
        </div>

        <div class="field">
            <label class="field-label" for="password">Пароль</label>
            <input class="field-input" type="password" id="password" name="password" required
                placeholder="Минимум 6 символов">
        </div>

        <div class="field">
            <label class="field-label" for="password_confirm">Повторите пароль</label>
            <input class="field-input" type="password" id="password_confirm" name="password_confirm" required
                placeholder="Повторите пароль">
        </div>

        <button class="submit-btn" type="submit">Зарегистрироваться</button>
    </form>

    <div class="form-links">
        Уже есть аккаунт? <a href="/login.php">Войти</a>
    </div>
<?php endif; ?>

    </div>
</div>

</body>
</html>
