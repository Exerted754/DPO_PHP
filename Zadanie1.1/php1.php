<?php

$input = trim(fgets(STDIN));

function double_num($str) {
    return preg_replace_callback(
        "/'(\d+)'/",
        function ($matches) {
            return "'" . ($matches[1] * 2) . "'";
        },
        $str
    );
}

$res = double_num($input);

print $res;