<?php
/**
 * Hybrid Search API — AJAX endpoint
 * Searches local DB + PubChem + ChEBI + NCBI simultaneously
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Compound.php';
require_once __DIR__ . '/../models/Organism.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../helpers/ExternalSearch.php';

header('Content-Type: application/json');

// Catch all errors and return JSON
set_exception_handler(function($e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    exit;
});

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');
$query  = trim($_POST['query'] ?? $_GET['query'] ?? '');
$type   = sanitize($_POST['type'] ?? $_GET['type'] ?? 'name');

if (empty($query)) {
    echo json_encode(['success' => false, 'error' => 'Query is required.']);
    exit;
}

// ── Rate limit: 20 external searches per user per hour ────────
$uid = $_SESSION['user_id'];
$db  = Database::getInstance()->getConnection();
try {
    if (DB_DRIVER === 'pgsql') {
        $countStmt = $db->prepare(
            "SELECT COUNT(*) FROM external_searches 
             WHERE user_id = ? AND created_at >= NOW() - INTERVAL '1 hour'"
        );
    } else {
        $countStmt = $db->prepare(
            "SELECT COUNT(*) FROM external_searches 
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        );
    }
    $countStmt->execute([$uid]);
    $searchesThisHour = (int)$countStmt->fetchColumn();

    if ($searchesThisHour >= 20) {
        echo json_encode([
            'success' => false,
            'error'   => 'Rate limit reached. You have made 20 external searches this hour. Please wait before searching again.',
            'rate_limited' => true,
        ]);
        exit;
    }
} catch (Exception $e) {
    // If table doesn't exist yet, allow search
}

$external = new ExternalSearch();

switch ($action) {

    // ── Sequential workflow: Local → PubChem → NCBI Taxonomy ──────────────────
    case 'search_sequential':
        $compoundModel = new Compound();
        $orgModel      = new Organism();

        // ── Step 1: Local Database ───────────────────────────────────────────
        $localCompounds = $compoundModel->getAll(0, 10, $query, $type === 'formula' ? 'formula' : 'name');
        $localOrganisms = [];

        if ($type === 'organism' || $type === 'name') {
            $localOrganisms = $orgModel->getAll(0, 5, $query);
        }

        $localFound = count($localCompounds) + count($localOrganisms);

        // ── Step 2: PubChem (only if local found nothing) ────────────────────
        $pubchemResults = [];
        $pubchemSearched = false;
        if ($localFound === 0 && in_array($type, ['name', 'formula', 'smiles'])) {
            $pubchemResults  = $external->searchPubChem($query, $type);
            $pubchemSearched = true;
        }

        // ── Step 2b: ChEBI (alongside PubChem when local found nothing) ──────
        $chebiResults = [];
        $chebiSearched = false;
        if ($localFound === 0 && in_array($type, ['name', 'formula', 'smiles'])) {
            $chebiResults  = $external->searchChEBI($query);
            $chebiSearched = true;
        }

        // ── Step 3: NCBI Taxonomy (only if local + PubChem found nothing) ────
        $ncbiResults = [];
        $ncbiSearched = false;
        if ($localFound === 0 && empty($pubchemResults) && in_array($type, ['name', 'organism'])) {
            $ncbiResults  = $external->searchNCBI($query);
            $ncbiSearched = true;
        }

        // ── PubMed count (bonus metadata when we have any compound results) ──
        $pubmedCount = 0;
        if ($type === 'name' && ($localFound > 0 || !empty($pubchemResults))) {
            $pubmedCount = $external->getPubMedCount($query);
        }

        // ── Log ──────────────────────────────────────────────────────────────
        $sources = ['local'];
        if ($pubchemSearched) $sources[] = 'PubChem';
        if ($chebiSearched)   $sources[] = 'ChEBI';
        if ($ncbiSearched)    $sources[] = 'NCBI';

        $totalResults = $localFound + count($pubchemResults) + count($chebiResults) + count($ncbiResults);
        $external->logSearch($_SESSION['user_id'], $query, $type, $sources, $totalResults);

        (new ActivityLog())->log(
            $_SESSION['user_id'], 'sequential_search',
            "Searched: \"{$query}\" [{$type}] — {$totalResults} results from " . implode(' → ', $sources)
        );

        echo json_encode([
            'success'          => true,
            'query'            => $query,
            'type'             => $type,
            'workflow' => [
                'local'   => ['searched' => true,             'found' => $localFound],
                'pubchem' => ['searched' => $pubchemSearched, 'found' => count($pubchemResults)],
                'chebi'   => ['searched' => $chebiSearched,   'found' => count($chebiResults)],
                'ncbi'    => ['searched' => $ncbiSearched,    'found' => count($ncbiResults)],
            ],
            'local_compounds'  => $localCompounds,
            'local_organisms'  => $localOrganisms,
            'pubchem'          => $pubchemResults,
            'chebi'            => $chebiResults,
            'ncbi'             => $ncbiResults,
            'pubmed_count'     => $pubmedCount,
            'total_results'    => $totalResults,
        ]);
        break;

    case 'search_all':
        // Search local DB first
        $compoundModel = new Compound();
        $localCompounds = $compoundModel->getAll(0, 5, $query, $type === 'formula' ? 'formula' : 'name');
        $localOrganisms = [];

        if ($type === 'organism' || $type === 'name') {
            $orgModel = new Organism();
            $localOrganisms = $orgModel->getAll(0, 3, $query);
        }

        // Search external sources
        $externalResults = $external->searchAll($query, $type);

        // Get PubMed count for name search
        $pubmedCount = 0;
        if ($type === 'name' && !empty($localCompounds)) {
            $pubmedCount = $external->getPubMedCount($query);
        }

        // Log search
        $sources = ['local'];
        if (!empty($externalResults['pubchem'])) $sources[] = 'PubChem';
        if (!empty($externalResults['chebi']))   $sources[] = 'ChEBI';
        if (!empty($externalResults['ncbi']))    $sources[] = 'NCBI';

        $totalResults = count($localCompounds) + count($localOrganisms) +
                       count($externalResults['pubchem']) + count($externalResults['chebi']) + count($externalResults['ncbi']);

        $external->logSearch($_SESSION['user_id'], $query, $type, $sources, $totalResults);

        // Log activity
        (new ActivityLog())->log(
            $_SESSION['user_id'], 'hybrid_search',
            "Searched: \"{$query}\" [{$type}] — {$totalResults} results from " . implode(', ', $sources)
        );

        echo json_encode([
            'success'         => true,
            'query'           => $query,
            'type'            => $type,
            'local_compounds' => $localCompounds,
            'local_organisms' => $localOrganisms,
            'pubchem'         => $externalResults['pubchem'],
            'chebi'           => $externalResults['chebi'],
            'ncbi'            => $externalResults['ncbi'],
            'pubmed_count'    => $pubmedCount,
            'total_results'   => $totalResults,
        ]);
        break;

    case 'pubchem_detail':
        $cid = (int)($_POST['cid'] ?? $_GET['cid'] ?? 0);
        if (!$cid) {
            echo json_encode(['success' => false, 'error' => 'CID required.']);
            exit;
        }
        $detail = $external->getPubChemDetail($cid);
        echo json_encode(['success' => $detail !== null, 'data' => $detail]);
        break;

    case 'ncbi_detail':
        $taxId = sanitize($_POST['tax_id'] ?? $_GET['tax_id'] ?? '');
        if (!$taxId) {
            echo json_encode(['success' => false, 'error' => 'Tax ID required.']);
            exit;
        }
        $detail = $external->getNCBIDetail($taxId);
        echo json_encode(['success' => $detail !== null, 'data' => $detail]);
        break;

    case 'pubmed_count':
        $count = $external->getPubMedCount($query);
        echo json_encode(['success' => true, 'count' => $count, 'url' => 'https://pubmed.ncbi.nlm.nih.gov/?term=' . urlencode($query)]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
}
