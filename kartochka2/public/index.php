<?php
// Установим кодировку по умолчанию
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №22 — вариант 5</title>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6fb;
            color: #222;
        }
        header {
            background: #1f2937;
            color: #fff;
            padding: 16px 24px;
        }
        header h1 {
            margin: 0;
            font-size: 20px;
        }
        main {
            max-width: 960px;
            margin: 24px auto 40px;
            padding: 0 16px;
        }
        section {
            background: #fff;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
        }
        section h2 {
            margin-top: 0;
            font-size: 18px;
            color: #111827;
        }
        .code {
            font-family: "Fira Code", "JetBrains Mono", Consolas, monospace;
            background: #f3f4f6;
            padding: 4px 6px;
            border-radius: 4px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f9fafb;
        }
        .error {
            color: #b91c1c;
            font-weight: 500;
        }
        .success {
            color: #065f46;
            font-weight: 500;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            align-items: flex-end;
            margin-bottom: 8px;
        }
        label {
            display: flex;
            flex-direction: column;
            font-size: 14px;
            color: #374151;
        }
        input[type="number"],
        input[type="text"] {
            padding: 6px 8px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            min-width: 120px;
        }
        button, input[type="submit"] {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            background: #2563eb;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover,
        input[type="submit"]:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
<header>
    <h1>Лабораторная работа №22 по PHP — вариант 5</h1>
</header>
<main>
    <?php
    // 1. Использование операторов включения: подключаем файлы с решениями заданий 2–6.
    include __DIR__ . '/task2_month.php';
    include __DIR__ . '/task3_loop.php';
    include __DIR__ . '/task4_array.php';
    include __DIR__ . '/task5_strings.php';
    include __DIR__ . '/task6_function.php';
    ?>
</main>
</body>
</html>

