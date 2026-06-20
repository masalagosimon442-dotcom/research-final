-- ============================================================
-- HAZINA ASILI v4.0 — Hybrid Search & Visitor Tracking (PostgreSQL)
-- ============================================================

-- 1. Enrich compounds table
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS smiles VARCHAR(1000) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS inchikey VARCHAR(27) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS iupac_name VARCHAR(500) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS cas_number VARCHAR(20) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS pubchem_cid INTEGER DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS chebi_id VARCHAR(20) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS biological_activities TEXT DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS pubmed_count INTEGER DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS external_source VARCHAR(50) DEFAULT NULL;
ALTER TABLE compounds ADD COLUMN IF NOT EXISTS synonyms TEXT DEFAULT NULL;

-- 2. Site visits tracking
CREATE TABLE IF NOT EXISTS site_visits (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    page_url VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(300) DEFAULT NULL,
    session_id VARCHAR(64) DEFAULT NULL,
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_visits_date ON site_visits(visited_at);
CREATE INDEX IF NOT EXISTS idx_visits_user ON site_visits(user_id);

-- 3. External search log
CREATE TABLE IF NOT EXISTS external_searches (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    query VARCHAR(500) NOT NULL,
    search_type VARCHAR(50) NOT NULL DEFAULT 'name',
    sources_queried TEXT DEFAULT NULL,
    results_count INTEGER DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_ext_search_user ON external_searches(user_id);
CREATE INDEX IF NOT EXISTS idx_ext_search_date ON external_searches(created_at);

-- 4. External data cache
CREATE TABLE IF NOT EXISTS compound_cache (
    id SERIAL PRIMARY KEY,
    query_key VARCHAR(200) NOT NULL,
    source VARCHAR(50) NOT NULL,
    raw_data TEXT DEFAULT NULL,
    cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP DEFAULT NULL,
    UNIQUE(query_key, source)
);
