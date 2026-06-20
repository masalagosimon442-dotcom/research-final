<?php
/**
 * Hybrid Search API — AJAX endpoint
 * Searches local DB + PubChem + ChEBI + NCBI simultaneously
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Compound.php';
require_once __DIR__ . '/../models/Organism.php';
require_once __DIR__ . '/../helpers/ExternalSearch.php';

header('Content-Type: application/json');

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

$external = new ExternalSearch();

switch ($action) {

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
