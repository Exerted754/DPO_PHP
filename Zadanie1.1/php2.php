<?php

function update_link($link) {
    preg_match("/RN=([0-9-]+)/", $link, $matches);
    if (isset($matches[1])) {
        return "http://sozd.parlament.gov.ru/bill/" . $matches[1];
    }
    return $link;
}

$file_path = 'text.html';
$content = file_get_contents($file_path);

$pattern = '/http:\/\/asozd\.duma\.gov\.ru\/main\.nsf\/\(Spravka\)\?OpenAgent&RN=([0-9-]+)&[0-9]+/';

$updated_content = preg_replace_callback($pattern, function($matches) {
    return update_link($matches[0]);
}, $content);

file_put_contents($file_path, $updated_content);

echo "Ссылки успешно обновлены в файле $file_path\n";