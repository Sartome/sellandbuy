<?php
// models/Vendeur.php

class Vendeur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $idUser, array $data) {
        // Vérifier si la colonne is_certified existe
        $hasCertifiedColumn = $this->hasCertifiedColumn();
        
        if ($hasCertifiedColumn) {
            $stmt = $this->db->prepare(
                "INSERT INTO Vendeur (id_user, nom_entreprise, siret, adresse_entreprise, email_pro, is_certified) VALUES (?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $idUser,
                $data['nom_entreprise'] ?? null,
                $data['siret'] ?? null,
                $data['adresse_entreprise'] ?? null,
                $data['email_pro'] ?? null,
                0, // Par défaut non certifié
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO Vendeur (id_user, nom_entreprise, siret, adresse_entreprise, email_pro) VALUES (?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $idUser,
                $data['nom_entreprise'] ?? null,
                $data['siret'] ?? null,
                $data['adresse_entreprise'] ?? null,
                $data['email_pro'] ?? null,
            ]);
        }
    }

    public function findByUserId(int $idUser) {
        $stmt = $this->db->prepare("SELECT * FROM Vendeur WHERE id_user = ?");
        $stmt->execute([$idUser]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllVendors() {
        if ($this->hasCertifiedColumn()) {
            $stmt = $this->db->prepare("
                SELECT v.*, u.nom, u.prenom, u.email 
                FROM Vendeur v 
                JOIN Utilisateur u ON v.id_user = u.id_user 
                ORDER BY v.is_certified DESC, u.nom ASC
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT v.*, u.nom, u.prenom, u.email, 0 as is_certified
                FROM Vendeur v 
                JOIN Utilisateur u ON v.id_user = u.id_user 
                ORDER BY u.nom ASC
            ");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setCertification(int $vendorId, bool $isCertified) {
        if (!$this->hasCertifiedColumn()) {
            // Ajouter la colonne si elle n'existe pas
            if (!$this->addCertifiedColumn()) {
                return false;
            }
        }
        
        $stmt = $this->db->prepare("UPDATE Vendeur SET is_certified = ? WHERE id_user = ?");
        return $stmt->execute([$isCertified ? 1 : 0, $vendorId]);
    }

    public function isCertified(int $userId) {
        // Vérifier si la colonne is_certified existe
        if (!$this->hasCertifiedColumn()) {
            return false; // Par défaut non certifié si la colonne n'existe pas
        }
        
        $stmt = $this->db->prepare("SELECT is_certified FROM Vendeur WHERE id_user = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['is_certified'] : false;
    }

    private function hasCertifiedColumn() {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM Vendeur LIKE 'is_certified'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function addCertifiedColumn() {
        try {
            $this->db->exec("ALTER TABLE Vendeur ADD COLUMN is_certified TINYINT(1) DEFAULT 0");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Rechercher des vendeurs par nom, email ou entreprise
     */
    public function searchVendors($search) {
        $searchTerm = '%' . $search . '%';
        $stmt = $this->db->prepare("
            SELECT v.*, u.nom, u.prenom, u.email 
            FROM Vendeur v 
            JOIN Utilisateur u ON v.id_user = u.id_user 
            WHERE u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR v.nom_entreprise LIKE ?
            ORDER BY v.is_certified DESC, u.nom ASC
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer un vendeur et ses données liées
     */
    public function delete($vendorId) {
        try {
            $this->db->beginTransaction();
            
            // 1. Supprimer les enchères
            $stmt = $this->db->prepare("DELETE FROM auctions WHERE id_vendeur = ?");
            $stmt->execute([$vendorId]);
            
            // 2. Supprimer les produits
            $stmt = $this->db->prepare("DELETE FROM Produit WHERE id_vendeur = ?");
            $stmt->execute([$vendorId]);
            
            // 3. Supprimer le vendeur
            $stmt = $this->db->prepare("DELETE FROM Vendeur WHERE id_user = ?");
            $stmt->execute([$vendorId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression du vendeur : " . $e->getMessage());
        }
    }
}


