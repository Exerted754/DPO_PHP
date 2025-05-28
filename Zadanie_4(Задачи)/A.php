<?php
$input = trim(file_get_contents('./test_a.txt'));

function restoreLink($brokenLink) {
    // Определяем протокол
    if (strpos($brokenLink, 'https') === 0) {
        $protocol = 'https';
        $rest = substr($brokenLink, 5);
    } else {
        $protocol = 'http';
        $rest = substr($brokenLink, 4);
    }
    
    // Находим домен
    $domainEnd = '';
    if (strpos($rest, 'ru') !== false) {
        $domainEnd = 'ru';
    } elseif (strpos($rest, 'com') !== false) {
        $domainEnd = 'com';
    }
    
    // Разделяем оставшуюся часть на домен и контекст
    $parts = [];
    if ($domainEnd === 'ru') {
        $parts = explode('ru', $rest, 2);
        $domain = $parts[0];
        $context = isset($parts[1]) ? $parts[1] : '';
    } else { 
        $parts = explode('com', $rest, 2);
        $domain = $parts[0];
        $context = isset($parts[1]) ? $parts[1] : '';
    }
    
    // Формируем восстановленную ссылку
    $restoredLink = $protocol . '://' . $domain . '.' . $domainEnd;
    
    // Добавляем контекст, если он есть
    if (!empty($context)) {
        $restoredLink .= '/' . $context;
    }
    
    return $restoredLink;
}

echo restoreLink($input);
echo "\n";
?>
