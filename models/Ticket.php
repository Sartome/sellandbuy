<?php

class Ticket {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ensureTable();
    }

    private function ensureTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS tickets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            admin_response TEXT NULL,
            status ENUM('open','answered','closed') NOT NULL DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->pdo->exec($sql);
    }

    public function create(int $userId, string $subject, string $message): bool {
        $stmt = $this->pdo->prepare("INSERT INTO tickets (user_id, subject, message) VALUES (?,?,?)");
        return $stmt->execute([$userId, $subject, $message]);
    }

    public function getByUser(int $userId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT t.*, u.email, u.prenom, u.nom FROM tickets t JOIN Utilisateur u ON t.user_id = u.id_user ORDER BY t.created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function answer(int $id, string $adminResponse): bool {
        $stmt = $this->pdo->prepare("UPDATE tickets SET admin_response = ?, status = 'answered' WHERE id = ?");
        return $stmt->execute([$adminResponse, $id]);
    }

    public function close(int $id): bool {
        $stmt = $this->pdo->prepare("UPDATE tickets SET status = 'closed' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
