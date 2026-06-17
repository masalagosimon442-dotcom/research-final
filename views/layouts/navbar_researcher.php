<?php
require_once __DIR__ . '/../../models/Notification.php';
$unreadNotifs = (new Notification())->countUnread($_SESSION['user_id']);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top shadow" role="navigation" aria-label="Main navigation">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>views/researcher/dashboard.php" id="navLogo" style="text-decoration:none">
            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:rgba(255,255,255,0.15)">
                <span style="font-size:1.4rem">🌿</span>
            </div>
            <div class="d-flex flex-column lh-1 d-none d-md-flex">
                <span class="fw-bold text-white" style="font-size:1.1rem;letter-spacing:1px">HAZINA ASILI</span>
                <span style="font-size:.6rem;color:rgba(255,255,255,.7)">Natural Compounds DB</span>
            </div>
        </a>
        <script>
        (function(){
            var logo = document.getElementById('navLogo');
            logo.addEventListener('dblclick', function(e) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = '<?= BASE_URL ?>views/auth/admin_gate.php';
            });
        })();
        </script>

        <!-- ALWAYS VISIBLE: Icons (outside hamburger) -->
        <div class="d-flex align-items-center gap-1 order-lg-last">
            <!-- Theme toggle -->
            <button class="btn btn-link nav-link text-white p-2" onclick="toggleDarkMode()" title="Toggle theme" aria-label="Toggle dark mode">
                <i class="bi bi-moon-fill theme-icon-dark"></i>
                <i class="bi bi-sun-fill theme-icon-light"></i>
            </button>

            <!-- Notification Bell -->
            <div class="dropdown">
                <a class="btn btn-link nav-link text-white p-2 position-relative" href="#" data-bs-toggle="dropdown" aria-label="Notifications">
                    <i class="bi bi-bell fs-5"></i>
                    <?php if ($unreadNotifs > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem">
                        <?= $unreadNotifs > 9 ? '9+' : $unreadNotifs ?>
                    </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:280px;max-height:350px;overflow-y:auto">
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center border-bottom">
                        <span class="fw-semibold small">Notifications</span>
                        <a href="<?= BASE_URL ?>views/notifications.php" class="small text-success">View all</a>
                    </li>
                    <?php
                    $notifs = (new Notification())->getForUser($_SESSION['user_id'], 5);
                    if (empty($notifs)): ?>
                    <li class="px-3 py-3 text-muted small text-center">No notifications</li>
                    <?php else: foreach ($notifs as $n): ?>
                    <li>
                        <a class="dropdown-item py-2 <?= $n['is_read'] ? 'text-muted' : 'fw-semibold' ?>"
                           href="<?= $n['link'] ? sanitize($n['link']) : '#' ?>">
                            <div class="small"><?= sanitize($n['title']) ?></div>
                            <div class="text-muted" style="font-size:.7rem;white-space:normal"><?= sanitize(mb_strimwidth($n['message'],0,50,'…')) ?></div>
                        </a>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
            </div>

            <!-- Profile dropdown -->
            <div class="dropdown">
                <a class="btn btn-link nav-link text-white p-2 d-flex align-items-center gap-1" href="#" data-bs-toggle="dropdown" aria-label="Profile">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-md-inline small"><?= sanitize(currentUser()['name']) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold"><?= sanitize(currentUser()['name']) ?></div>
                        <div class="text-muted small"><?= sanitize(currentUser()['email']) ?></div>
                    </li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>views/profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>views/notifications.php"><i class="bi bi-bell me-2"></i>Notifications <?php if ($unreadNotifs): ?><span class="badge bg-warning text-dark"><?= $unreadNotifs ?></span><?php endif; ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?= BASE_URL ?>controllers/process.php">
                            <input type="hidden" name="action" value="logout">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            <!-- Hamburger (for nav links only) -->
            <button class="navbar-toggler border-0 ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#researcherNav" aria-controls="researcherNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- COLLAPSIBLE: Navigation links only -->
        <div class="collapse navbar-collapse" id="researcherNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= activeNav('dashboard.php') ?>" href="<?= BASE_URL ?>views/researcher/dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>views/researcher/compounds/index.php">
                        <i class="bi bi-capsule"></i> Compounds
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-tools"></i> Tools
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/compounds/advanced_search.php"><i class="bi bi-funnel me-2"></i>Advanced Search</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/compounds/compare.php"><i class="bi bi-arrows-angle-expand me-2"></i>Compare Compounds</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>views/researcher/organisms/index.php">
                        <i class="bi bi-tree"></i> Organisms
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-square-text"></i> Submissions
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/insights/index.php">My Insights</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/insights/create.php">Submit Insight</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/recommendations/index.php">My Recommendations</a></li>
                        <li><a class="dropdown-item" href="<?= BASE_URL ?>views/researcher/recommendations/create.php">Submit Recommendation</a></li>
                    </ul>
                </li>
            </ul>
            <!-- Search (inside collapse for mobile) -->
            <form class="d-flex d-lg-inline-flex" style="max-width:200px" action="<?= BASE_URL ?>views/researcher/compounds/index.php" method="GET">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control form-control-sm border-light-subtle" placeholder="Search... (Ctrl+K)" aria-label="Search">
                </div>
            </form>
        </div>
    </div>
</nav>
