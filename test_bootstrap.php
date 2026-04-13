<?php
require __DIR__ . '/bootstrap/app.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "App bootstrapped successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
