<?php
/**
 * External Search Helper
 * Queries PubChem, ChEBI, and NCBI Taxonomy APIs
 * Free APIs — no API key required
 */
class ExternalSearch {

    private PDO $db;
    private int $cacheHours = 24; // Cache results for 24 hours

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureCacheTable();
    }

    private function ensureCacheTable(): void {
        try {
            if (DB_DRIVER === 'pgsql') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS compound_cache (
                        id SERIAL PRIMARY KEY,
                        query_key VARCHAR(200) NOT NULL,
                        source VARCHAR(50) NOT NULL,
                        raw_data TEXT DEFAULT NULL,
                        cached_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        expires_at TIMESTAMP DEFAULT NULL,
                        UNIQUE(query_key, source)
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS compound_cache (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        query_key VARCHAR(200) NOT NULL,
                        source VARCHAR(50) NOT NULL,
                        raw_data LONGTEXT DEFAULT NULL,
                        cached_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        expires_at DATETIME DEFAULT NULL,
                        UNIQUE KEY uq_cache (query_key, source)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
            
            // Ensure external_searches table exists
            if (DB_DRIVER === 'pgsql') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS external_searches (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER NOT NULL,
                        query VARCHAR(500) NOT NULL,
                        search_type VARCHAR(50) NOT NULL DEFAULT 'name',
                        sources_queried TEXT DEFAULT NULL,
                        results_count INTEGER DEFAULT 0,
                        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS external_searches (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT NOT NULL,
                        query VARCHAR(500) NOT NULL,
                        search_type VARCHAR(50) NOT NULL DEFAULT 'name',
                        sources_queried TEXT DEFAULT NULL,
                        results_count INTEGER DEFAULT 0,
                        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
        } catch (Exception $e) { /* table may exist */ }
    }

    /**
     * Main search — queries all sources and returns combined results
     */
    public function searchAll(string $query, string $type = 'name'): array {
        $results = [
            'pubchem' => [],
            'chebi'   => [],
            'ncbi'    => [],
            'query'   => $query,
            'type'    => $type,
        ];

        if (empty(trim($query))) return $results;

        if ($type === 'name' || $type === 'formula' || $type === 'smiles') {
            $results['pubchem'] = $this->searchPubChem($query, $type);
            $results['chebi']   = $this->searchChEBI($query);
        }

        if ($type === 'name' || $type === 'organism') {
            $results['ncbi'] = $this->searchNCBI($query);
        }

        return $results;
    }

    /**
     * Search PubChem by name, formula, or SMILES
     */
    public function searchPubChem(string $query, string $type = 'name'): array {
        $cacheKey = 'pubchem_' . $type . '_' . md5($query);
        $cached = $this->getCache($cacheKey, 'pubchem');
        if ($cached !== null) return $cached;

        try {
            $encodedQuery = urlencode($query);
            $inputType = $type === 'formula' ? 'formula' : ($type === 'smiles' ? 'smiles' : 'name');
            
            // Search for CIDs
            $url = "https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/{$inputType}/{$encodedQuery}/cids/JSON?MaxRecords=5";
            $response = $this->httpGet($url);
            
            if (!$response || !isset($response['IdentifierList']['CID'])) {
                return [];
            }

            $cids = array_slice($response['IdentifierList']['CID'], 0, 5);
            $results = [];

            foreach ($cids as $cid) {
                $detail = $this->getPubChemDetail($cid);
                if ($detail) $results[] = $detail;
            }

            $this->setCache($cacheKey, 'pubchem', $results);
            return $results;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get full compound detail from PubChem by CID
     */
    public function getPubChemDetail(int $cid): ?array {
        $cacheKey = 'pubchem_cid_' . $cid;
        $cached = $this->getCache($cacheKey, 'pubchem_detail');
        if ($cached !== null) return $cached;

        try {
            // Get properties
            $propUrl = "https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/property/IUPACName,MolecularFormula,MolecularWeight,CanonicalSMILES,InChIKey,XLogP,ExactMass/JSON";
            $propData = $this->httpGet($propUrl);

            if (!$propData || !isset($propData['PropertyTable']['Properties'][0])) {
                return null;
            }

            $props = $propData['PropertyTable']['Properties'][0];

            // Get synonyms (first 5)
            $synUrl = "https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/synonyms/JSON";
            $synData = $this->httpGet($synUrl);
            $synonyms = [];
            if ($synData && isset($synData['InformationList']['Information'][0]['Synonym'])) {
                $synonyms = array_slice($synData['InformationList']['Information'][0]['Synonym'], 0, 5);
            }

            // Structure image URL
            $imageUrl = "https://pubchem.ncbi.nlm.nih.gov/rest/pug/compound/cid/{$cid}/PNG";

            $result = [
                'source'          => 'PubChem',
                'cid'             => $cid,
                'name'            => $synonyms[0] ?? ('CID ' . $cid),
                'iupac_name'      => $props['IUPACName'] ?? null,
                'formula'         => $props['MolecularFormula'] ?? null,
                'molecular_weight'=> $props['MolecularWeight'] ?? null,
                'smiles'          => $props['CanonicalSMILES'] ?? null,
                'inchikey'        => $props['InChIKey'] ?? null,
                'xlogp'           => $props['XLogP'] ?? null,
                'exact_mass'      => $props['ExactMass'] ?? null,
                'synonyms'        => $synonyms,
                'image_url'       => $imageUrl,
                'pubchem_url'     => "https://pubchem.ncbi.nlm.nih.gov/compound/{$cid}",
            ];

            $this->setCache($cacheKey, 'pubchem_detail', $result);
            return $result;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Search ChEBI for chemical entities and biological roles
     */
    public function searchChEBI(string $query): array {
        $cacheKey = 'chebi_' . md5($query);
        $cached = $this->getCache($cacheKey, 'chebi');
        if ($cached !== null) return $cached;

        try {
            $encodedQuery = urlencode($query);
            $url = "https://www.ebi.ac.uk/chebi/searchFreeText?searchString={$encodedQuery}&maximumResults=3";
            
            // ChEBI REST API
            $url2 = "https://www.ebi.ac.uk/chebi/webservices/rest/search?search={$encodedQuery}&searchCategory=ALL_NAMES&maximumResults=3";
            
            $response = $this->httpGet($url2, true);
            
            if (!$response) return [];

            $results = [];
            // Parse XML response
            if (is_string($response)) {
                preg_match_all('/<chebiId>(CHEBI:\d+)<\/chebiId>/', $response, $ids);
                preg_match_all('/<chebiAsciiName>(.*?)<\/chebiAsciiName>/', $response, $names);
                
                for ($i = 0; $i < min(3, count($ids[1])); $i++) {
                    $results[] = [
                        'source'   => 'ChEBI',
                        'chebi_id' => $ids[1][$i] ?? '',
                        'name'     => $names[1][$i] ?? '',
                        'chebi_url'=> 'https://www.ebi.ac.uk/chebi/searchId.do?chebiId=' . ($ids[1][$i] ?? ''),
                    ];
                }
            }

            $this->setCache($cacheKey, 'chebi', $results);
            return $results;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search NCBI Taxonomy for organism details
     */
    public function searchNCBI(string $query): array {
        $cacheKey = 'ncbi_' . md5($query);
        $cached = $this->getCache($cacheKey, 'ncbi');
        if ($cached !== null) return $cached;

        try {
            $encodedQuery = urlencode($query);
            
            // Search for taxonomy IDs
            $searchUrl = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=taxonomy&term={$encodedQuery}&retmax=3&retmode=json";
            $searchData = $this->httpGet($searchUrl);

            if (!$searchData || empty($searchData['esearchresult']['idlist'])) {
                return [];
            }

            $taxIds = $searchData['esearchresult']['idlist'];
            $results = [];

            foreach (array_slice($taxIds, 0, 3) as $taxId) {
                $detail = $this->getNCBIDetail($taxId);
                if ($detail) $results[] = $detail;
            }

            $this->setCache($cacheKey, 'ncbi', $results);
            return $results;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get NCBI Taxonomy detail by TaxID
     */
    public function getNCBIDetail(string $taxId): ?array {
        try {
            $url = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&id={$taxId}&retmode=xml";
            $xml = $this->httpGet($url, true);

            if (!$xml || !is_string($xml)) return null;

            $result = [
                'source'      => 'NCBI Taxonomy',
                'tax_id'      => $taxId,
                'name'        => '',
                'kingdom'     => '',
                'phylum'      => '',
                'class'       => '',
                'order'       => '',
                'family'      => '',
                'genus'       => '',
                'species'     => '',
                'ncbi_url'    => "https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id={$taxId}",
            ];

            // Parse scientific name
            if (preg_match('/<ScientificName>(.*?)<\/ScientificName>/', $xml, $m)) {
                $result['name'] = $m[1];
            }

            // Parse lineage
            $ranks = ['kingdom' => 'kingdom', 'phylum' => 'phylum', 'class' => 'class', 
                     'order' => 'order', 'family' => 'family', 'genus' => 'genus', 'species' => 'species'];
            
            preg_match_all('/<Taxon>.*?<Rank>(.*?)<\/Rank>.*?<ScientificName>(.*?)<\/ScientificName>.*?<\/Taxon>/s', $xml, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $rank = strtolower($match[1]);
                if (isset($ranks[$rank])) {
                    $result[$rank] = $match[2];
                }
            }

            return $result;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get PubMed article count for a compound
     */
    public function getPubMedCount(string $query): int {
        try {
            $cacheKey = 'pubmed_count_' . md5($query);
            $cached = $this->getCache($cacheKey, 'pubmed');
            if ($cached !== null) return (int)($cached['count'] ?? 0);

            $encodedQuery = urlencode($query);
            $url = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term={$encodedQuery}&retmax=0&retmode=json";
            $data = $this->httpGet($url);

            $count = (int)($data['esearchresult']['count'] ?? 0);
            $this->setCache($cacheKey, 'pubmed', ['count' => $count]);
            return $count;

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Log external search to database
     */
    public function logSearch(int $userId, string $query, string $type, array $sources, int $resultsCount): void {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO external_searches (user_id, query, search_type, sources_queried, results_count, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$userId, $query, $type, implode(',', $sources), $resultsCount]);
        } catch (Exception $e) { /* silently fail */ }
    }

    /**
     * Get external search stats for admin
     */
    public function getSearchStats(): array {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM external_searches");
            $total = (int)$stmt->fetchColumn();

            $stmt2 = $this->db->prepare(
                "SELECT query, COUNT(*) AS cnt FROM external_searches GROUP BY query ORDER BY cnt DESC LIMIT 10"
            );
            $stmt2->execute();
            $topSearches = $stmt2->fetchAll();

            return ['total' => $total, 'top_searches' => $topSearches];
        } catch (Exception $e) {
            return ['total' => 0, 'top_searches' => []];
        }
    }

    // ── Cache helpers ──────────────────────────────────────────────────────────

    private function getCache(string $key, string $source): ?array {
        try {
            if (DB_DRIVER === 'pgsql') {
                $stmt = $this->db->prepare(
                    "SELECT raw_data FROM compound_cache WHERE query_key=? AND source=? AND (expires_at IS NULL OR expires_at > NOW())"
                );
            } else {
                $stmt = $this->db->prepare(
                    "SELECT raw_data FROM compound_cache WHERE query_key=? AND source=? AND (expires_at IS NULL OR expires_at > NOW())"
                );
            }
            $stmt->execute([$key, $source]);
            $row = $stmt->fetch();
            if ($row && $row['raw_data']) {
                return json_decode($row['raw_data'], true);
            }
        } catch (Exception $e) { /* ignore */ }
        return null;
    }

    private function setCache(string $key, string $source, array $data): void {
        try {
            $expires = date('Y-m-d H:i:s', strtotime("+{$this->cacheHours} hours"));
            if (DB_DRIVER === 'pgsql') {
                $this->db->prepare(
                    "INSERT INTO compound_cache (query_key, source, raw_data, cached_at, expires_at)
                     VALUES (?, ?, ?, NOW(), ?)
                     ON CONFLICT (query_key, source) DO UPDATE SET raw_data=EXCLUDED.raw_data, cached_at=NOW(), expires_at=EXCLUDED.expires_at"
                )->execute([$key, $source, json_encode($data), $expires]);
            } else {
                $this->db->prepare(
                    "INSERT INTO compound_cache (query_key, source, raw_data, cached_at, expires_at)
                     VALUES (?, ?, ?, NOW(), ?)
                     ON DUPLICATE KEY UPDATE raw_data=VALUES(raw_data), cached_at=NOW(), expires_at=VALUES(expires_at)"
                )->execute([$key, $source, json_encode($data), $expires]);
            }
        } catch (Exception $e) { /* ignore */ }
    }

    // ── HTTP helper ────────────────────────────────────────────────────────────

    private function httpGet(string $url, bool $raw = false): mixed {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT      => 'HazinaAsili/4.0 (Research Database; contact@hazina-asili.com)',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) return null;
        if ($raw) return $response;
        
        $decoded = json_decode($response, true);
        return $decoded ?: null;
    }
}
