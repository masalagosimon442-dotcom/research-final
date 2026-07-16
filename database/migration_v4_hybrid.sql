-- ============================================================
-- HAZINA ASILI v4.0 — Hybrid Search & Visitor Tracking
-- Run this migration to add new features
-- ============================================================

-- 1. Enrich compounds table with external data columns
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

-- 2. Site visits tracking table
CREATE TABLE IF NOT EXISTS site_visits (
    id INTEGER NOT NULL AUTO_INCREMENT,
    user_id INTEGER DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    page_url VARCHAR(500) DEFAULT NULL,
    user_agent VARCHAR(300) DEFAULT NULL,
    session_id VARCHAR(64) DEFAULT NULL,
    visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_visits_date (visited_at),
    KEY idx_visits_user (user_id),
    KEY idx_visits_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. External search log
CREATE TABLE IF NOT EXISTS external_searches (
    id INTEGER NOT NULL AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    query VARCHAR(500) NOT NULL,
    search_type VARCHAR(50) NOT NULL DEFAULT 'name',
    sources_queried TEXT DEFAULT NULL,
    results_count INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ext_search_user (user_id),
    KEY idx_ext_search_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. External data cache
CREATE TABLE IF NOT EXISTS compound_cache (
    id INTEGER NOT NULL AUTO_INCREMENT,
    query_key VARCHAR(200) NOT NULL,
    source VARCHAR(50) NOT NULL,
    raw_data LONGTEXT DEFAULT NULL,
    cached_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cache_key (query_key, source),
    KEY idx_cache_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
