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
        
        // Find a valid admin/gestionnaire to assign ownership
        $stmtAdmin = $this->db->query("SELECT id_user FROM Gestionnaire LIMIT 1");
        $adminId = $stmtAdmin->fetchColumn();
        if (!$adminId) {
            // Create a default fallback admin user to satisfy foreign key constraints
            $insUser = $this->db->prepare("INSERT INTO Utilisateur (nom, prenom, email, motdepasse) VALUES (?, ?, ?, ?)");
            $insUser->execute(['System', 'Admin', 'system_admin_' . time() . '@sellandbuy.local', password_hash('admin123', PASSWORD_DEFAULT)]);
            $newAdminId = $this->db->lastInsertId();
            
            $insGest = $this->db->prepare("INSERT INTO Gestionnaire (id_user) VALUES (?)");
            $insGest->execute([$newAdminId]);
            $adminId = $newAdminId;
        }

        $ins = $this->db->prepare("INSERT INTO Categorie (id_gestionnaire, lib) VALUES (?, ?)");
        $ins->execute([$adminId, 'Acquisition']);
        return (int)$this->db->lastInsertId();
    }
}


