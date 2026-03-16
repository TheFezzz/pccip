<?php
// Задание 3. Работа с циклами.
// Замените значения переменных $firstName и $lastName на свои.
$lastName  = 'Иванов';
$firstName = 'Иван';

$fullName = $lastName . ' ' . $firstName;

// n — количество букв в имени
$nLetters = mb_strlen($firstName, 'UTF-8');
$repeatTimes = $nLetters + 5;

$i = 0;
?>

<section>
    <h2>Задание 3. Цикл while</h2>
    <p>Фамилия и имя: <strong><?= htmlspecialchars($fullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong></p>
    <p>Количество букв в имени (n): <strong><?= $nLetters ?></strong>. Будет выведено n + 5 = <strong><?= $repeatTimes ?></strong> раз.</p>

    <?php while ($i < $repeatTimes): ?>
        <p><?= $i + 1 ?>. <?= htmlspecialchars($fullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php $i++; ?>
    <?php endwhile; ?>
</section>

