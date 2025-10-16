<?php
// models/Categorie.php

class Categorie {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM Categorie ORDER BY lib");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ensureDefaultAcquisition(): int {
        // Create default 'Acquisition' category if not exists and return its id
        $stmt = $this->db->prepare("SELECT id_categorie FROM Categorie WHERE lib = ? LIMIT 1");
        $stmt->execute(['Acquisition']);
        $id = $stmt->fetchColumn();
        if ($id) {
            return (int)$id;
        }
        $ins = $this->db->prepare("INSERT INTO Categorie (id_gestionnaire, lib) VALUES (NULL, ?)");
        $ins->execute(['Acquisition']);
        return (int)$this->db->lastInsertId();
    }
}


