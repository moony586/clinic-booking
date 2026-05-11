<?php
// Setup for SQLite
$dbPath = __DIR__ . '/database/clinic_booking.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Connected to SQLite!\n";
} catch (PDOException $e) {
    echo "❌ Could not connect to SQLite: " . $e->getMessage() . "\n";
    exit(1);
}

// Read SQL file
$sql = file_get_contents('database/clinic_booking_sqlite.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "\nCreating tables...\n";

foreach ($statements as $statement) {
    if (!empty($statement)) {
        try {
            $pdo->exec($statement);
            echo "✓ Executed: " . substr($statement, 0, 40) . "...\n";
        } catch (PDOException $e) {
            echo "⚠ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Database setup completed!\n";
?>
