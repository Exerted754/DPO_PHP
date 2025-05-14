<?php
$input = file_get_contents('input.txt');
$lines = explode("\n", trim($input));
$n = (int)$lines[0];
$output = [];

for ($i = 1; $i <= $n; $i++) {
    list($dep_time, $dep_zone, $arr_time, $arr_zone) = explode(' ', trim($lines[$i]));
    $dep_time = str_replace('_', ' ', $dep_time);
    $arr_time = str_replace('_', ' ', $arr_time);
    $dep_zone = sprintf('%+03d:00', (int)$dep_zone);
    $arr_zone = sprintf('%+03d:00', (int)$arr_zone);
    $departure = DateTime::createFromFormat('d.m.Y H:i:s', $dep_time, new DateTimeZone($dep_zone));
    $arrival = DateTime::createFromFormat('d.m.Y H:i:s', $arr_time, new DateTimeZone($arr_zone));
    $duration = $arrival->getTimestamp() - $departure->getTimestamp();
    $output[] = $duration;
}

file_put_contents('output.txt', implode("\n", $output));
?>