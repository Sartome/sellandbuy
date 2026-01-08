<?php
// models/Debloquer.php

class Debloquer {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $gestionnaireId, int $vendeurId): bool {
        $stmt = $this->db->prepare("INSERT INTO Debloquer (id_gestionnaire, id_vendeur) VALUES (?, ?)");
        return $stmt->execute([$gestionnaireId, $vendeurId]);
    }

    public function getRecentByVendor(int $vendeurId) {
        $stmt = $this->db->prepare("SELECT * FROM Debloquer WHERE id_vendeur = ? ORDER BY date_deblocage DESC LIMIT 1");
        $stmt->execute([$vendeurId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>