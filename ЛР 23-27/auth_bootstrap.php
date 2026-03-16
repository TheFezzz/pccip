<?php
declare(strict_types=1);

session_start();

$dbConfig = require __DIR__ . '/../config/db.php';

$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['dbname'],
    $dbConfig['charset']
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);

$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT id, username, email, role FROM users WHERE id = :id AND is_active = 1');
    $stmt->execute([':id' => (int) $_SESSION['user_id']]);
    $currentUser = $stmt->fetch() ?: null;
}

function require_login(): void
{
    global $currentUser;
    if (!$currentUser) {
        header('Location: /login.php');
        exit;
    }
}

function require_admin(): void
{
    global $currentUser;
    if (!$currentUser || $currentUser['role'] !== 'admin') {
        http_response_code(403);
        echo 'Доступ запрещён';
        exit;
    }
}

