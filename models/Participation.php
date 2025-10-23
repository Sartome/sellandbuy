<?php
// models/Participation.php

class Participation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function existsForClientAndPrevente(int $clientId, int $preventeId): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Participation WHERE id_client = ? AND id_prevente = ? LIMIT 1");
        $stmt->execute([$clientId, $preventeId]);
        return (bool)$stmt->fetchColumn();
    }

    public function countByPrevente(int $preventeId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM Participation WHERE id_prevente = ?");
        $stmt->execute([$preventeId]);
        return (int)$stmt->fetchColumn();
    }

    public function create(int $clientId, int $preventeId, ?int $factureId = null): bool {
        $stmt = $this->db->prepare("INSERT INTO Participation (id_client, id_prevente, id_facture) VALUES (?, ?, ?)");
        return $stmt->execute([$clientId, $preventeId, $factureId]);
    }
}

?>


