<?php
require_once __DIR__ . '/config/config.php';
$key = getenv('ADMIN_PASSWORD') ?: 'faith@mabiki';
if (($_GET['key'] ?? '') !== $key) { die('Access denied. Use ?key=YOUR_ADMIN_PASSWORD'); }

$db = Database::getInstance()->getConnection();
$db->exec("DELETE FROM error_log");
echo "All error logs cleared. <a href='" . BASE_URL . "views/admin/error_log.php'>Go back</a>";
