<?php
echo "<h2>Задание 5: Строки</h2>";
$s1 = "Я люблю Беларусь";
$s2 = "Я учусь в Политехническом колледже";

echo "Длина строки S1: " . strlen($s1) . "<br>";

$n_var = 1;
$char = $s1[$n_var - 1];
$ascii = ord($char);
echo "$n_var-ый символ строки S1: '$char', его ASCII-код: $ascii<br>";

$s2_modified = str_replace('о', 'а', $s2);
echo "Строка S2 после замены: $s2_modified<br>";
?>