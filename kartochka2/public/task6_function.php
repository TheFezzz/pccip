<?php
// Задание 6. Пользовательская функция.
// Для наглядности используем формулу:
// f(x, y) = (1 + sqrt(x * y - (x + y))) / (y^2 - 4)
// Это позволяет показать обработку деления на ноль и корня из отрицательного числа.

function calculateExpression(float $x, float $y)
{
    $denominator = $y * $y - 4;
    if ($denominator == 0.0) {
        return 'Ошибка: деление на ноль (знаменатель y² - 4 равен 0).';
    }

    $underRoot = $x * $y - ($x + $y);
    if ($underRoot < 0) {
        return 'Ошибка: подкоренное выражение меньше нуля (x * y - (x + y) < 0).';
    }

    $result = (1 + sqrt($underRoot)) / $denominator;
    return $result;
}

// Получим значения из формы (метод GET по умолчанию).
$x = isset($_GET['x']) ? (float) $_GET['x'] : null;
$y = isset($_GET['y']) ? (float) $_GET['y'] : null;

$calculationDone = $x !== null && $y !== null;
$calculationResult = null;

if ($calculationDone) {
    $calculationResult = calculateExpression($x, $y);
}
?>

<section>
    <h2>Задание 6. Пользовательская функция и обработка исключений</h2>

    <p>Формула: <span class="code">f(x, y) = (1 + √(x * y - (x + y))) / (y² - 4)</span></p>

    <form method="get" action="">
        <label>
            Значение x:
            <input type="number" step="any" name="x" value="<?= $x !== null ? htmlspecialchars((string) $x, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
        </label>
        <label>
            Значение y:
            <input type="number" step="any" name="y" value="<?= $y !== null ? htmlspecialchars((string) $y, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
        </label>
        <input type="submit" value="Вычислить">
    </form>

    <?php if ($calculationDone): ?>
        <?php if (is_string($calculationResult)): ?>
            <p class="error"><?= htmlspecialchars($calculationResult, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
        <?php else: ?>
            <p class="success">
                Для x = <?= htmlspecialchars((string) $x, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> и
                y = <?= htmlspecialchars((string) $y, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>,
                результат вычисления функции: <strong><?= htmlspecialchars((string) $calculationResult, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>
            </p>
        <?php endif; ?>
    <?php endif; ?>
</section>

