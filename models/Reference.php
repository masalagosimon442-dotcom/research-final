<?php
/**
 * Reference Model
 */
class Reference {
    private PDO $db;
    private string $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        // "references" is a reserved word - quote appropriately per driver
        $this->table = (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') ? '"references"' : '`references`';
    }

    public function getAll(int $offset = 0, int $limit = RECORDS_PER_PAGE, string $search = ''): array {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (title LIKE :s OR author LIKE :s OR citation LIKE :s)";
            $params[':s'] = "%{$search}%";
        }
        $sql .= " ORDER BY year DESC, title ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        if ($search) {
            $sql .= " AND (title LIKE :s OR author LIKE :s OR citation LIKE :s)";
            $params[':s'] = "%{$search}%";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} (title, author, year, citation)
             VALUES (:title, :author, :year, :citation)"
        );
        $stmt->execute([
            ':title'    => $data['title'],
            ':author'   => $data['author'],
            ':year'     => $data['year'],
            ':citation' => $data['citation'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET title=:title, author=:author, year=:year, citation=:citation WHERE id=:id"
        );
        return $stmt->execute([
            ':title'    => $data['title'],
            ':author'   => $data['author'],
            ':year'     => $data['year'],
            ':citation' => $data['citation'],
            ':id'       => $id,
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllSimple(): array {
        return $this->db->query("SELECT id, title, author, year FROM {$this->table} ORDER BY year DESC, title")->fetchAll();
    }
}
