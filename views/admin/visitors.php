<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/SiteVisit.php';
requireAdmin();

$visitModel = new SiteVisit();
$stats      = $visitModel->getStats();
$daily30    = $visitModel->getDailyVisits(30);
$topPages   = $visitModel->getTopPages(10);

$pageTitle = 'Visitor Statistics';
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
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-people text-success"></i> Visitor Statistics</h1>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Today',         $stats['today']          ?? 0, 'calendar-day',   'success'],
            ['This Week',     $stats['this_week']      ?? 0, 'calendar-week',  'primary'],
            ['This Month',    $stats['this_month']     ?? 0, 'calendar-month', 'info'],
            ['This Year',     $stats['this_year']      ?? 0, 'calendar',       'warning'],
            ['All Time',      $stats['total_all_time'] ?? 0, 'graph-up',       'secondary'],
            ['Unique IPs',    $stats['unique_visitors']?? 0, 'person-check',   'danger'],
        ];
        foreach ($cards as [$label, $val, $icon, $color]): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body py-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2"
                         style="width:52px;height:52px;background:var(--bs-<?= $color ?>-bg-subtle,#f8f9fa)">
                        <i class="bi bi-<?= $icon ?> text-<?= $color ?> fs-3"></i>
                    </div>
                    <h2 class="fw-bold mb-0"><?= number_format((int)$val) ?></h2>
                    <small class="text-muted"><?= $label ?></small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <!-- Visits Chart -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-graph-up text-success me-2"></i>Daily Visits (Last 30 Days)
                </div>
                <div class="card-body">
                    <canvas id="visitsChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Pages -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Top Pages
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (empty($topPages)): ?>
                    <li class="list-group-item text-muted text-center py-4">No data yet</li>
                    <?php else: foreach ($topPages as $p): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                        <span class="small text-truncate me-2" style="max-width:200px">
                            <?= sanitize($p['page_url'] ?? '—') ?>
                        </span>
                        <span class="badge bg-primary rounded-pill"><?= number_format($p['visits']) ?></span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels   = <?= json_encode(array_column($daily30, 'day')) ?>;
const visits   = <?= json_encode(array_column($daily30, 'visits')) ?>;
const uniques  = <?= json_encode(array_column($daily30, 'unique_visitors')) ?>;

new Chart(document.getElementById('visitsChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            { label: 'Total Visits',     data: visits,  borderColor: '#198754', backgroundColor: '#19875422', fill: true, tension: 0.3 },
            { label: 'Unique Visitors',  data: uniques, borderColor: '#0d6efd', backgroundColor: 'transparent', tension: 0.3, borderDash: [5,5] }
        ]
    },
    options: {
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
