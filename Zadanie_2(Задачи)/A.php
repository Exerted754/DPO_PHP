<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = './test.xlsx';

$spreadsheet = IOFactory::load($path);
$worksheet = $spreadsheet->getActiveSheet();
$inputData = $worksheet->rangeToArray('B2:B5', null, true, true, false);

foreach ($inputData as $index => $row) { 
    $input = $row[0];
    $lines = explode("\n", trim($input));
    $banners = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $columns = preg_split("/\s+/", $line, 2);
        $bannerId = trim($columns[0]);
        $time = trim($columns[1]);

        if (!isset($banners[$bannerId])) {
            $banners[$bannerId] = ['count' => 0, 'last_time' => ''];
        }
        $banners[$bannerId]['count']++;
        if ($banners[$bannerId]['last_time'] === '' || strtotime($time) > strtotime($banners[$bannerId]['last_time'])) {
            $banners[$bannerId]['last_time'] = $time;
        }
    }

    $result = [];
    foreach ($banners as $bannerId => $data) {
        $result[] = "{$data['count']} {$bannerId} {$data['last_time']}";
    }
    sort($result);
    $actualOutput = implode("\n", $result);

    echo "Тест " . ($index + 1) . " (ячейка B" . ($index + 2) . "):\n";
    echo "$actualOutput\n\n";
}
?>