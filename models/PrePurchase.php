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
            quantity INT NOT NULL DEFAULT 1,
            expires_at DATETIME NULL,
            status ENUM('pending','confirmed','cancelled','expired') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_produit),
            INDEX (id_client),
            INDEX (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->pdo->exec($sql);
    }

    public function create(int $productId, int $clientId, int $quantity = 1, ?string $expiresAt = null): bool {
        $stmt = $this->pdo->prepare("INSERT INTO pre_purchases (id_produit, id_client, quantity, expires_at) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$productId, $clientId, $quantity, $expiresAt]);
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

    public function checkExpired(): int {
        $stmt = $this->pdo->prepare("UPDATE pre_purchases SET status='expired' WHERE expires_at IS NOT NULL AND expires_at < NOW() AND status='pending'");
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getTotalQuantityForProduct(int $productId): int {
        $stmt = $this->pdo->prepare("SELECT SUM(quantity) as total FROM pre_purchases WHERE id_produit=? AND status IN ('pending', 'confirmed')");
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }

    public function isExpired(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT expires_at FROM pre_purchases WHERE id=?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || !$result['expires_at']) return false;
        return strtotime($result['expires_at']) < time();
    }

    public function getByUser(int $userId): array {
        // Vérifier si la colonne quantity existe
        $hasQuantityColumn = $this->hasQuantityColumn();
        
        if ($hasQuantityColumn) {
            $stmt = $this->pdo->prepare("
                SELECT pp.*, p.description, p.prix, p.image, p.quantity as product_quantity
                FROM pre_purchases pp
                JOIN Produit p ON pp.id_produit = p.id_produit
                WHERE pp.id_client = ?
                ORDER BY pp.created_at DESC
            ");
        } else {
            $stmt = $this->pdo->prepare("
                SELECT pp.*, p.description, p.prix, p.image, 1 as product_quantity
                FROM pre_purchases pp
                JOIN Produit p ON pp.id_produit = p.id_produit
                WHERE pp.id_client = ?
                ORDER BY pp.created_at DESC
            ");
        }
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM pre_purchases WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifier si la colonne quantity existe dans la table Produit
     */
    private function hasQuantityColumn() {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM Produit LIKE 'quantity'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}


