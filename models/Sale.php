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
}


