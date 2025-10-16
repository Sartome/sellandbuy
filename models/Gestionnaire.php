<?php
// models/Gestionnaire.php

class Gestionnaire {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function isAdminUser(int $idUser): bool {
        $stmt = $this->db->prepare("SELECT 1 FROM Gestionnaire WHERE id_user = ? LIMIT 1");
        $stmt->execute([$idUser]);
        return (bool)$stmt->fetchColumn();
    }
}


