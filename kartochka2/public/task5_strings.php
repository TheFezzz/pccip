<?php
// Задание 5. Работа со строками.
// ВАШЕ ИМЯ и УВЛЕЧЕНИЯ — замените на свои реальные данные.

$yourName = 'Мартин'; // здесь укажите своё имя

$s1 = 'Моё имя: ' . $yourName;
$s2 = 'Моё хобби — программирование, музыка и спорт';

// 1. Определить длину строки S1.
$lengthS1 = mb_strlen($s1, 'UTF-8');

// 2. Вставить в строку S2 между словами дополнительные пробелы.
// Для примера заменим одиночные пробелы на двойные.
$s2WithExtraSpaces = preg_replace('/\s+/', '  ', $s2);

// 3. Заменить ВАШЕ ИМЯ в строке S1 на имя Павел.
$s1Replaced = str_replace($yourName, 'Павел', $s1);
?>

<section>
    <h2>Задание 5. Работа со строками</h2>

    <p>Строка S1: <strong><?= htmlspecialchars($s1, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
    <p>Длина строки S1: <strong><?= $lengthS1 ?></strong> символов (функция <span class="code">mb_strlen</span>).</p>

    <p>Строка S2 (исходная): <strong><?= htmlspecialchars($s2, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
    <p>Строка S2 с дополнительными пробелами: <strong><?= htmlspecialchars($s2WithExtraSpaces, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>

    <p>Строка S1 после замены имени на «Павел»: <strong><?= htmlspecialchars($s1Replaced, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
</section>

