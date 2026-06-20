<?php
require_once __DIR__ . '/../../config/config.php';
requireResearcher();

$pageTitle = 'Research Search';
$navFile   = isAdmin() && !empty($_SESSION['admin_secret_access'])
    ? __DIR__ . '/../layouts/navbar_admin.php'
    : __DIR__ . '/../layouts/navbar_researcher.php';
include __DIR__ . '/../layouts/header.php';
?>
<div class="d-flex flex-column min-vh-100">
<?php include $navFile; ?>
<main class="flex-grow-1 py-4" id="main-content">
<div class="container-fluid px-4" style="max-width:1200px">

    <?= renderFlash() ?>

    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-search text-success"></i> Research Search</h1>
            <p class="text-muted mb-0 small">Search compounds & organisms from our comprehensive scientific database</p>
        </div>
    </div>

    <!-- Search Panel -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <!-- Search method tabs -->
            <ul class="nav nav-pills mb-4" id="searchTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-tab="name">
                        <i class="bi bi-type me-1"></i> By Name
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-tab="formula">
                        <i class="bi bi-grid-3x2 me-1"></i> By Formula
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-tab="organism">
                        <i class="bi bi-tree me-1"></i> By Organism
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-tab="smiles">
                        <i class="bi bi-bezier2 me-1"></i> By SMILES
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-tab="draw">
                        <i class="bi bi-pencil-square me-1"></i> Draw Structure
                    </button>
                </li>
            </ul>

            <!-- By Name -->
            <div class="tab-content" id="tab-name">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" id="searchName" class="form-control form-control-lg"
                               placeholder="Enter compound or organism name (e.g. Curcumin, Quercetin, Aspirin...)"
                               autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg w-100" onclick="doSearch('name')">
                            <i class="bi bi-search me-2"></i>Search All Sources
                        </button>
                    </div>
                </div>
            </div>

            <!-- By Formula -->
            <div class="tab-content d-none" id="tab-formula">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" id="searchFormula" class="form-control form-control-lg"
                               placeholder="Enter molecular formula (e.g. C21H20O6, C6H12O6...)"
                               autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg w-100" onclick="doSearch('formula')">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- By Organism -->
            <div class="tab-content d-none" id="tab-organism">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" id="searchOrganism" class="form-control form-control-lg"
                               placeholder="Enter organism name (e.g. Curcuma longa, Allium sativum...)"
                               autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg w-100" onclick="doSearch('organism')">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- By SMILES -->
            <div class="tab-content d-none" id="tab-smiles">
                <div class="row g-3">
                    <div class="col-md-9">
                        <input type="text" id="searchSmiles" class="form-control form-control-lg font-monospace"
                               placeholder="Enter SMILES string (e.g. COc1cc(/C=C/C(=O)...)..."
                               autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-success btn-lg w-100" onclick="doSearch('smiles')">
                            <i class="bi bi-search me-2"></i>Search by SMILES
                        </button>
                    </div>
                </div>
            </div>

            <!-- Draw Structure -->
            <div class="tab-content d-none" id="tab-draw">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="border rounded p-2 bg-light" style="min-height:300px">
                            <div id="jsme_container" style="width:100%;height:300px"></div>
                        </div>
                        <small class="text-muted">Draw your molecule using the structure editor above</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Generated SMILES:</label>
                        <textarea id="drawSmiles" class="form-control font-monospace mb-3" rows="3" readonly
                                  placeholder="SMILES will appear here..."></textarea>
                        <button class="btn btn-success w-100" onclick="doSearch('smiles', document.getElementById('drawSmiles').value)">
                            <i class="bi bi-search me-2"></i>Search by Drawing
                        </button>
                        <hr>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-1"></i>
                            Draw a molecule structure and click Search to find matching compounds in local DB and PubChem.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sequential Search Workflow Progress -->
    <div id="searchLoading" class="d-none mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-semibold mb-3 text-center text-muted">
                    <span id="searchingQueryLabel"></span>
                </h6>
                <div class="d-flex flex-column gap-3" id="workflowSteps">

                    <!-- Step 1: Local DB -->
                    <div class="d-flex align-items-center gap-3 p-3 rounded border" id="step-local" style="transition:all .3s">
                        <div id="step-local-icon" class="fs-4 text-muted" style="width:2rem;text-align:center">
                            <span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Step 1 — Local Database</div>
                            <div class="text-muted small" id="step-local-msg">Searching our internal compound &amp; organism records…</div>
                        </div>
                        <span class="badge bg-secondary" id="step-local-badge"></span>
                    </div>

                    <!-- Step 2: PubChem -->
                    <div class="d-flex align-items-center gap-3 p-3 rounded border border-secondary-subtle text-muted" id="step-pubchem" style="opacity:.4;transition:all .3s">
                        <div id="step-pubchem-icon" class="fs-4" style="width:2rem;text-align:center">
                            <i class="bi bi-capsule"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Step 2 — PubChem</div>
                            <div class="text-muted small" id="step-pubchem-msg">Will search NCBI PubChem if local returns nothing</div>
                        </div>
                        <span class="badge bg-secondary d-none" id="step-pubchem-badge"></span>
                    </div>

                    <!-- Step 3: NCBI Taxonomy -->
                    <div class="d-flex align-items-center gap-3 p-3 rounded border border-secondary-subtle text-muted" id="step-ncbi" style="opacity:.4;transition:all .3s">
                        <div id="step-ncbi-icon" class="fs-4" style="width:2rem;text-align:center">
                            <i class="bi bi-tree"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Step 3 — NCBI Taxonomy</div>
                            <div class="text-muted small" id="step-ncbi-msg">Will search NCBI organisms if PubChem returns nothing</div>
                        </div>
                        <span class="badge bg-secondary d-none" id="step-ncbi-badge"></span>
                    </div>

                    <!-- Step 4: Display Results -->
                    <div class="d-flex align-items-center gap-3 p-3 rounded border border-secondary-subtle text-muted" id="step-display" style="opacity:.4;transition:all .3s">
                        <div class="fs-4" style="width:2rem;text-align:center">
                            <i class="bi bi-layout-text-window-reverse"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Step 4 — Display Results</div>
                            <div class="text-muted small" id="step-display-msg">Rendering final results</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div id="searchResults" class="d-none">

        <!-- Summary bar -->
        <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded">
            <i class="bi bi-check-circle text-success fs-5"></i>
            <span id="resultsSummary" class="fw-semibold"></span>
            <div class="ms-auto d-flex gap-2" id="resultsBadges"></div>
        </div>

        <!-- Local Results -->
        <div id="localResults" class="mb-4"></div>

        <!-- PubChem Results -->
        <div id="pubchemResults" class="mb-4"></div>

        <!-- ChEBI Results -->
        <div id="chebiResults" class="mb-4"></div>

        <!-- NCBI Results -->
        <div id="ncbiResults" class="mb-4"></div>

    </div>

    <!-- No results -->
    <div id="noResults" class="d-none text-center py-5">
        <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
        <h5 class="text-muted">No results found</h5>
        <p class="text-muted small">Try a different search term or search method.</p>
    </div>

</div>
</main>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
</div>

<!-- JSME Structure Editor (free, open source) -->
<script src="https://jsme-editor.github.io/dist/jsme.nocache.js"></script>
<script>
var jsmeApp = null;

function jsmeOnLoad() {
    jsmeApp = new JSApplet.JSME('jsme_container', '100%', '300px', {
        options: 'query,hydrogens'
    });
    jsmeApp.setCallBack('AtomHighlighted', function() {
        var smiles = jsmeApp.smiles();
        document.getElementById('drawSmiles').value = smiles || '';
    });
}

// Tab switching
document.querySelectorAll('[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.add('d-none'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('d-none');
    });
});

// Enter key triggers search
['searchName','searchFormula','searchOrganism','searchSmiles'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            var tab = id.replace('search','').toLowerCase();
            doSearch(tab);
        }
    });
});

function doSearch(type, customQuery) {
    var query = customQuery || '';
    if (!query) {
        var inputMap = { name: 'searchName', formula: 'searchFormula', organism: 'searchOrganism', smiles: 'searchSmiles' };
        var el = document.getElementById(inputMap[type]);
        query = el ? el.value.trim() : '';
    }

    if (!query) {
        alert('Please enter a search term.');
        return;
    }

    // ── Reset UI ──────────────────────────────────────────────────────────────
    document.getElementById('searchLoading').classList.remove('d-none');
    document.getElementById('searchResults').classList.add('d-none');
    document.getElementById('noResults').classList.add('d-none');

    document.getElementById('searchingQueryLabel').textContent = 'Searching for "' + query + '"…';

    // Reset all steps to pending state
    resetStepUI();

    // Mark Step 1 as active (spinner already there from reset)
    setStepActive('step-local');

    var BASE = document.querySelector('meta[name="base-url"]').content;
    var formData = new FormData();
    formData.append('action', 'search_sequential');
    formData.append('query', query);
    formData.append('type', type);

    // Simulate step-by-step visual progress while waiting for the single response
    // Step 1 fires immediately; steps 2/3 animate in after short delays if search takes time
    var step2Timer = setTimeout(function() { setStepActive('step-pubchem'); }, 1200);
    var step3Timer = setTimeout(function() { setStepActive('step-ncbi'); }, 2400);

    fetch(BASE + 'controllers/api_hybrid_search.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        clearTimeout(step2Timer);
        clearTimeout(step3Timer);
        document.getElementById('searchLoading').classList.add('d-none');

        if (!data.success) {
            if (data.rate_limited) {
                document.getElementById('noResults').classList.remove('d-none');
                document.getElementById('noResults').innerHTML =
                    '<i class="bi bi-hourglass-split fs-1 text-warning d-block mb-3"></i>' +
                    '<h5 class="text-warning">Rate Limit Reached</h5>' +
                    '<p class="text-muted small">You have made 20 external searches this hour.<br>Please wait before searching again.<br>Your local database search still works — try browsing <a href="' + BASE + 'views/researcher/compounds/index.php">Compounds</a>.</p>';
            } else {
                alert('Search error: ' + data.error);
            }
            return;
        }

        // ── Animate workflow step results ─────────────────────────────────
        var wf = data.workflow || {};

        // Step 1: Local
        if (wf.local && wf.local.found > 0) {
            setStepDone('step-local', wf.local.found + ' result' + (wf.local.found !== 1 ? 's' : '') + ' found', 'success');
        } else {
            setStepDone('step-local', 'Not found', 'secondary');
        }

        // Step 2: PubChem
        if (wf.pubchem && wf.pubchem.searched) {
            if (wf.pubchem.found > 0) {
                setStepDone('step-pubchem', wf.pubchem.found + ' result' + (wf.pubchem.found !== 1 ? 's' : '') + ' found', 'primary');
            } else {
                setStepDone('step-pubchem', 'Not found', 'secondary');
            }
        } else {
            setStepSkipped('step-pubchem', 'Skipped — local results found');
        }

        // Step 3: NCBI
        if (wf.ncbi && wf.ncbi.searched) {
            if (wf.ncbi.found > 0) {
                setStepDone('step-ncbi', wf.ncbi.found + ' result' + (wf.ncbi.found !== 1 ? 's' : '') + ' found', 'warning');
            } else {
                setStepDone('step-ncbi', 'Not found', 'secondary');
            }
        } else {
            setStepSkipped('step-ncbi', 'Skipped — results found earlier');
        }

        // Step 4: Display
        setStepDone('step-display', 'Rendering ' + data.total_results + ' result' + (data.total_results !== 1 ? 's' : ''), 'success');

        // Brief pause so user sees the completed workflow before results appear
        setTimeout(function() {
            document.getElementById('searchLoading').classList.add('d-none');
            renderResults(data, query);
        }, 600);
    })
    .catch(function() {
        clearTimeout(step2Timer);
        clearTimeout(step3Timer);
        document.getElementById('searchLoading').classList.add('d-none');
        alert('Network error. Please try again.');
    });
}

// ── Workflow Step Helpers ─────────────────────────────────────────────────────

function resetStepUI() {
    ['step-local','step-pubchem','step-ncbi','step-display'].forEach(function(id) {
        var el = document.getElementById(id);
        el.style.opacity = '0.4';
        el.classList.remove('border-success','border-primary','border-warning','bg-success-subtle','bg-primary-subtle','bg-warning-subtle');
        el.classList.add('border-secondary-subtle');
    });
    // Restore step-1 spinner icon
    document.getElementById('step-local-icon').innerHTML = '<span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>';
    document.getElementById('step-local-msg').textContent = 'Searching our internal compound & organism records…';

    // Restore step-2/3/4 icons
    document.getElementById('step-pubchem-icon').innerHTML = '<i class="bi bi-capsule"></i>';
    document.getElementById('step-pubchem-msg').textContent = 'Will search NCBI PubChem if local returns nothing';

    document.getElementById('step-ncbi-icon').innerHTML = '<i class="bi bi-tree"></i>';
    document.getElementById('step-ncbi-msg').textContent = 'Will search NCBI organisms if PubChem returns nothing';

    document.getElementById('step-display-msg').textContent = 'Rendering final results';

    ['step-local-badge','step-pubchem-badge','step-ncbi-badge'].forEach(function(id) {
        var b = document.getElementById(id);
        b.textContent = '';
        b.className = 'badge bg-secondary d-none';
    });
}

function setStepActive(id) {
    var el = document.getElementById(id);
    el.style.opacity = '1';
    el.classList.remove('border-secondary-subtle');
    el.classList.add('border-success');
    // Replace icon with spinner
    var iconEl = document.getElementById(id + '-icon');
    if (iconEl) iconEl.innerHTML = '<span class="spinner-border spinner-border-sm text-success" role="status" aria-hidden="true"></span>';
}

function setStepDone(id, message, color) {
    var el = document.getElementById(id);
    el.style.opacity = '1';
    el.classList.remove('border-secondary-subtle','border-success');
    el.classList.add('border-' + color);

    var iconEl = document.getElementById(id + '-icon');
    var msgEl  = document.getElementById(id + '-msg');
    var badge  = document.getElementById(id + '-badge');

    var iconMap = { success: 'bi-check-circle-fill text-success', primary: 'bi-check-circle-fill text-primary', warning: 'bi-check-circle-fill text-warning', secondary: 'bi-x-circle text-secondary' };
    if (iconEl) iconEl.innerHTML = '<i class="bi ' + (iconMap[color] || 'bi-check-circle-fill') + '"></i>';
    if (msgEl && message) msgEl.textContent = message;
    if (badge) {
        badge.textContent = message;
        badge.className = 'badge bg-' + color;
    }
}

function setStepSkipped(id, message) {
    var el = document.getElementById(id);
    el.style.opacity = '0.5';
    el.classList.add('border-secondary-subtle');
    var msgEl = document.getElementById(id + '-msg');
    if (msgEl) msgEl.textContent = message;
    var iconEl = document.getElementById(id + '-icon');
    if (iconEl) iconEl.innerHTML = '<i class="bi bi-skip-forward text-muted"></i>';
}


function renderResults(data, query) {
    var hasResults = data.total_results > 0;

    if (!hasResults) {
        document.getElementById('noResults').classList.remove('d-none');
        document.getElementById('noResults').innerHTML =
            '<i class="bi bi-search fs-1 text-muted d-block mb-3"></i>' +
            '<h5 class="text-muted">No results found</h5>' +
            '<p class="text-muted small">Searched local database' +
            (data.workflow && data.workflow.pubchem && data.workflow.pubchem.searched ? ', PubChem' : '') +
            (data.workflow && data.workflow.ncbi && data.workflow.ncbi.searched ? ', and NCBI Taxonomy' : '') +
            '. Try a different search term or method.</p>';
        return;
    }

    document.getElementById('searchResults').classList.remove('d-none');

    // ── Summary bar ───────────────────────────────────────────────────────────
    var wf = data.workflow || {};
    var sources = ['Local DB'];
    if (wf.pubchem && wf.pubchem.searched) sources.push('PubChem');
    if (wf.ncbi    && wf.ncbi.searched)    sources.push('NCBI Taxonomy');

    document.getElementById('resultsSummary').textContent =
        data.total_results + ' result' + (data.total_results !== 1 ? 's' : '') + ' for "' + query + '" — searched ' + sources.join(' → ');

    var badgesHtml = '<span class="badge bg-success px-3">' + data.total_results + ' result' + (data.total_results !== 1 ? 's' : '') + '</span>';
    if (data.pubmed_count > 0) {
        badgesHtml += ' <a href="https://pubmed.ncbi.nlm.nih.gov/?term=' + encodeURIComponent(query) + '" target="_blank" class="badge bg-info text-decoration-none">' +
            data.pubmed_count.toLocaleString() + ' PubMed papers</a>';
    }
    document.getElementById('resultsBadges').innerHTML = badgesHtml;

    var BASE = document.querySelector('meta[name="base-url"]').content;

    // ── Step 1 Results: Local Compounds ──────────────────────────────────────
    var localHtml = '';
    if (data.local_compounds && data.local_compounds.length > 0) {
        localHtml += '<div class="card border-0 shadow-sm mb-3">' +
            '<div class="card-header bg-success text-white fw-semibold">' +
            '<i class="bi bi-database me-2"></i>Local Compounds (' + data.local_compounds.length + ')' +
            '<span class="badge bg-white text-success ms-2 small">Step 1 — Local DB</span>' +
            '</div><div class="row g-3 p-3">';
        data.local_compounds.forEach(function(c) {
            localHtml += '<div class="col-md-6"><div class="card border-success h-100">' +
                '<div class="card-body">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                '<h6 class="fw-bold mb-0">' + escHtml(c.name) + '</h6>' +
                '<span class="badge bg-success">Verified</span></div>' +
                '<code class="text-success small">' + escHtml(c.formula) + '</code>' +
                '<div class="text-muted small mt-1"><i class="bi bi-speedometer2 me-1"></i>' + parseFloat(c.molecular_weight || 0).toFixed(2) + ' g/mol</div>' +
                (c.organism_name ? '<div class="text-muted small"><i class="bi bi-tree me-1"></i>' + escHtml(c.organism_name) + '</div>' : '') +
                (c.description ? '<p class="small text-muted mt-2 mb-0">' + escHtml(c.description.substring(0, 100)) + '…</p>' : '') +
                '</div>' +
                '<div class="card-footer bg-white border-0">' +
                '<a href="' + BASE + 'views/admin/compounds/view.php?id=' + c.id + '" class="btn btn-sm btn-success w-100"><i class="bi bi-eye me-1"></i>View Full Details</a>' +
                '</div></div></div>';
        });
        localHtml += '</div></div>';
    }

    // Local Organisms
    if (data.local_organisms && data.local_organisms.length > 0) {
        localHtml += '<div class="card border-0 shadow-sm mb-3">' +
            '<div class="card-header bg-success text-white fw-semibold">' +
            '<i class="bi bi-tree me-2"></i>Local Organisms (' + data.local_organisms.length + ')' +
            '<span class="badge bg-white text-success ms-2 small">Step 1 — Local DB</span>' +
            '</div><div class="row g-3 p-3">';
        data.local_organisms.forEach(function(o) {
            localHtml += '<div class="col-md-6"><div class="card border-success h-100"><div class="card-body">' +
                '<h6 class="fw-bold fst-italic">' + escHtml(o.scientific_name) + '</h6>' +
                '<div class="small text-muted"><span class="badge bg-success me-1">' + escHtml(o.kingdom || '') + '</span>' +
                escHtml(o.phylum || '') + (o.class ? ' › ' + escHtml(o.class) : '') + '</div>' +
                '</div><div class="card-footer bg-white border-0">' +
                '<a href="' + BASE + 'views/researcher/organisms/view.php?id=' + o.id + '" class="btn btn-sm btn-success w-100"><i class="bi bi-eye me-1"></i>View Details</a>' +
                '</div></div></div>';
        });
        localHtml += '</div></div>';
    }
    document.getElementById('localResults').innerHTML = localHtml;

    // ── Step 2 Results: PubChem ───────────────────────────────────────────────
    var pubchemHtml = '';
    if (data.pubchem && data.pubchem.length > 0) {
        pubchemHtml = '<div class="card border-0 shadow-sm mb-3">' +
            '<div class="card-header bg-primary text-white fw-semibold">' +
            '<i class="bi bi-capsule me-2"></i>PubChem Results (' + data.pubchem.length + ')' +
            '<span class="badge bg-white text-primary ms-2 small">Step 2 — PubChem</span>' +
            '</div><div class="row g-3 p-3">';
        data.pubchem.forEach(function(c) {
            pubchemHtml += '<div class="col-md-6"><div class="card border-primary h-100">' +
                '<div class="card-body">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                '<h6 class="fw-bold mb-0">' + escHtml(c.name) + '</h6>' +
                '<span class="badge bg-primary">PubChem</span></div>' +
                (c.iupac_name ? '<div class="text-muted" style="font-size:.7rem;font-style:italic">' + escHtml(c.iupac_name.substring(0, 80)) + '</div>' : '') +
                '<code class="text-primary small">' + escHtml(c.formula || '') + '</code>' +
                '<div class="text-muted small mt-1"><i class="bi bi-speedometer2 me-1"></i>' + (c.molecular_weight || '—') + ' g/mol</div>' +
                (c.inchikey ? '<div class="text-muted" style="font-size:.7rem"><strong>InChIKey:</strong> ' + escHtml(c.inchikey) + '</div>' : '') +
                (c.smiles ? '<div class="mt-2"><small class="text-muted">SMILES:</small><div class="font-monospace" style="font-size:.65rem;word-break:break-all;color:#0d6efd">' + escHtml(c.smiles.substring(0, 100)) + '</div></div>' : '') +
                (c.image_url ? '<img src="' + escHtml(c.image_url) + '" class="img-fluid mt-2 rounded" style="max-height:120px" alt="Structure" onerror="this.style.display=\'none\'">' : '') +
                '</div>' +
                '<div class="card-footer bg-white border-0">' +
                '<button class="btn btn-sm btn-primary w-100" onclick="showCompoundDetail(' + c.cid + ', \'' + escHtml(c.name) + '\')"><i class="bi bi-eye me-1"></i>View Full Details</button>' +
                '</div></div></div>';
        });
        pubchemHtml += '</div></div>';
    }
    document.getElementById('pubchemResults').innerHTML = pubchemHtml;

    // ── Step 3 Results: NCBI Taxonomy ─────────────────────────────────────────
    var ncbiHtml = '';
    if (data.ncbi && data.ncbi.length > 0) {
        ncbiHtml = '<div class="card border-0 shadow-sm mb-3">' +
            '<div class="card-header bg-warning text-dark fw-semibold">' +
            '<i class="bi bi-tree me-2"></i>NCBI Taxonomy Results (' + data.ncbi.length + ')' +
            '<span class="badge bg-dark text-white ms-2 small">Step 3 — NCBI</span>' +
            '</div><div class="row g-3 p-3">';
        data.ncbi.forEach(function(o) {
            ncbiHtml += '<div class="col-md-4"><div class="card border-warning h-100"><div class="card-body">' +
                '<h6 class="fw-bold fst-italic">' + escHtml(o.name) + '</h6>' +
                (o.kingdom ? '<div class="small text-muted"><strong>Kingdom:</strong> ' + escHtml(o.kingdom) + '</div>' : '') +
                (o.phylum  ? '<div class="small text-muted"><strong>Phylum:</strong> '  + escHtml(o.phylum)  + '</div>' : '') +
                (o.family  ? '<div class="small text-muted"><strong>Family:</strong> '  + escHtml(o.family)  + '</div>' : '') +
                (o.genus   ? '<div class="small text-muted"><strong>Genus:</strong> '   + escHtml(o.genus)   + '</div>' : '') +
                '</div>' +
                '<div class="card-footer bg-white border-0">' +
                '<a href="' + escHtml(o.ncbi_url) + '" target="_blank" class="btn btn-sm btn-warning w-100 text-dark"><i class="bi bi-box-arrow-up-right me-1"></i>View on NCBI</a>' +
                '</div></div></div>';
        });
        ncbiHtml += '</div></div>';
    }
    document.getElementById('ncbiResults').innerHTML = ncbiHtml;

    // Clear ChEBI section (not part of sequential workflow)
    document.getElementById('chebiResults').innerHTML = '';
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showCompoundDetail(cid, name) {
    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'compoundDetailModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="bi bi-capsule me-2"></i>${escHtml(name)}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="compoundDetailBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success mb-2"></div>
                        <p class="text-muted">Loading full compound profile...</p>
                    </div>
                </div>
            </div>
        </div>`;
    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', function() { modal.remove(); });

    var BASE = document.querySelector('meta[name="base-url"]').content;

    // Fetch PubChem detail + NCBI taxonomy in parallel
    var formData1 = new FormData();
    formData1.append('action', 'pubchem_detail');
    formData1.append('cid', cid);
    formData1.append('query', name);

    Promise.all([
        fetch(BASE + 'controllers/api_hybrid_search.php', { method: 'POST', body: formData1 }).then(r => r.json()),
        fetch('https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/' + cid + '/property/IUPACName,MolecularFormula,MolecularWeight,CanonicalSMILES,InChIKey,XLogP,HBondDonorCount,HBondAcceptorCount,RotatableBondCount,ExactMass,TPSA/JSON').then(r => r.json()).catch(() => null)
    ])
    .then(function(results) {
        var data = results[0];
        var propData = results[1];

        var c = data.data;
        if (!c) {
            document.getElementById('compoundDetailBody').innerHTML =
                '<p class="text-danger text-center py-3">Could not load details.</p>';
            return;
        }

        // Merge extra properties
        var props = (propData && propData.PropertyTable && propData.PropertyTable.Properties)
            ? propData.PropertyTable.Properties[0] : {};

        document.getElementById('compoundDetailBody').innerHTML = `
        <div class="row g-4">

            <!-- LEFT: Structure + Basic ID -->
            <div class="col-lg-4">
                <!-- 2D Structure -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-semibold small">
                        <i class="bi bi-bezier2 me-2"></i>Chemical Structure
                    </div>
                    <div class="card-body text-center p-3">
                        <img src="${escHtml(c.image_url)}"
                             class="img-fluid rounded border mb-2"
                             style="max-height:200px"
                             alt="2D Structure"
                             onerror="this.style.display='none';document.getElementById('noStructure${cid}').style.display='block'">
                        <div id="noStructure${cid}" style="display:none">
                            <code class="fs-5">${escHtml(c.formula||'')}</code>
                            <p class="text-muted small mt-1">Structure image not available</p>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-light text-dark border">2D Structure</span>
                        </div>
                    </div>
                </div>

                <!-- Source Info -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-semibold small">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Source
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Database</span>
                            <span class="badge bg-primary">PubChem</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">CID</span>
                            <a href="https://pubchem.ncbi.nlm.nih.gov/compound/${cid}" target="_blank" class="small text-decoration-none">${cid} <i class="bi bi-box-arrow-up-right"></i></a>
                        </div>
                        ${c.inchikey ? `<div class="d-flex justify-content-between">
                            <span class="text-muted small">InChIKey</span>
                            <code style="font-size:.65rem">${escHtml(c.inchikey)}</code>
                        </div>` : ''}
                    </div>
                </div>
            </div>

            <!-- RIGHT: Full Details -->
            <div class="col-lg-8">

                <!-- Chemical Identity -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-success text-white fw-semibold small">
                        <i class="bi bi-capsule me-2"></i>Chemical Identity
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><th class="ps-3 text-muted" style="width:40%">Compound Name</th><td class="fw-semibold">${escHtml(c.name)}</td></tr>
                            ${c.iupac_name ? `<tr><th class="ps-3 text-muted">IUPAC Name</th><td style="font-size:.8rem">${escHtml(c.iupac_name)}</td></tr>` : ''}
                            ${c.formula ? `<tr><th class="ps-3 text-muted">Molecular Formula</th><td><code class="text-success fs-6">${escHtml(c.formula)}</code></td></tr>` : ''}
                            ${c.molecular_weight ? `<tr><th class="ps-3 text-muted">Molecular Weight</th><td><strong>${c.molecular_weight}</strong> g/mol</td></tr>` : ''}
                            ${props.XLogP ? `<tr><th class="ps-3 text-muted">XLogP</th><td>${props.XLogP}</td></tr>` : ''}
                            ${props.TPSA ? `<tr><th class="ps-3 text-muted">TPSA</th><td>${props.TPSA} Å²</td></tr>` : ''}
                            ${props.HBondDonorCount ? `<tr><th class="ps-3 text-muted">H-Bond Donors</th><td>${props.HBondDonorCount}</td></tr>` : ''}
                            ${props.HBondAcceptorCount ? `<tr><th class="ps-3 text-muted">H-Bond Acceptors</th><td>${props.HBondAcceptorCount}</td></tr>` : ''}
                        </table>
                    </div>
                </div>

                <!-- Synonyms -->
                ${c.synonyms && c.synonyms.length > 0 ? `
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-semibold small">
                        <i class="bi bi-tags me-2 text-secondary"></i>Also Known As
                    </div>
                    <div class="card-body p-3">
                        ${c.synonyms.map(s => `<span class="badge bg-light text-dark border me-1 mb-1">${escHtml(s)}</span>`).join('')}
                    </div>
                </div>` : ''}

                <!-- SMILES -->
                ${c.smiles ? `
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-semibold small">
                        <i class="bi bi-bezier me-2 text-info"></i>SMILES Notation
                    </div>
                    <div class="card-body p-3">
                        <code class="font-monospace p-2 d-block bg-light rounded" style="font-size:.75rem;word-break:break-all">${escHtml(c.smiles)}</code>
                    </div>
                </div>` : ''}

                <!-- Research Papers -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light fw-semibold small">
                        <i class="bi bi-journal-text me-2 text-primary"></i>Scientific References
                    </div>
                    <div class="card-body p-3" id="pubmedSection${cid}">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        <span class="text-muted small">Loading PubMed data...</span>
                    </div>
                </div>

            </div>
        </div>`;

        // Async: Fetch PubMed count
        fetch('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term=' + encodeURIComponent(name) + '&retmax=0&retmode=json')
        .then(r => r.json())
        .then(function(pm) {
            var count = pm.esearchresult ? parseInt(pm.esearchresult.count) : 0;
            var el = document.getElementById('pubmedSection' + cid);
            if (el) {
                el.innerHTML = count > 0
                    ? `<div class="d-flex align-items-center gap-3">
                        <div>
                            <div class="fw-bold fs-5 text-primary">${count.toLocaleString()}</div>
                            <div class="text-muted small">Research publications found</div>
                        </div>
                        <a href="https://pubmed.ncbi.nlm.nih.gov/?term=${encodeURIComponent(name)}" target="_blank" class="btn btn-sm btn-outline-primary ms-auto">
                            <i class="bi bi-box-arrow-up-right me-1"></i>View on PubMed
                        </a>
                       </div>`
                    : '<p class="text-muted small mb-0">No PubMed publications found for this compound.</p>';
            }
        }).catch(function() {
            var el = document.getElementById('pubmedSection' + cid);
            if (el) el.innerHTML = '<p class="text-muted small mb-0">Could not load PubMed data.</p>';
        });
    })
    .catch(function() {
        document.getElementById('compoundDetailBody').innerHTML =
            '<p class="text-danger text-center py-3">Network error loading details.</p>';
    });
}
</script>
