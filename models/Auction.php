<?php
// models/Auction.php

class Auction {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ensureTables();
    }

    private function ensureTables(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS auctions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_produit INT NOT NULL,
            starting_price DECIMAL(10,2) NOT NULL,
            current_price DECIMAL(10,2) NOT NULL,
            ends_at DATETIME NOT NULL,
            status ENUM('active','ended') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (id_produit), INDEX (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS bids (
            id INT AUTO_INCREMENT PRIMARY KEY,
            auction_id INT NOT NULL,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (auction_id), INDEX (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function create(int $productId, float $startingPrice, string $endsAt): bool {
        $stmt = $this->pdo->prepare("INSERT INTO auctions (id_produit, starting_price, current_price, ends_at) VALUES (?,?,?,?)");
        return $stmt->execute([$productId, $startingPrice, $startingPrice, $endsAt]);
    }

    public function getByProduct(int $productId) {
        $stmt = $this->pdo->prepare("SELECT * FROM auctions WHERE id_produit=? AND status='active' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $auctionId) {
        $stmt = $this->pdo->prepare("SELECT * FROM auctions WHERE id=?");
        $stmt->execute([$auctionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function placeBid(int $auctionId, int $userId, float $amount): bool {
        $auction = $this->findById($auctionId);
        if (!$auction || $auction['status'] !== 'active') { return false; }
        if (strtotime($auction['ends_at']) <= time()) { return false; }
        if ($amount <= (float)$auction['current_price']) { return false; }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("INSERT INTO bids (auction_id, user_id, amount) VALUES (?,?,?)");
            $stmt->execute([$auctionId, $userId, $amount]);

            $stmt2 = $this->pdo->prepare("UPDATE auctions SET current_price=? WHERE id=?");
            $stmt2->execute([$amount, $auctionId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function listBids(int $auctionId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM bids WHERE auction_id=? ORDER BY amount DESC, created_at DESC");
        $stmt->execute([$auctionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countDistinctBidders(int $auctionId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(DISTINCT user_id) AS c FROM bids WHERE auction_id=?");
        $stmt->execute([$auctionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }
}


