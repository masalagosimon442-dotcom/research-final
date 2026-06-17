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

// Create users table if missing
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (PDOException $e) {
    echo "Creating users table manually...\n";
    $pdo->exec("
        CREATE TABLE users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(180) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'researcher',
            bio TEXT DEFAULT NULL,
            institution VARCHAR(200) DEFAULT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            passport_document VARCHAR(255) DEFAULT NULL,
            api_key VARCHAR(64) DEFAULT NULL,
            reset_token VARCHAR(64) DEFAULT NULL,
            reset_expires TIMESTAMP DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    ");
    $pdo->exec("
        INSERT INTO users (name, email, password, role, institution, created_at) VALUES
        ('System Admin', 'admin@hazina-asili.com', '\$2y\$12\$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi', 'admin', 'HAZINA ASILI', NOW()),
        ('Dr. Jane Smith', 'researcher@hazina-asili.com', '\$2y\$12\$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi', 'researcher', 'University of Dar es Salaam', NOW())
    ");
    echo "✅ Users table created and seeded!\n";
}

// Verify
$tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename")->fetchAll(PDO::FETCH_COLUMN);
echo "Tables (" . count($tables) . "):\n";
foreach ($tables as $t) echo "  - $t\n";

$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "\nUsers (" . count($users) . "):\n";
foreach ($users as $u) echo "  - {$u['name']} ({$u['email']}) [{$u['role']}]\n";

echo "\n\n🎉 SETUP COMPLETE! Delete setup.php for security.\n";
echo "</pre>";
