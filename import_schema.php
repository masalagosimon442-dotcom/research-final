<?php
/**
 * Import PostgreSQL schema into Render database.
 * Run from Render Shell: php import_schema.php
 * Reads connection from environment variables automatically.
 */

$host   = getenv('DB_HOST') ?: 'localhost';
$port   = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'hazina_asili_db';
$user   = getenv('DB_USER') ?: 'postgres';
$pass   = getenv('DB_PASS') ?: '';

echo "=== HAZINA ASILI — Database Schema Import ===\n\n";
echo "Connecting to: $host:$port/$dbname as $user\n";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected successfully!\n\n";
} catch (PDOException $e) {
    echo "Connection FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Read schema file
$schemaFile = __DIR__ . '/database/postgresql_schema.sql';
if (!file_exists($schemaFile)) {
    echo "ERROR: postgresql_schema.sql not found!\n";
    exit(1);
}

$sql = file_get_contents($schemaFile);
$statements = array_filter(array_map('trim', explode(';', $sql)));

$success = 0;
$skipped = 0;
$errors  = 0;

foreach ($statements as $stmt) {
    if (empty($stmt) || strpos(ltrim($stmt), '--') === 0) continue;
    try {
        $pdo->exec($stmt);
        $success++;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'already exists') !== false || strpos($msg, 'duplicate key') !== false) {
            $skipped++;
        } else {
            echo "ERROR: $msg\n";
            $errors++;
        }
    }
}

echo "Results: $success executed, $skipped skipped (already exist), $errors errors.\n\n";

// Verify tables
$tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables in database (" . count($tables) . "):\n";
foreach ($tables as $t) echo "  - $t\n";

// Verify users
$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers (" . count($users) . "):\n";
foreach ($users as $u) echo "  - {$u['name']} ({$u['email']}) [{$u['role']}]\n";

echo "\n=== IMPORT COMPLETE ===\n";
