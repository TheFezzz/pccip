<?php
echo "<h2>Задание 4: Массивы</h2>";
$arr = [3, 7, 2, 9, 5, 1, 8];
echo "Исходный массив: " . implode(", ", $arr) . "<br>";
$max = max($arr);
echo "Максимальный элемент: $max<br>";
array_pop($arr);
echo "Измененный массив: " . implode(", ", $arr) . "<br>";
?>