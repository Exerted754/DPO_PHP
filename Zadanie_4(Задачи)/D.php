<?php
$input = file('test_d.txt');
$sections = [];

// Парсинг входных данных
foreach ($input as $line) {
    $line = trim($line);
    if (empty($line)) continue;
    
    list($id, $url, $parent_id, $time) = explode(';', $line);
    $sections[$id] = [
        'id' => (int)$id,
        'url' => $url,
        'parent_id' => (int)$parent_id,
        'time' => (int)$time,
        'children' => [],
        'lastmod' => 0 // Будет вычислено позже
    ];
}

// Построение дерева разделов
foreach ($sections as $id => &$section) {
    if ($section['parent_id'] != 0 && isset($sections[$section['parent_id']])) {
        $sections[$section['parent_id']]['children'][$id] = &$section;
    }
}

// Функция для вычисления времени последнего изменения с учетом потомков
function calculateLastMod(&$section, &$sections) {
    $lastmod = $section['time'];
    
    foreach ($section['children'] as $child_id => &$child) {
        calculateLastMod($child, $sections);
        $lastmod = max($lastmod, $child['lastmod']);
    }
    
    $section['lastmod'] = $lastmod;
    return $lastmod;
}

// Вычисление времени последнего изменения для всех разделов
foreach ($sections as &$section) {
    if (!isset($section['lastmod']) || $section['lastmod'] == 0) {
        calculateLastMod($section, $sections);
    }
}

// Функция для форматирования времени в ISO 8601
function formatTime($timestamp) {
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $date->setTimezone(new DateTimeZone('+03:00')); // Используем московское время (UTC+3)
    
    return $date->format('Y-m-d\TH:i:sP');
}

// Сортировка разделов по ID
uksort($sections, function($a, $b) {
    return (int)$a - (int)$b;
});

// Генерация XML
$output = '<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($sections as $section) {
    $output .= '<url><loc>' . $section['url'] . '</loc><lastmod>' . formatTime($section['lastmod']) . '</lastmod></url>';
}

$output .= '</urlset>';

// Запись результата в файл output.xml
file_put_contents('output.xml', $output);
?>
