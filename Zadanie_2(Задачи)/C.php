<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = './test.xlsx';

$spreadsheet = IOFactory::load($path);
$worksheet = $spreadsheet->getActiveSheet();
$inputData = $worksheet->rangeToArray('B15:B20', null, true, true, false);

foreach ($inputData as $index => $row) {
    $input = $row[0];
    $lines = explode("\n", trim($input));
    $banners = [];

    // Парсинг входных данных
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $columns = preg_split("/[\s\t]+/", $line, 2);
        $bannerId = trim($columns[0]);
        $weight = (float)str_replace(',', '.', $columns[1]);
        $banners[] = ['id' => $bannerId, 'weight' => $weight, 'shows' => 0];
    }

    // Взвешенный случайный выбор
    $totalWeight = array_sum(array_column($banners, 'weight'));
    $totalShows = 1000000; // 10^6 показов

    for ($i = 0; $i < $totalShows; $i++) {
        $rand = mt_rand(0, 1000000) / 1000000.0 * $totalWeight;
        $currentSum = 0;
        foreach ($banners as &$banner) {
            $currentSum += $banner['weight'];
            if ($rand <= $currentSum) {
                $banner['shows']++;
                break;
            }
        }
        unset($banner);
    }

    // Формирование результата
    $result = [];
    foreach ($banners as $banner) {
        $share = $banner['shows'] / $totalShows;
        $result[] = sprintf("%s %.6f", $banner['id'], $share);
    }
    $actualOutput = implode("\n", $result);

    echo "Тест " . ($index + 1) . " (ячейка B" . ($index + 15) . "):\n";
    echo "$actualOutput\n\n";
}
?>