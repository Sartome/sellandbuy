<?php
// models/Utilisateur.php

class Utilisateur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("SELECT * FROM Utilisateur WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare(
            "INSERT INTO Utilisateur (nom, prenom, adresse, phone, email, motdepasse) VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['nom'] ?? null,
            $data['prenom'] ?? null,
            $data['adresse'] ?? null,
            $data['phone'] ?? null,
            $data['email'],
            $data['motdepasse'],
        ]);
    }

    public function updateProfile(int $id, array $data) {
        $this->ensureAvatarColumn();

        $avatar = $data['avatar'] ?? null;

        $stmt = $this->db->prepare("UPDATE Utilisateur SET nom = ?, prenom = ?, adresse = ?, phone = ?, avatar = COALESCE(?, avatar) WHERE id_user = ?");
        return $stmt->execute([
            $data['nom'] ?? null,
            $data['prenom'] ?? null,
            $data['adresse'] ?? null,
            $data['phone'] ?? null,
            $avatar,
            $id,
        ]);
    }

    /**
     * Récupérer tous les utilisateurs
     */
    public function getAllUsers() {
        $hasCertifiedColumn = $this->vendorHasCertifiedColumn();

        if ($hasCertifiedColumn) {
            $sql = "
                SELECT u.*, 
                       CASE 
                           WHEN g.id_user IS NOT NULL THEN 'Administrateur'
                           WHEN v.id_user IS NOT NULL THEN 'Vendeur'
                           ELSE 'Utilisateur'
                       END as type,
                       v.nom_entreprise,
                       v.is_certified
                FROM Utilisateur u 
                LEFT JOIN Vendeur v ON u.id_user = v.id_user 
                LEFT JOIN Gestionnaire g ON u.id_user = g.id_user
                ORDER BY u.created_at DESC
            ";
        } else {
            $sql = "
                SELECT u.*, 
                       CASE 
                           WHEN g.id_user IS NOT NULL THEN 'Administrateur'
                           WHEN v.id_user IS NOT NULL THEN 'Vendeur'
                           ELSE 'Utilisateur'
                       END as type,
                       v.nom_entreprise,
                       0 as is_certified
                FROM Utilisateur u 
                LEFT JOIN Vendeur v ON u.id_user = v.id_user 
                LEFT JOIN Gestionnaire g ON u.id_user = g.id_user
                ORDER BY u.created_at DESC
            ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Rechercher des utilisateurs par nom, email ou téléphone
     */
    public function searchUsers($search) {
        $searchTerm = '%' . $search . '%';
        $hasCertifiedColumn = $this->vendorHasCertifiedColumn();

        if ($hasCertifiedColumn) {
            $sql = "
                SELECT u.*, 
                       CASE 
                           WHEN g.id_user IS NOT NULL THEN 'Administrateur'
                           WHEN v.id_user IS NOT NULL THEN 'Vendeur'
                           ELSE 'Utilisateur'
                       END as type,
                       v.nom_entreprise,
                       v.is_certified
                FROM Utilisateur u 
                LEFT JOIN Vendeur v ON u.id_user = v.id_user 
                LEFT JOIN Gestionnaire g ON u.id_user = g.id_user
                WHERE u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.phone LIKE ?
                ORDER BY u.created_at DESC
            ";
        } else {
            $sql = "
                SELECT u.*, 
                       CASE 
                           WHEN g.id_user IS NOT NULL THEN 'Administrateur'
                           WHEN v.id_user IS NOT NULL THEN 'Vendeur'
                           ELSE 'Utilisateur'
                       END as type,
                       v.nom_entreprise,
                       0 as is_certified
                FROM Utilisateur u 
                LEFT JOIN Vendeur v ON u.id_user = v.id_user 
                LEFT JOIN Gestionnaire g ON u.id_user = g.id_user
                WHERE u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR u.phone LIKE ?
                ORDER BY u.created_at DESC
            ";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprimer un utilisateur et toutes ses données liées
     */
    public function delete($userId) {
        try {
            $this->db->beginTransaction();
            
            // Supprimer dans l'ordre des dépendances (du plus spécifique au plus général)
            
            // 1. Supprimer les participations
            $stmt = $this->db->prepare("DELETE FROM Participation WHERE id_client = ?");
            $stmt->execute([$userId]);
            
            // 2. Supprimer les préventes
            $stmt = $this->db->prepare("DELETE FROM Prevente WHERE id_client = ?");
            $stmt->execute([$userId]);
            
            // 3. Supprimer les factures
            $stmt = $this->db->prepare("DELETE FROM Facture WHERE id_client = ?");
            $stmt->execute([$userId]);
            
            // 4. Supprimer les enchères liées aux produits de cet utilisateur (s'il est vendeur)
            $stmt = $this->db->prepare("DELETE FROM auctions WHERE id_produit IN (SELECT id_produit FROM Produit WHERE id_vendeur = ?)");
            $stmt->execute([$userId]);
            
            // 5. Supprimer les produits
            $stmt = $this->db->prepare("DELETE FROM Produit WHERE id_vendeur = ?");
            $stmt->execute([$userId]);
            
            // 6. Supprimer le vendeur
            $stmt = $this->db->prepare("DELETE FROM Vendeur WHERE id_user = ?");
            $stmt->execute([$userId]);
            
            // 7. Supprimer le client
            $stmt = $this->db->prepare("DELETE FROM Client WHERE id_user = ?");
            $stmt->execute([$userId]);
            
            // 8. Supprimer le gestionnaire (si applicable)
            $stmt = $this->db->prepare("DELETE FROM Gestionnaire WHERE id_user = ?");
            $stmt->execute([$userId]);
            
            // 9. Enfin, supprimer l'utilisateur
            $stmt = $this->db->prepare("DELETE FROM Utilisateur WHERE id_user = ?");
            $stmt->execute([$userId]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Erreur lors de la suppression : " . $e->getMessage());
        }
    }

    private function vendorHasCertifiedColumn() {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM Vendeur LIKE 'is_certified'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function hasAvatarColumn() {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM Utilisateur LIKE 'avatar'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function ensureAvatarColumn() {
        if (!$this->hasAvatarColumn()) {
            $this->db->exec("ALTER TABLE Utilisateur ADD COLUMN avatar VARCHAR(255) NULL AFTER phone");
        }
    }
}


