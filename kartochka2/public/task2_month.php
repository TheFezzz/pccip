<?php
// Задание 2. Работа с датой, временем, календарем.
$months = [
    1  => 'Январь',
    2  => 'Февраль',
    3  => 'Март',
    4  => 'Апрель',
    5  => 'Май',
    6  => 'Июнь',
    7  => 'Июль',
    8  => 'Август',
    9  => 'Сентябрь',
    10 => 'Октябрь',
    11 => 'Ноябрь',
    12 => 'Декабрь',
];

$currentMonthNumber = (int) date('n');
$currentMonthName = $months[$currentMonthNumber] ?? 'Неизвестный месяц';
?>

<section>
    <h2>Задание 2. Название текущего месяца</h2>
    <p>Текущий месяц (по функции <span class="code">date('n')</span>): <strong><?= $currentMonthNumber ?></strong>.</p>
    <p>Название текущего месяца из массива: <strong><?= htmlspecialchars($currentMonthName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
</section>

