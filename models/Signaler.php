<?php
// models/Signaler.php

class Signaler {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("INSERT INTO Signaler (id_user, id_produit, date_signal) VALUES (?, ?, ?) ");
        return $stmt->execute([$userId, $productId, date('Y-m-d')]);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT s.*, u.nom, u.prenom, p.description, p.id_produit, p.id_vendeur FROM Signaler s LEFT JOIN Utilisateur u ON s.id_user = u.id_user LEFT JOIN Produit p ON s.id_produit = p.id_produit ORDER BY s.date_signal DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByProduct(int $productId): array {
        $stmt = $this->db->prepare("SELECT s.*, u.nom, u.prenom FROM Signaler s LEFT JOIN Utilisateur u ON s.id_user = u.id_user WHERE s.id_produit = ? ORDER BY s.date_signal DESC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Signaler WHERE id_signal = ?");
        return $stmt->execute([$id]);
    }
}
?>