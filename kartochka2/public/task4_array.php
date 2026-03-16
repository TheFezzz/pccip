<?php
// Задание 4. Работа с массивом.
// Точный текст задания на фото читается не полностью, поэтому
// берём типичную формулировку: есть массив стипендий студентов,
// нужно вывести значения и посчитать суммарную величину.

$scholarships = [
    'Иванов' => 3200,
    'Петров' => 3400,
    'Сидоров' => 3000,
    'Кузнецова' => 3600,
    'Смирнова' => 3800,
];

$totalScholarship = array_sum($scholarships);
?>

<section>
    <h2>Задание 4. Работа с массивом (стипендии)</h2>
    <table>
        <thead>
        <tr>
            <th>Студент</th>
            <th>Стипендия, руб.</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($scholarships as $student => $amount): ?>
            <tr>
                <td><?= htmlspecialchars($student, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                <td><?= number_format($amount, 2, ',', ' ') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th>Суммарная величина начисленной стипендии</th>
            <th><?= number_format($totalScholarship, 2, ',', ' ') ?> руб.</th>
        </tr>
        </tfoot>
    </table>
</section>

