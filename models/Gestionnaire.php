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

    /**
     * Créer un nouveau gestionnaire (administrateur)
     */
    public function create(int $idUser): bool {
        try {
            // Vérifier si l'utilisateur existe déjà comme gestionnaire
            if ($this->isAdminUser($idUser)) {
                return false; // Déjà admin
            }

            $stmt = $this->db->prepare("INSERT INTO Gestionnaire (id_user) VALUES (?)");
            return $stmt->execute([$idUser]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Supprimer un gestionnaire (retirer les privilèges admin)
     */
    public function delete(int $idUser): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM Gestionnaire WHERE id_user = ?");
            return $stmt->execute([$idUser]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Obtenir tous les gestionnaires
     */
    public function getAll(): array {
        try {
            $stmt = $this->db->query("
                SELECT g.*, u.nom, u.prenom, u.email, u.created_at
                FROM Gestionnaire g
                JOIN Utilisateur u ON g.id_user = u.id_user
                ORDER BY u.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}


