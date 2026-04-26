<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$columns = DB::select('DESCRIBE sent_emails');

foreach ($columns as $col) {
    echo $col->Field . " - " . $col->Type . "\n";
}