<?php
$dbPath = __DIR__ . '/database/database.sqlite';
echo "Database path: " . $dbPath . "\n";
echo "File exists: " . (file_exists($dbPath) ? "Yes" : "No") . "\n";
echo "File size: " . filesize($dbPath) . " bytes\n\n";

$conn = new PDO('sqlite:' . $dbPath);
$result = $conn->query('SELECT name FROM sqlite_master WHERE type="table" ORDER BY name');
$tables = $result->fetchAll(PDO::FETCH_ASSOC);
echo "Tables found: " . count($tables) . "\n";
foreach ($tables as $row) {
    echo "- " . $row['name'] . "\n";
}
?>
