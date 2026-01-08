<?php
// models/Bloquer.php

class Bloquer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $gestionnaireId, int $vendeurId): bool {
        $stmt = $this->db->prepare("INSERT INTO Bloquer (id_gestionnaire, id_vendeur) VALUES (?, ?)");
        return $stmt->execute([$gestionnaireId, $vendeurId]);
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT b.*, u.nom, u.prenom, v.nom_entreprise FROM Bloquer b LEFT JOIN Gestionnaire g ON b.id_gestionnaire = g.id_user LEFT JOIN Vendeur v ON b.id_vendeur = v.id_user LEFT JOIN Utilisateur u ON v.id_user = u.id_user ORDER BY b.date_blocage DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function exists(int $vendeurId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Bloquer WHERE id_vendeur = ? LIMIT 1");
        $stmt->execute([$vendeurId]);
        return (bool)$stmt->fetchColumn();
    }

    public function removeByVendor(int $vendeurId): bool {
        $stmt = $this->db->prepare("DELETE FROM Bloquer WHERE id_vendeur = ?");
        return $stmt->execute([$vendeurId]);
    }
}
?>