<?php
/**
 * One-time database setup page.
 * Visit: https://your-app.onrender.com/setup.php
 * Delete this file after setup is complete.
 */

// Simple security - require a secret key
$setupKey = getenv('ADMIN_PASSWORD') ?: 'faith@mabiki';
if (($_GET['key'] ?? '') !== $setupKey) {
    http_response_code(403);
    die('Access denied. Use ?key=YOUR_ADMIN_PASSWORD');
}

$host   = getenv('DB_HOST') ?: 'localhost';
$port   = getenv('DB_PORT') ?: '5432';
$dbname = getenv('DB_NAME') ?: 'hazina_asili_db';
$user   = getenv('DB_USER') ?: 'postgres';
$pass   = getenv('DB_PASS') ?: '';
$driver = getenv('DB_DRIVER') ?: 'pgsql';

echo "<h2>HAZINA ASILI — Database Setup</h2>";
echo "<pre>";
echo "Driver: $driver\n";
echo "Connecting to: $host:$port/$dbname as $user\n\n";

try {
    $pdo = new PDO("$driver:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connected successfully!\n\n";
} catch (PDOException $e) {
    echo "❌ Connection FAILED: " . $e->getMessage() . "\n";
    exit;
}

$schemaFile = __DIR__ . '/database/postgresql_schema.sql';
if (!file_exists($schemaFile)) {
    echo "❌ postgresql_schema.sql not found!\n";
    exit;
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
            echo "⚠️ $msg\n";
            $errors++;
        }
    }
}

echo "\n✅ Results: $success executed, $skipped skipped, $errors errors.\n\n";

// Verify
$tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables (" . count($tables) . "):\n";
foreach ($tables as $t) echo "  - $t\n";

$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers (" . count($users) . "):\n";
foreach ($users as $u) echo "  - {$u['name']} ({$u['email']}) [{$u['role']}]\n";

echo "\n\n🎉 SETUP COMPLETE! Delete setup.php for security.\n";
echo "</pre>";
