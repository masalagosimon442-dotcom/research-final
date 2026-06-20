<?php
/**
 * Site Visit Model — tracks visitor counts per day/week/month/year
 */
class SiteVisit {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        try {
            if (DB_DRIVER === 'pgsql') {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS site_visits (
                        id SERIAL PRIMARY KEY,
                        user_id INTEGER DEFAULT NULL,
                        ip_address VARCHAR(45) NOT NULL,
                        page_url VARCHAR(500) DEFAULT NULL,
                        user_agent VARCHAR(300) DEFAULT NULL,
                        session_id VARCHAR(64) DEFAULT NULL,
                        visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    )
                ");
            } else {
                $this->db->exec("
                    CREATE TABLE IF NOT EXISTS site_visits (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        user_id INT DEFAULT NULL,
                        ip_address VARCHAR(45) NOT NULL,
                        page_url VARCHAR(500) DEFAULT NULL,
                        user_agent VARCHAR(300) DEFAULT NULL,
                        session_id VARCHAR(64) DEFAULT NULL,
                        visited_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_visits_date (visited_at),
                        INDEX idx_visits_user (user_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
        } catch (Exception $e) {
            // Table may already exist
        }
    }

    public function record(): void {
        try {
            $userId    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $ip        = $this->getClientIp();
            $pageUrl   = mb_substr($_SERVER['REQUEST_URI'] ?? '', 0, 500);
            $ua        = mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 300);
            $sessionId = mb_substr(session_id() ?? '', 0, 64);

            // Deduplicate: don't count same session+page within 30 minutes
            if (DB_DRIVER === 'pgsql') {
                $check = $this->db->prepare(
                    "SELECT COUNT(*) FROM site_visits 
                     WHERE session_id = ? AND page_url = ? 
                     AND visited_at > NOW() - INTERVAL '30 minutes'"
                );
            } else {
                $check = $this->db->prepare(
                    "SELECT COUNT(*) FROM site_visits 
                     WHERE session_id = ? AND page_url = ? 
                     AND visited_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)"
                );
            }
            $check->execute([$sessionId, $pageUrl]);
            if ((int)$check->fetchColumn() > 0) return;

            $stmt = $this->db->prepare(
                "INSERT INTO site_visits (user_id, ip_address, page_url, user_agent, session_id, visited_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([$userId, $ip, $pageUrl, $ua, $sessionId]);
        } catch (Exception $e) {
            // Silently fail — don't break the app
        }
    }

    public function getStats(): array {
        try {
            if (DB_DRIVER === 'pgsql') {
                $stmt = $this->db->query(
                    "SELECT 
                        COUNT(*) AS total_all_time,
                        SUM(CASE WHEN visited_at::date = CURRENT_DATE THEN 1 ELSE 0 END) AS today,
                        SUM(CASE WHEN visited_at >= NOW() - INTERVAL '7 days' THEN 1 ELSE 0 END) AS this_week,
                        SUM(CASE WHEN visited_at >= DATE_TRUNC('month', NOW()) THEN 1 ELSE 0 END) AS this_month,
                        SUM(CASE WHEN visited_at >= DATE_TRUNC('year', NOW()) THEN 1 ELSE 0 END) AS this_year,
                        COUNT(DISTINCT ip_address) AS unique_visitors,
                        COUNT(DISTINCT session_id) AS unique_sessions
                     FROM site_visits"
                );
            } else {
                $stmt = $this->db->query(
                    "SELECT 
                        COUNT(*) AS total_all_time,
                        SUM(DATE(visited_at) = CURDATE()) AS today,
                        SUM(visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS this_week,
                        SUM(visited_at >= DATE_FORMAT(NOW(),'%Y-%m-01')) AS this_month,
                        SUM(YEAR(visited_at) = YEAR(NOW())) AS this_year,
                        COUNT(DISTINCT ip_address) AS unique_visitors,
                        COUNT(DISTINCT session_id) AS unique_sessions
                     FROM site_visits"
                );
            }
            return $stmt->fetch() ?: [];
        } catch (Exception $e) {
            return ['today' => 0, 'this_week' => 0, 'this_month' => 0, 'this_year' => 0, 'total_all_time' => 0, 'unique_visitors' => 0, 'unique_sessions' => 0];
        }
    }

    public function getDailyVisits(int $days = 30): array {
        try {
            if (DB_DRIVER === 'pgsql') {
                $stmt = $this->db->prepare(
                    "SELECT visited_at::date AS day, COUNT(*) AS visits, COUNT(DISTINCT ip_address) AS unique_visitors
                     FROM site_visits WHERE visited_at >= NOW() - INTERVAL '1 day' * ?
                     GROUP BY visited_at::date ORDER BY day ASC"
                );
            } else {
                $stmt = $this->db->prepare(
                    "SELECT DATE(visited_at) AS day, COUNT(*) AS visits, COUNT(DISTINCT ip_address) AS unique_visitors
                     FROM site_visits WHERE visited_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                     GROUP BY DATE(visited_at) ORDER BY day ASC"
                );
            }
            $stmt->execute([$days]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTopPages(int $limit = 10): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT page_url, COUNT(*) AS visits FROM site_visits
                 GROUP BY page_url ORDER BY visits DESC LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    private function getClientIp(): string {
        foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = explode(',', $_SERVER[$h])[0];
                if (filter_var(trim($ip), FILTER_VALIDATE_IP)) return trim($ip);
            }
        }
        return '0.0.0.0';
    }
}
