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
            <p class="text-muted mb-0 small">Search compounds & organisms from local database and external scientific repositories</p>
        </div>
        <div class="ms-auto d-flex gap-2">
            <span class="badge bg-success px-3 py-2">Local DB</span>
            <span class="badge bg-primary px-3 py-2">PubChem</span>
            <span class="badge bg-info px-3 py-2">ChEBI</span>
            <span class="badge bg-warning text-dark px-3 py-2">NCBI</span>
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

    <!-- Loading indicator -->
    <div id="searchLoading" class="text-center py-5 d-none">
        <div class="spinner-border text-success mb-3" role="status"></div>
        <p class="text-muted">Searching local database, PubChem, ChEBI, and NCBI simultaneously...</p>
        <div class="progress mt-3" style="max-width:400px;margin:0 auto">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:100%"></div>
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

    // Show loading
    document.getElementById('searchLoading').classList.remove('d-none');
    document.getElementById('searchResults').classList.add('d-none');
    document.getElementById('noResults').classList.add('d-none');

    var BASE = document.querySelector('meta[name="base-url"]').content;
    var formData = new FormData();
    formData.append('action', 'search_all');
    formData.append('query', query);
    formData.append('type', type);

    fetch(BASE + 'controllers/api_hybrid_search.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(function(data) {
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

        renderResults(data, query);
    })
    .catch(function(err) {
        document.getElementById('searchLoading').classList.add('d-none');
        alert('Network error. Please try again.');
    });
}

function renderResults(data, query) {
    var hasResults = data.total_results > 0;

    if (!hasResults) {
        document.getElementById('noResults').classList.remove('d-none');
        return;
    }

    document.getElementById('searchResults').classList.remove('d-none');

    // Summary
    document.getElementById('resultsSummary').textContent =
        data.total_results + ' results found for "' + query + '"';

    // Badges
    var badges = '';
    if (data.local_compounds.length > 0) badges += '<span class="badge bg-success">Local: ' + data.local_compounds.length + '</span>';
    if (data.pubchem.length > 0) badges += '<span class="badge bg-primary">PubChem: ' + data.pubchem.length + '</span>';
    if (data.chebi.length > 0) badges += '<span class="badge bg-info">ChEBI: ' + data.chebi.length + '</span>';
    if (data.ncbi.length > 0) badges += '<span class="badge bg-warning text-dark">NCBI: ' + data.ncbi.length + '</span>';
    document.getElementById('resultsBadges').innerHTML = badges;

    var BASE = document.querySelector('meta[name="base-url"]').content;

    // Local Compounds
    var localHtml = '';
    if (data.local_compounds.length > 0) {
        localHtml = '<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-success text-white fw-semibold"><i class="bi bi-database me-2"></i>Local Database (' + data.local_compounds.length + ' compounds)</div><div class="row g-3 p-3">';
        data.local_compounds.forEach(function(c) {
            localHtml += '<div class="col-md-6"><div class="card border-success h-100">' +
                '<div class="card-body">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                '<h6 class="fw-bold mb-0">' + escHtml(c.name) + '</h6>' +
                '<span class="badge bg-success">Local</span></div>' +
                '<code class="text-success small">' + escHtml(c.formula) + '</code>' +
                '<div class="text-muted small mt-1"><i class="bi bi-speedometer2 me-1"></i>' + parseFloat(c.molecular_weight).toFixed(2) + ' g/mol</div>' +
                (c.organism_name ? '<div class="text-muted small"><i class="bi bi-tree me-1"></i>' + escHtml(c.organism_name) + '</div>' : '') +
                (c.description ? '<p class="small text-muted mt-2 mb-0">' + escHtml(c.description.substring(0, 100)) + '...</p>' : '') +
                '</div>' +
                '<div class="card-footer bg-white border-0">' +
                '<a href="' + BASE + 'views/admin/compounds/view.php?id=' + c.id + '" class="btn btn-sm btn-success w-100"><i class="bi bi-eye me-1"></i>View Full Details</a>' +
                '</div></div></div>';
        });
        localHtml += '</div></div>';
    }
    document.getElementById('localResults').innerHTML = localHtml;

    // Local Organisms
    if (data.local_organisms && data.local_organisms.length > 0) {
        var orgHtml = '<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-warning text-dark fw-semibold"><i class="bi bi-tree me-2"></i>Local Organisms (' + data.local_organisms.length + ')</div><div class="row g-3 p-3">';
        data.local_organisms.forEach(function(o) {
            orgHtml += '<div class="col-md-6"><div class="card border-warning h-100"><div class="card-body">' +
                '<h6 class="fw-bold fst-italic">' + escHtml(o.scientific_name) + '</h6>' +
                '<div class="small text-muted"><span class="badge bg-warning text-dark me-1">' + escHtml(o.kingdom) + '</span>' +
                escHtml(o.phylum || '') + ' › ' + escHtml(o.class || '') + '</div>' +
                '</div><div class="card-footer bg-white border-0">' +
                '<a href="' + BASE + 'views/researcher/organisms/view.php?id=' + o.id + '" class="btn btn-sm btn-warning w-100 text-dark"><i class="bi bi-eye me-1"></i>View Details</a>' +
                '</div></div></div>';
        });
        orgHtml += '</div></div>';
        document.getElementById('localResults').innerHTML += orgHtml;
    }

    // PubChem Results
    var pubchemHtml = '';
    if (data.pubchem.length > 0) {
        pubchemHtml = '<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-primary text-white fw-semibold"><i class="bi bi-globe me-2"></i>PubChem (' + data.pubchem.length + ' compounds)' +
            (data.pubmed_count > 0 ? '<span class="badge bg-light text-primary ms-2">PubMed: ' + data.pubmed_count.toLocaleString() + ' papers</span>' : '') +
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
                '<div class="text-muted small"><strong>CID:</strong> ' + c.cid + '</div>' +
                (c.inchikey ? '<div class="text-muted" style="font-size:.7rem"><strong>InChIKey:</strong> ' + escHtml(c.inchikey) + '</div>' : '') +
                (c.smiles ? '<div class="mt-2"><small class="text-muted">SMILES:</small><div class="font-monospace" style="font-size:.65rem;word-break:break-all;color:#0d6efd">' + escHtml(c.smiles.substring(0, 100)) + '</div></div>' : '') +
                (c.image_url ? '<img src="' + escHtml(c.image_url) + '" class="img-fluid mt-2 rounded" style="max-height:120px" alt="Structure" onerror="this.style.display=\'none\'">' : '') +
                '</div>' +
                '<div class="card-footer bg-white border-0 d-flex gap-1">' +
                '<a href="' + escHtml(c.pubchem_url) + '" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1"><i class="bi bi-box-arrow-up-right me-1"></i>PubChem</a>' +
                '<a href="https://pubmed.ncbi.nlm.nih.gov/?term=' + encodeURIComponent(c.name) + '" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-journal-text"></i></a>' +
                '</div></div></div>';
        });
        pubchemHtml += '</div></div>';
    }
    document.getElementById('pubchemResults').innerHTML = pubchemHtml;

    // ChEBI Results
    var chebiHtml = '';
    if (data.chebi.length > 0) {
        chebiHtml = '<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-info text-white fw-semibold"><i class="bi bi-diagram-3 me-2"></i>ChEBI - Chemical Entities (' + data.chebi.length + ')</div><div class="row g-3 p-3">';
        data.chebi.forEach(function(c) {
            chebiHtml += '<div class="col-md-4"><div class="card border-info h-100"><div class="card-body">' +
                '<h6 class="fw-bold">' + escHtml(c.name) + '</h6>' +
                '<div class="badge bg-info mb-2">' + escHtml(c.chebi_id) + '</div>' +
                '</div><div class="card-footer bg-white border-0">' +
                '<a href="' + escHtml(c.chebi_url) + '" target="_blank" class="btn btn-sm btn-info text-white w-100"><i class="bi bi-box-arrow-up-right me-1"></i>View on ChEBI</a>' +
                '</div></div></div>';
        });
        chebiHtml += '</div></div>';
    }
    document.getElementById('chebiResults').innerHTML = chebiHtml;

    // NCBI Taxonomy Results
    var ncbiHtml = '';
    if (data.ncbi.length > 0) {
        ncbiHtml = '<div class="card border-0 shadow-sm mb-3"><div class="card-header bg-warning text-dark fw-semibold"><i class="bi bi-tree me-2"></i>NCBI Taxonomy (' + data.ncbi.length + ' organisms)</div><div class="row g-3 p-3">';
        data.ncbi.forEach(function(o) {
            ncbiHtml += '<div class="col-md-4"><div class="card border-warning h-100"><div class="card-body">' +
                '<h6 class="fw-bold fst-italic">' + escHtml(o.name) + '</h6>' +
                '<div class="small text-muted"><strong>Tax ID:</strong> ' + escHtml(o.tax_id) + '</div>' +
                (o.kingdom ? '<div class="small text-muted"><strong>Kingdom:</strong> ' + escHtml(o.kingdom) + '</div>' : '') +
                (o.family ? '<div class="small text-muted"><strong>Family:</strong> ' + escHtml(o.family) + '</div>' : '') +
                (o.genus ? '<div class="small text-muted"><strong>Genus:</strong> ' + escHtml(o.genus) + '</div>' : '') +
                '</div><div class="card-footer bg-white border-0">' +
                '<a href="' + escHtml(o.ncbi_url) + '" target="_blank" class="btn btn-sm btn-warning text-dark w-100"><i class="bi bi-box-arrow-up-right me-1"></i>NCBI Taxonomy</a>' +
                '</div></div></div>';
        });
        ncbiHtml += '</div></div>';
    }
    document.getElementById('ncbiResults').innerHTML = ncbiHtml;
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
