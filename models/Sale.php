<?php
// models/Sale.php

class Sale {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            buyer_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (product_id), INDEX (buyer_id), INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->pdo->exec($sql);
    }

    public function create(int $productId, int $buyerId, float $amount): bool {
        $stmt = $this->pdo->prepare("INSERT INTO sales (product_id, buyer_id, amount) VALUES (?,?,?)");
        return $stmt->execute([$productId, $buyerId, $amount]);
    }

    public function countByDayLast30(): array {
        $stmt = $this->pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d ORDER BY d");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalAmountByDayLast30(): array {
        $stmt = $this->pdo->query("SELECT DATE(created_at) d, SUM(amount) s FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d ORDER BY d");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByBuyer(int $buyerId): array {
        $stmt = $this->pdo->prepare("SELECT s.*, p.description, p.prix, p.id_produit, p.id_vendeur FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE s.buyer_id = ? ORDER BY s.created_at DESC");
        $stmt->execute([$buyerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastInsertId(): int {
        return (int)$this->pdo->lastInsertId();
    }

    public function userHasPurchasedProduct(int $buyerId, int $productId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM sales WHERE buyer_id = ? AND product_id = ? LIMIT 1");
        $stmt->execute([$buyerId, $productId]);
        return (bool)$stmt->fetchColumn();
    }

    public function userHasPurchasedFromSeller(int $buyerId, int $sellerId): bool {
        $stmt = $this->pdo->prepare("SELECT 1 FROM sales s JOIN Produit p ON s.product_id = p.id_produit WHERE s.buyer_id = ? AND p.id_vendeur = ? LIMIT 1");
        $stmt->execute([$buyerId, $sellerId]);
        return (bool)$stmt->fetchColumn();
    }
}


