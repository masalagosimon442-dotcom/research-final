<?php
/**
 * Quick test for hybrid search API
 * Visit: https://hazina-asili.onrender.com/test_search.php?key=faith@mabiki
 */
$key = getenv('ADMIN_PASSWORD') ?: 'faith@mabiki';
if (($_GET['key'] ?? '') !== $key) die('Access denied.');

// Simulate logged in session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user'] = ['id'=>1,'name'=>'Admin','email'=>'admin@hazina-asili.com','role'=>'admin'];
$_SESSION['last_activity'] = time();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Compound.php';
require_once __DIR__ . '/models/Organism.php';
require_once __DIR__ . '/models/ActivityLog.php';
require_once __DIR__ . '/helpers/ExternalSearch.php';

echo "<h2>Search API Test</h2><pre>";

// Test local DB
echo "1. Local DB connection: ";
try {
    $db = Database::getInstance()->getConnection();
    $count = $db->query("SELECT COUNT(*) FROM compounds")->fetchColumn();
    echo "OK ($count compounds)\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// Test ExternalSearch init
echo "2. ExternalSearch init: ";
try {
    $ext = new ExternalSearch();
    echo "OK\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// Test PubChem API
echo "3. PubChem API (Curcumin): ";
try {
    $ext = new ExternalSearch();
    $results = $ext->searchPubChem('Curcumin', 'name');
    echo count($results) . " results\n";
    if (!empty($results)) {
        echo "   First: " . ($results[0]['name'] ?? '?') . " (CID: " . ($results[0]['cid'] ?? '?') . ")\n";
    }
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// Test local search
echo "4. Local compound search (Quercetin): ";
try {
    $model = new Compound();
    $results = $model->getAll(0, 3, 'Quercetin', 'name');
    echo count($results) . " results\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

// Check tables
echo "\n5. Required tables: ";
try {
    $tables = ['site_visits', 'external_searches', 'compound_cache'];
    foreach ($tables as $t) {
        try {
            $db->query("SELECT 1 FROM $t LIMIT 1");
            echo "$t ✅ ";
        } catch (Exception $e) {
            echo "$t ❌ (missing - run setup.php) ";
        }
    }
    echo "\n";
} catch (Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
}

echo "\nAll tests complete.</pre>";
echo '<br><a href="' . (getenv('APP_URL') ?: '/') . 'setup.php?key=' . htmlspecialchars($key) . '">Run Setup (create missing tables)</a>';
