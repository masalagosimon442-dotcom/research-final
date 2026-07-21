<?php
/**
 * HAZINA ASILI — PostgreSQL Database Setup
 * Visit this URL once after deployment to create all tables.
 * DELETE this file immediately after running!
 */

$host = getenv('DB_HOST') ?: 'dpg-d9fglb7avr4c73c720mg-a.frankfurt-postgres.render.com';
$port = getenv('DB_PORT') ?: '5432';
$user = getenv('DB_USER') ?: 'hazina_asil_db_user';
$pass = getenv('DB_PASS') ?: 'aABbVTFhohrVuYutkyMIkGiuLXfumWsS';
$db   = getenv('DB_NAME') ?: 'hazina_asil_db';

try {
    $dsn  = "pgsql:host={$host};port={$port};dbname={$db};sslmode=require";
    $conn = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die('<h2 style="color:red">Connection failed: ' . $e->getMessage() . '</h2>');
}

$statements = [];

$statements[] = "CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'researcher',
    bio TEXT DEFAULT NULL,
    institution VARCHAR(200) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    passport_document VARCHAR(255) DEFAULT NULL,
    api_key VARCHAR(64) DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_expires TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS organisms (
    id SERIAL PRIMARY KEY,
    kingdom VARCHAR(100) NOT NULL,
    phylum VARCHAR(100) NOT NULL,
    class VARCHAR(100) NOT NULL,
    order_name VARCHAR(100) DEFAULT NULL,
    family VARCHAR(100) DEFAULT NULL,
    genus VARCHAR(100) DEFAULT NULL,
    species VARCHAR(100) DEFAULT NULL,
    cell_type VARCHAR(20) DEFAULT NULL,
    habitat VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    structure_image VARCHAR(255) DEFAULT NULL,
    scientific_name VARCHAR(200) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = 'CREATE TABLE IF NOT EXISTS "references" (
    id SERIAL PRIMARY KEY,
    title VARCHAR(400) NOT NULL,
    author VARCHAR(300) NOT NULL,
    year INTEGER NOT NULL,
    citation TEXT NOT NULL
)';

$statements[] = "CREATE TABLE IF NOT EXISTS compounds (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    formula VARCHAR(100) NOT NULL,
    molecular_weight DECIMAL(10,4) NOT NULL,
    description TEXT DEFAULT NULL,
    structure_image VARCHAR(255) DEFAULT NULL,
    smiles VARCHAR(1000) DEFAULT NULL,
    inchikey VARCHAR(27) DEFAULT NULL,
    iupac_name VARCHAR(500) DEFAULT NULL,
    pubchem_cid INTEGER DEFAULT NULL,
    biological_activities TEXT DEFAULT NULL,
    synonyms TEXT DEFAULT NULL,
    organism_id INTEGER DEFAULT NULL,
    created_by INTEGER DEFAULT NULL,
    version INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS compound_versions (
    id SERIAL PRIMARY KEY,
    compound_id INTEGER NOT NULL,
    version INTEGER NOT NULL,
    name VARCHAR(200) NOT NULL,
    formula VARCHAR(100) NOT NULL,
    molecular_weight DECIMAL(10,4) NOT NULL,
    description TEXT DEFAULT NULL,
    organism_id INTEGER DEFAULT NULL,
    changed_by INTEGER DEFAULT NULL,
    change_summary VARCHAR(500) DEFAULT NULL,
    old_values JSONB DEFAULT NULL,
    new_values JSONB DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(compound_id, version)
)";

$statements[] = "CREATE TABLE IF NOT EXISTS compound_reference (
    compound_id INTEGER NOT NULL,
    reference_id INTEGER NOT NULL,
    PRIMARY KEY (compound_id, reference_id)
)";

$statements[] = "CREATE TABLE IF NOT EXISTS researcher_insights (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    compound_id INTEGER NOT NULL,
    insight_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_comment TEXT DEFAULT NULL,
    reviewed_by INTEGER DEFAULT NULL,
    reviewed_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS researcher_recommendations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    compound_id INTEGER NOT NULL,
    field_to_change VARCHAR(50) NOT NULL,
    suggested_value TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_comment TEXT DEFAULT NULL,
    reviewed_by INTEGER DEFAULT NULL,
    reviewed_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS approval_comments (
    id SERIAL PRIMARY KEY,
    entity_type VARCHAR(20) NOT NULL,
    entity_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    comment TEXT NOT NULL,
    action VARCHAR(20) NOT NULL DEFAULT 'comment',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS activity_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INTEGER DEFAULT NULL,
    old_values JSONB DEFAULT NULL,
    new_values JSONB DEFAULT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(300) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(400) DEFAULT NULL,
    is_read SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS error_log (
    id SERIAL PRIMARY KEY,
    level VARCHAR(20) NOT NULL DEFAULT 'notice',
    message TEXT NOT NULL,
    file VARCHAR(400) DEFAULT NULL,
    line INTEGER DEFAULT NULL,
    trace TEXT DEFAULT NULL,
    user_id INTEGER DEFAULT NULL,
    url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS login_attempts (
    id SERIAL PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS site_visits (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    page_url VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(300) DEFAULT NULL,
    session_id VARCHAR(64) DEFAULT NULL,
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS external_searches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    query VARCHAR(500) NOT NULL,
    search_type VARCHAR(50) NOT NULL DEFAULT 'name',
    sources_queried TEXT DEFAULT NULL,
    results_count INTEGER DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";

$statements[] = "CREATE TABLE IF NOT EXISTS compound_cache (
    id SERIAL PRIMARY KEY,
    query_key VARCHAR(200) NOT NULL,
    source VARCHAR(50) NOT NULL,
    raw_data TEXT DEFAULT NULL,
    cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT NULL,
    UNIQUE(query_key, source)
)";

// ── SEED DATA ──────────────────────────────────────────────────
$statements[] = "INSERT INTO users (name,email,password,role,institution,created_at)
VALUES
('System Admin','admin@hazina-asili.com','\$2y\$12\$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','admin','Hazina Asili Research Institute',NOW()),
('Dr. Jane Smith','researcher@hazina-asili.com','\$2y\$12\$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','researcher','University of Nairobi',NOW()),
('Prof. Ahmed Hassan','ahmed.hassan@hazina-asili.com','\$2y\$12\$LN1Rh.LjDDT9TuO5RhGwOeDQhMqx3bRwFMGFhJXXMfYs3MnCKMQWi','researcher','Kenyatta University',NOW())
ON CONFLICT (email) DO NOTHING";

$statements[] = "INSERT INTO organisms (kingdom,phylum,class,order_name,family,genus,species,scientific_name,cell_type,habitat,description)
VALUES
('Plantae','Tracheophyta','Magnoliopsida','Ericales','Theaceae','Camellia','sinensis','Camellia sinensis','eukaryotic','Tropical Asia','The tea plant. Source of EGCG and quercetin.'),
('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Curcuma','longa','Curcuma longa','eukaryotic','Tropical South Asia','Turmeric. Primary source of curcumin.'),
('Plantae','Tracheophyta','Magnoliopsida','Asparagales','Amaryllidaceae','Allium','sativum','Allium sativum','eukaryotic','Central Asia','Garlic. Source of allicin.'),
('Plantae','Tracheophyta','Magnoliopsida','Zingiberales','Zingiberaceae','Zingiber','officinale','Zingiber officinale','eukaryotic','Southeast Asia','Ginger. Contains gingerols.'),
('Fungi','Ascomycota','Eurotiomycetes','Eurotiales','Aspergillaceae','Penicillium','chrysogenum','Penicillium chrysogenum','eukaryotic','Soil environments','Source of Penicillin G.'),
('Bacteria','Actinobacteria','Actinomycetia','Streptomycetales','Streptomycetaceae','Streptomyces','griseus','Streptomyces griseus','prokaryotic','Soil worldwide','Source of streptomycin.'),
('Plantae','Tracheophyta','Magnoliopsida','Vitales','Vitaceae','Vitis','vinifera','Vitis vinifera','eukaryotic','Mediterranean','Grape vine. Source of resveratrol.'),
('Plantae','Tracheophyta','Magnoliopsida','Gentianales','Rubiaceae','Coffea','arabica','Coffea arabica','eukaryotic','Ethiopia','Coffee plant. Source of caffeine.')
ON CONFLICT (scientific_name) DO NOTHING";

$statements[] = 'INSERT INTO "references" (title,author,year,citation) VALUES
(\'Quercetin: A Versatile Flavonoid\',\'Boots A.W., Haenen G.R., Bast A.\',2008,\'Boots AW et al. Eur J Pharmacol. 2008.\'),
(\'Curcumin: Biological and Medicinal Properties\',\'Aggarwal B.B., Harikumar K.B.\',2009,\'Aggarwal BB et al. Int J Biochem. 2009.\'),
(\'Allicin: Chemistry and Biological Properties\',\'Borlinghaus J. et al.\',2014,\'Borlinghaus J et al. Molecules. 2014.\'),
(\'Penicillin: Discovery and Development\',\'Fleming A.\',1929,\'Fleming A. Br J Exp Pathol. 1929.\')
ON CONFLICT DO NOTHING';

$statements[] = "INSERT INTO compounds (name,formula,molecular_weight,description,organism_id,smiles,inchikey,pubchem_cid,biological_activities,version,created_by) VALUES
('Quercetin','C15H10O7',302.2357,'A plant flavonoid with antioxidant and anti-inflammatory properties.',1,'O=c1c(O)c(-c2ccc(O)c(O)c2)oc2cc(O)cc(O)c12','REOJLIXKJNPJEP-UHFFFAOYSA-N',5280343,'Antioxidant, anti-inflammatory, anticancer',1,1),
('Curcumin','C21H20O6',368.3799,'The principal curcuminoid of turmeric.',2,NULL,NULL,969516,'Anti-inflammatory, antioxidant, anticancer',1,1),
('Allicin','C6H10OS2',162.2700,'Organosulfur compound from garlic.',3,NULL,NULL,65036,'Antimicrobial, antifungal, antiviral',1,1),
('Gingerol','C17H26O4',294.3800,'Primary bioactive in fresh ginger.',4,NULL,NULL,442495,'Anti-nausea, anti-inflammatory',1,1),
('Penicillin G','C16H18N2O4S',334.3900,'First beta-lactam antibiotic.',5,NULL,NULL,5904,'Antibacterial',1,1),
('Streptomycin','C21H39N7O12',581.5700,'Aminoglycoside antibiotic for tuberculosis.',6,NULL,NULL,19649,'Antibacterial, antitubercular',1,1),
('Resveratrol','C14H12O3',228.2440,'Stilbenoid from red grapes.',7,NULL,NULL,445154,'Antioxidant, cardioprotective',1,1),
('Caffeine','C8H10N4O2',194.1900,'Purine alkaloid CNS stimulant.',8,NULL,NULL,2519,'CNS stimulant',1,1)
ON CONFLICT DO NOTHING";

// ── RUN ────────────────────────────────────────────────────────
$success = 0; $errors = [];
foreach ($statements as $sql) {
    try {
        $conn->exec($sql);
        $success++;
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Hazina Asili Setup</title>
<style>body{font-family:sans-serif;max-width:650px;margin:3rem auto;padding:1rem;background:#f8f9fa}
.card{background:#fff;padding:2rem;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
h2{color:#198754}.ok{color:#198754}.err{color:#dc3545}
.creds{background:#e9f7ef;padding:1rem;border-radius:8px;margin:1rem 0}
.warn{background:#fff3cd;padding:1rem;border-radius:8px;color:#856404}</style>
</head>
<body>
<div class="card">
<h2>🌿 HAZINA ASILI — Database Setup</h2>
<?php if (count($errors) === 0): ?>
<p class="ok">✅ <strong>All <?= $success ?> statements executed successfully!</strong></p>
<div class="creds">
<h4>Login Credentials:</h4>
<p><strong>Admin:</strong> admin@hazina-asili.com / <code>Admin@1234</code></p>
<p><strong>Researcher:</strong> researcher@hazina-asili.com / <code>Admin@1234</code></p>
</div>
<?php else: ?>
<p class="ok">✅ <?= $success ?> succeeded.</p>
<p class="err">⚠️ <?= count($errors) ?> errors (may be harmless duplicates):</p>
<ul><?php foreach($errors as $e): ?><li class="err" style="font-size:.85rem"><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
<?php endif; ?>
<div class="warn">⚠️ <strong>Security: Delete this file immediately!</strong><br>
Go to Render dashboard → your service → Shell → run: <code>rm /var/www/html/setup.php</code></div>
</div>
</body></html>
