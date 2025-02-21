<?php

// Расширяет IPv6 адрес, заполняя пропущенные блоки нулями
function expandIPv6($ip) {
    $parts = explode("::", $ip);
    $left = isset($parts[0]) ? explode(":", $parts[0]) : [];
    $right = isset($parts[1]) ? explode(":", $parts[1]) : [];

    // Дополняем блоки до 4 символов
    foreach ($left as &$block) $block = str_pad($block, 4, "0", STR_PAD_LEFT);
    foreach ($right as &$block) $block = str_pad($block, 4, "0", STR_PAD_LEFT);

    // Добавляем пропущенные блоки
    $missingBlocks = 8 - (count($left) + count($right));
    $expanded = array_merge($left, array_fill(0, $missingBlocks, "0000"), $right);

    return implode(":", $expanded);
}


// Обрабатываем все файлы в директории input
$inputFiles = glob('input/*.dat');
foreach ($inputFiles as $inputFile) {
    $outputFile = 'output/' . basename($inputFile, '.dat') . '.ans';

    $inputData = file($inputFile, FILE_IGNORE_NEW_LINES);
    $expectedData = file($outputFile, FILE_IGNORE_NEW_LINES);
    $calculatedData = [];

    // Обрабатываем каждую строку
    foreach ($inputData as $line) {
        $calculatedData[] = (strpos($line, ':') !== false) ? expandIPv6($line) : validateData($line);
    }

    // Сравниваем результаты
    if (implode("\n", $expectedData) === implode("\n", $calculatedData)) {
        echo basename($inputFile) . " - Тест прошел!\n";
    } else {
        echo basename($inputFile) . " - Тест не прошел!\n";
    }
}
?>