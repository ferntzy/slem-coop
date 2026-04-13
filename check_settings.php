<?php
$dbPath = __DIR__ . '/database/database.sqlite';
$conn = new PDO('sqlite:' . $dbPath);
$result = $conn->query('SELECT * FROM system_settings');
$rows = $result->fetchAll(PDO::FETCH_ASSOC);
echo "System Settings rows: " . count($rows) . "\n";
foreach ($rows as $row) {
    echo "Key: " . $row['key'] . " | Value: " . $row['value'] . "\n";
}
?>
