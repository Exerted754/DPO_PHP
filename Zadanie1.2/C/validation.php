<?php

declare(strict_types=1);

function validate($value, $type, $params): string {
    switch ($type) {
        case 'S': // Проверка длины строки
            if (count($params) < 2) return 'FAIL'; 
            [$n, $m] = array_map('intval', $params);
            return (strlen($value) >= $n && strlen($value) <= $m) ? 'OK' : 'FAIL';
        case 'N': // Проверка на целое число в диапазоне
            if (count($params) < 2) return 'FAIL';
            [$n, $m] = array_map('intval', $params);
            return (preg_match('/^-?\d+$/', $value) && (int)$value >= $n && (int)$value <= $m) ? 'OK' : 'FAIL';
        case 'P': // Проверка номера телефона
            return preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $value) ? 'OK' : 'FAIL';
        case 'D': // Проверка даты
            $value = preg_replace('/\b(\d)\b/', '0$1', $value); // Добавляем ведущие нули
            $dateTime = DateTime::createFromFormat('d.m.Y H:i', $value);
            return ($dateTime && $dateTime->format('d.m.Y H:i') === $value) ? 'OK' : 'FAIL';
        case 'E': // Проверка email
            return preg_match('/^[A-Za-z0-9][A-Za-z0-9_]{3,29}@[A-Za-z]{2,30}\.[a-z]{2,10}$/', $value) ? 'OK' : 'FAIL';
        default:
            return 'FAIL';
    }
}

// Чтение входных файлов
$inputFiles = glob("input/*.dat");
$outputFiles = glob("output/*.ans");

foreach ($inputFiles as $inputFile) {
    // Получаем ожидаемый файл вывода
    $expectedOutputFile = str_replace('input/', 'output/', $inputFile);
    $expectedOutputFile = str_replace('.dat', '.ans', $expectedOutputFile);
    
    // Чтение строк из входного и выходного файлов
    $lines = file($inputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $expectedOutput = file($expectedOutputFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $actualOutput = [];
    
    // Обработка каждой строки
    foreach ($lines as $index => $line) {
        if (preg_match('/<(.*?)>\s([SNPDE])(?:\s(-?\d+)\s(-?\d+))?/', $line, $matches)) {
            $value = $matches[1];
            $type = $matches[2];
            $params = array_slice($matches, 3);
            $result = validate($value, $type, $params);
            $actualOutput[] = $result;
        } else {
            // Если строка не соответствует формату
            $result = 'FAIL';
            $actualOutput[] = $result;
        }
    }
    
    // Выводим результат в нужном формате
    $fileName = basename($inputFile); // Получаем имя файла (например, 001.dat)
    if ($actualOutput === $expectedOutput) {
        echo "{$fileName} - Тест прошел!\n";
    } else {
        echo "{$fileName} - Тест не пройден.\n";
    }
}
