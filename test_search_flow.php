<?php
/**
 * Test the full search flow
 * Visit: https://hazina-asili.onrender.com/test_search_flow.php?key=faith@mabiki
 */
$key = getenv('ADMIN_PASSWORD') ?: 'faith@mabiki';
if (($_GET['key'] ?? '') !== $key) die('Access denied. Use ?key=YOUR_ADMIN_PASSWORD');

session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user'] = ['id'=>1,'name'=>'Admin','email'=>'admin@hazina-asili.com','role'=>'admin'];
$_SESSION['last_activity'] = time();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = '/test';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'TestBot';

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Compound.php';
require_once __DIR__ . '/models/Organism.php';
require_once __DIR__ . '/models/ActivityLog.php';
require_once __DIR__ . '/helpers/ExternalSearch.php';

echo "<style>body{font-family:monospace;padding:20px;} .ok{color:green} .fail{color:red} .info{color:blue}</style>";
echo "<h2>🔬 Search Flow Verification</h2>";

$allPass = true;

// ── 1. Local DB Search ──────────────────────────────────────
echo "<h3>1. Local Database Search</h3>";
try {
    $model = new Compound();
    
    // Test by name
    $r = $model->getAll(0, 3, 'Quercetin', 'name');
    echo count($r) > 0 
        ? "<p class='ok'>✅ Search by name 'Quercetin': " . count($r) . " result(s)</p>"
        : "<p class='fail'>❌ Search by name returned 0 results</p>";
    
    // Test by formula
    $r2 = $model->getAll(0, 3, 'C21H20O6', 'formula');
    echo count($r2) > 0
        ? "<p class='ok'>✅ Search by formula 'C21H20O6': " . count($r2) . " result(s)</p>"
        : "<p class='info'>ℹ️ Search by formula: 0 results (ok if formula differs)</p>";
    
    // Test organisms
    $orgModel = new Organism();
    $orgs = $orgModel->getAll(0, 3, 'Curcuma');
    echo count($orgs) > 0
        ? "<p class='ok'>✅ Organism search 'Curcuma': " . count($orgs) . " result(s)</p>"
        : "<p class='info'>ℹ️ Organism search: 0 results</p>";
        
} catch (Exception $e) {
    echo "<p class='fail'>❌ Local DB error: " . htmlspecialchars($e->getMessage()) . "</p>";
    $allPass = false;
}

// ── 2. External Search Init ─────────────────────────────────
echo "<h3>2. External Search Init</h3>";
try {
    $ext = new ExternalSearch();
    echo "<p class='ok'>✅ ExternalSearch initialized (tables auto-created)</p>";
} catch (Exception $e) {
    echo "<p class='fail'>❌ ExternalSearch init failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    $allPass = false;
}

// ── 3. PubChem API ──────────────────────────────────────────
echo "<h3>3. PubChem API Test</h3>";
try {
    $ext = new ExternalSearch();
    $results = $ext->searchPubChem('Curcumin', 'name');
    if (count($results) > 0) {
        echo "<p class='ok'>✅ PubChem returned " . count($results) . " result(s)</p>";
        echo "<p class='info'>First result: " . htmlspecialchars($results[0]['name'] ?? 'N/A') . 
             " | CID: " . ($results[0]['cid'] ?? 'N/A') . 
             " | Formula: " . ($results[0]['formula'] ?? 'N/A') . "</p>";
        if (!empty($results[0]['image_url'])) {
            echo "<img src='" . htmlspecialchars($results[0]['image_url']) . "' style='max-height:100px;border:1px solid #ccc'> <br>";
        }
    } else {
        echo "<p class='fail'>❌ PubChem returned 0 results (may be rate limited or no internet)</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>❌ PubChem error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// ── 4. Full Combined Search ─────────────────────────────────
echo "<h3>4. Full Combined Search ('Quercetin')</h3>";
try {
    $ext = new ExternalSearch();
    $allResults = $ext->searchAll('Quercetin', 'name');
    
    $localCompounds = (new Compound())->getAll(0, 5, 'Quercetin', 'name');
    
    $total = count($localCompounds) + 
             count($allResults['pubchem']) + 
             count($allResults['chebi']) + 
             count($allResults['ncbi']);
    
    echo "<p class='ok'>✅ Combined search complete: $total total results</p>";
    echo "<ul>";
    echo "<li>Local DB: " . count($localCompounds) . " compounds</li>";
    echo "<li>PubChem: " . count($allResults['pubchem']) . " compounds</li>";
    echo "<li>ChEBI: " . count($allResults['chebi']) . " entities</li>";
    echo "<li>NCBI: " . count($allResults['ncbi']) . " organisms</li>";
    echo "</ul>";
    
    if ($total > 0) {
        echo "<p class='ok'>✅ Search shows results — feature WORKS</p>";
    } else {
        echo "<p class='fail'>❌ No results — check internet/API access</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='fail'>❌ Combined search error: " . htmlspecialchars($e->getMessage()) . "</p>";
    $allPass = false;
}

// ── 5. Rate Limit Check ─────────────────────────────────────
echo "<h3>5. Rate Limit</h3>";
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        DB_DRIVER === 'pgsql'
            ? "SELECT COUNT(*) FROM external_searches WHERE user_id = 1 AND created_at >= NOW() - INTERVAL '1 hour'"
            : "SELECT COUNT(*) FROM external_searches WHERE user_id = 1 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
    );
    $stmt->execute();
    $count = (int)$stmt->fetchColumn();
    $remaining = 20 - $count;
    echo "<p class='ok'>✅ Rate limit: $count/20 searches used this hour ($remaining remaining)</p>";
} catch (Exception $e) {
    echo "<p class='info'>ℹ️ Rate limit table not ready yet (run setup.php first)</p>";
}

// ── 6. Required Tables ──────────────────────────────────────
echo "<h3>6. Required Tables</h3>";
try {
    $db = Database::getInstance()->getConnection();
    $tables = DB_DRIVER === 'pgsql'
        ? $db->query("SELECT tablename FROM pg_tables WHERE schemaname='public'")->fetchAll(PDO::FETCH_COLUMN)
        : $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    $required = ['site_visits','external_searches','compound_cache'];
    foreach ($required as $t) {
        echo in_array($t, $tables)
            ? "<p class='ok'>✅ Table '$t' exists</p>"
            : "<p class='fail'>❌ Table '$t' MISSING — run setup.php</p>";
    }
} catch (Exception $e) {
    echo "<p class='fail'>❌ Table check failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<h3>" . ($allPass ? "🎉 All core checks passed!" : "⚠️ Some checks failed") . "</h3>";
echo "<p><a href='" . BASE_URL . "views/researcher/research_search.php'>→ Open Research Search Page</a></p>";
echo "<p><a href='" . BASE_URL . "setup.php?key=$key'>→ Run Setup (fix missing tables)</a></p>";
