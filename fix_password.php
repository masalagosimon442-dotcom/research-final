<?php
/**
 * Fix admin password.
 * Visit: https://your-app.onrender.com/fix_password.php?key=faith@mabiki
 */

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

echo "<h2>Fix Passwords</h2><pre>";

try {
    $pdo = new PDO("$driver:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected!\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Generate proper bcrypt hash for Admin@1234
$password = 'Admin@1234';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "New hash for '$password': $hash\n\n";

// Update all users
$stmt = $pdo->prepare("UPDATE users SET password = ?");
$stmt->execute([$hash]);
echo "Updated " . $stmt->rowCount() . " users with new password hash.\n\n";

// Verify
$stmt = $pdo->query("SELECT id, name, email, role, password FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    $verify = password_verify('Admin@1234', $u['password']);
    echo "{$u['email']} - password_verify: " . ($verify ? "✅ PASS" : "❌ FAIL") . "\n";
}

echo "\n\nDone! Try logging in now with:\nEmail: admin@hazina-asili.com\nPassword: Admin@1234\n";
echo "</pre>";
