<?php

$lines = file('storage/logs/laravel.log');
$recentLines = array_slice($lines, -100);

foreach ($recentLines as $line) {
    if (strpos($line, '[') === 0 || strpos($line, 'local.') === 0) {
        echo trim($line)."\n";
    }
}
