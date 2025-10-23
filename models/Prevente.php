<?php
// models/Prevente.php

class Prevente {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM Prevente WHERE id_prevente = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findActiveByProduct(int $productId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM Prevente WHERE id_produit = ? AND statut IN ('en cours','en_attente') ORDER BY id_prevente DESC LIMIT 1"
        );
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO Prevente (date_limite, nombre_min, statut, prix_prevente, id_produit) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['date_limite'],
            (int)$data['nombre_min'],
            $data['statut'] ?? 'en cours',
            (float)$data['prix_prevente'],
            (int)$data['id_produit'],
        ]);
    }

    public function updateStatut(int $id, string $statut): bool {
        $stmt = $this->db->prepare("UPDATE Prevente SET statut = ? WHERE id_prevente = ?");
        return $stmt->execute([$statut, $id]);
    }
}

?>


