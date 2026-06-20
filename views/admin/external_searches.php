<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/ExternalSearch.php';
requireAdmin();

$db = Database::getInstance()->getConnection();
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

// Get searches with user info
try {
    $stmt = $db->prepare(
        "SELECT es.*, u.name AS user_name, u.email AS user_email
         FROM external_searches es
         LEFT JOIN users u ON es.user_id = u.id
         ORDER BY es.created_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $searches = $stmt->fetchAll();

    $total = (int)$db->query("SELECT COUNT(*) FROM external_searches")->fetchColumn();
} catch (Exception $e) {
    $searches = [];
    $total = 0;
}

// Top queries
try {
    $topQueries = $db->query(
        "SELECT query, COUNT(*) AS cnt, SUM(results_count) AS total_results 
         FROM external_searches GROUP BY query ORDER BY cnt DESC LIMIT 10"
    )->fetchAll();
} catch (Exception $e) {
    $topQueries = [];
}

$pageTitle = 'External Search Log';
include __DIR__ . '/../layouts/header.php';
?>
<div class="d-flex flex-column min-vh-100">
<?php include __DIR__ . '/../layouts/navbar_admin.php'; ?>
<main class="flex-grow-1 py-4">
<div class="container-fluid px-4">

    <?= renderFlash() ?>

    <div class="d-flex align-items-center mb-4">
        <a href="<?= BASE_URL ?>views/admin/dashboard.php" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h1 class="h3 fw-bold mb-0"><i class="bi bi-globe text-primary"></i> External Search Log</h1>
            <p class="text-muted mb-0 small">All researcher queries to PubChem, ChEBI, and NCBI</p>
        </div>
        <span class="badge bg-primary ms-auto px-3 py-2"><?= number_format($total) ?> total searches</span>
    </div>

    <div class="row g-4">
        <!-- Search Log Table -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Recent Searches</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Researcher</th>
                                <th>Query</th>
                                <th>Type</th>
                                <th>Sources</th>
                                <th>Results</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($searches)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No external searches yet</td></tr>
                            <?php else: foreach ($searches as $s): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold small"><?= sanitize($s['user_name'] ?? '—') ?></div>
                                    <div class="text-muted" style="font-size:.7rem"><?= sanitize($s['user_email'] ?? '') ?></div>
                                </td>
                                <td><span class="fw-semibold text-primary"><?= sanitize($s['query']) ?></span></td>
                                <td><span class="badge bg-secondary"><?= sanitize($s['search_type']) ?></span></td>
                                <td><small class="text-muted"><?= sanitize($s['sources_queried'] ?? '—') ?></small></td>
                                <td><span class="badge bg-<?= $s['results_count'] > 0 ? 'success' : 'danger' ?>"><?= $s['results_count'] ?></span></td>
                                <td><small class="text-muted"><?= formatDate($s['created_at']) ?></small></td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total > $limit): ?>
                <div class="card-footer bg-white">
                    <?php
                    $pagination = paginate($total, $page, $limit);
                    include __DIR__ . '/../layouts/pagination.php';
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Queries -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-bar-chart me-2"></i>Most Searched Queries
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (empty($topQueries)): ?>
                    <li class="list-group-item text-muted text-center py-4">No data yet</li>
                    <?php else: foreach ($topQueries as $q): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <div>
                            <div class="fw-semibold small text-primary"><?= sanitize($q['query']) ?></div>
                            <div class="text-muted" style="font-size:.7rem"><?= number_format($q['total_results']) ?> total results</div>
                        </div>
                        <span class="badge bg-primary rounded-pill"><?= $q['cnt'] ?>×</span>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>
</main>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
</div>
