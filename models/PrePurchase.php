<?php
// models/PrePurchase.php

class PrePurchase {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS pre_purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produit INT NOT NULL,
            id_client INT NOT NULL,
            status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_produit),
            INDEX (id_client)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->pdo->exec($sql);
    }

    public function create(int $productId, int $clientId): bool {
        $stmt = $this->pdo->prepare("INSERT INTO pre_purchases (id_produit, id_client) VALUES (?, ?)");
        return $stmt->execute([$productId, $clientId]);
    }

    public function confirm(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE pre_purchases SET status='confirmed' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function cancel(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE pre_purchases SET status='cancelled' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function findByProductAndUser(int $productId, int $clientId) {
        $stmt = $this->pdo->prepare("SELECT * FROM pre_purchases WHERE id_produit=? AND id_client=? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$productId, $clientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getForProduct(int $productId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM pre_purchases WHERE id_produit=? ORDER BY created_at DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


