<?php
declare(strict_types=1);

require __DIR__ . '/auth_bootstrap.php';

if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('DELETE FROM sessions WHERE id = :id');
    $stmt->execute([':id' => session_id()]);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Сбрасываем фронтенд-флаг авторизации
setcookie('auth_logged_in', '', time() - 3600, '/');

header('Location: /index.html');
exit;

