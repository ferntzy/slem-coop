<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$db = $app->make('db');

$email = 'accountofficer@example.com';

$profile = $db->table('profiles')->where('email', $email)->first();

print_r($profile);
