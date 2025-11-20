<?php

class Review {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            rating TINYINT NOT NULL,
            comment TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (product_id),
            INDEX (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->db->exec($sql);
    }

    public function createOrUpdate(int $productId, int $userId, int $rating, string $comment): bool {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$productId, $userId]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            $stmt = $this->db->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
            return $stmt->execute([$rating, $comment, $existingId]);
        }

        $stmt = $this->db->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?,?,?,?)");
        return $stmt->execute([$productId, $userId, $rating, $comment]);
    }

    public function getByProduct(int $productId): array {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.nom, u.prenom
             FROM reviews r
             JOIN Utilisateur u ON r.user_id = u.id_user
             WHERE r.product_id = ?
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageForProduct(int $productId): float {
        $stmt = $this->db->prepare("SELECT AVG(rating) FROM reviews WHERE product_id = ?");
        $stmt->execute([$productId]);
        $avg = $stmt->fetchColumn();
        return $avg ? (float)$avg : 0.0;
    }

    public function userHasReviewed(int $productId, int $userId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$productId, $userId]);
        return (bool)$stmt->fetchColumn();
    }
}
