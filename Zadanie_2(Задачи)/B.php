<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$path = './test.xlsx';

$spreadsheet = IOFactory::load($path);
$worksheet = $spreadsheet->getActiveSheet();
$inputData = $worksheet->rangeToArray('B7:B13', null, true, true, false);

foreach ($inputData as $index => $row) {
    $input = $row[0];
    $lines = explode("\n", trim($input));
    $sections = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }
        $columns = preg_split("/\s+/", $line, 4);
        $id = $columns[0];
        $name = $columns[1];
        $leftKey = (int)$columns[2];
        $rightKey = (int)$columns[3];
        $sections[] = ['id' => $id, 'name' => $name, 'left' => $leftKey, 'right' => $rightKey, 'level' => 0];
    }
    
    for ($i = 0; $i < count($sections); $i++) {
        $level = 0;
        for ($j = 0; $j < count($sections); $j++) {
            if ($i === $j) {
                continue;
            }
            if ($sections[$j]['left'] < $sections[$i]['left'] && $sections[$j]['right'] > $sections[$i]['right']) {
                $level++;
            }
        }
        $sections[$i]['level'] = $level;
    }

    $result = [];
    foreach ($sections as $section) {
        $prefix = str_repeat('–', $section['level']);
        $result[] = $prefix . $section['name'];
    }

    $actualOutput = implode("\n", $result);

    echo "Тест " . ($index + 1) . " (ячейка B" . ($index + 7) . "):\n";
    echo "$actualOutput\n\n";
}
?>