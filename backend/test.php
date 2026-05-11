<?php
echo "__DIR__: " . __DIR__ . "\n";
$dbPath = realpath(__DIR__ . '/../database/clinic_booking.sqlite');
echo "dbPath: $dbPath\n";
echo "file_exists: " . (file_exists($dbPath) ? 'yes' : 'no') . "\n";

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully\n";
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Users: " . count($users) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
?>