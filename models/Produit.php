<?php
// models/Produit.php

class Produit {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query(
            "SELECT p.*, v.nom_entreprise, c.lib AS categorie
             FROM Produit p
             LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
             LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
             ORDER BY p.created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare(
            "SELECT p.*, v.nom_entreprise, c.lib AS categorie
             FROM Produit p
             LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
             LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
             WHERE p.id_produit = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        // Vérifier si la colonne quantity existe
        $hasQuantityColumn = $this->hasQuantityColumn();
        
        if ($hasQuantityColumn) {
            $stmt = $this->db->prepare(
                "INSERT INTO Produit (description, prix, image, image_alt, image_size, image_width, image_height, id_vendeur, id_categorie, quantity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $data['description'],
                $data['prix'],
                $data['image'] ?? null,
                $data['image_alt'] ?? null,
                $data['image_size'] ?? null,
                $data['image_width'] ?? null,
                $data['image_height'] ?? null,
                $data['id_vendeur'],
                $data['id_categorie'] ?? null,
                $data['quantity'] ?? 1,
            ]);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO Produit (description, prix, image, image_alt, image_size, image_width, image_height, id_vendeur, id_categorie) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $data['description'],
                $data['prix'],
                $data['image'] ?? null,
                $data['image_alt'] ?? null,
                $data['image_size'] ?? null,
                $data['image_width'] ?? null,
                $data['image_height'] ?? null,
                $data['id_vendeur'],
                $data['id_categorie'] ?? null,
            ]);
        }
    }

    public function deleteById(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Produit WHERE id_produit = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtenir l'ID du dernier produit créé
     */
    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Mettre à jour l'image principale d'un produit
     */
    public function updateImage(int $id, string $imagePath): bool {
        $stmt = $this->db->prepare("UPDATE Produit SET image = ? WHERE id_produit = ?");
        return $stmt->execute([$imagePath, $id]);
    }

    /**
     * Rechercher des produits par terme
     */
    public function search(string $term) {
        $stmt = $this->db->prepare(
            "SELECT p.*, v.nom_entreprise, c.lib AS categorie
             FROM Produit p
             LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
             LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
             WHERE p.description LIKE ? OR v.nom_entreprise LIKE ? OR c.lib LIKE ?
             ORDER BY p.created_at DESC"
        );
        $searchTerm = '%' . $term . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir les produits par catégorie
     */
    public function getByCategory(int $categoryId) {
        $stmt = $this->db->prepare(
            "SELECT p.*, v.nom_entreprise, c.lib AS categorie
             FROM Produit p
             LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
             LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
             WHERE p.id_categorie = ?
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir les produits d'un vendeur
     */
    public function getByVendor(int $vendorId) {
        $stmt = $this->db->prepare(
            "SELECT p.*, v.nom_entreprise, c.lib AS categorie
             FROM Produit p
             LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
             LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
             WHERE p.id_vendeur = ?
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtenir les statistiques des produits
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de produits
        $stmt = $this->db->query("SELECT COUNT(*) FROM Produit");
        $stats['total_products'] = $stmt->fetchColumn();
        
        // Prix moyen
        $stmt = $this->db->query("SELECT AVG(prix) FROM Produit");
        $stats['average_price'] = round($stmt->fetchColumn() ?? 0, 2);
        
        // Prix minimum et maximum
        $stmt = $this->db->query("SELECT MIN(prix), MAX(prix) FROM Produit");
        $priceRange = $stmt->fetch(PDO::FETCH_NUM);
        $stats['min_price'] = $priceRange[0] ?? 0;
        $stats['max_price'] = $priceRange[1] ?? 0;
        
        // Produits par catégorie
        $stmt = $this->db->query("
            SELECT c.lib, COUNT(p.id_produit) as count 
            FROM Categorie c 
            LEFT JOIN Produit p ON c.id_categorie = p.id_categorie 
            GROUP BY c.id_categorie, c.lib 
            ORDER BY count DESC
        ");
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    /**
     * Vérifier si la colonne quantity existe
     */
    private function hasQuantityColumn() {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM Produit LIKE 'quantity'");
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Ajouter la colonne quantity si elle n'existe pas
     */
    public function addQuantityColumn() {
        try {
            $this->db->exec("ALTER TABLE Produit ADD COLUMN quantity INT DEFAULT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mettre à jour la quantité d'un produit
     */
    public function updateQuantity(int $id, int $quantity): bool {
        if (!$this->hasQuantityColumn()) {
            if (!$this->addQuantityColumn()) {
                return false;
            }
        }
        
        $stmt = $this->db->prepare("UPDATE Produit SET quantity = ? WHERE id_produit = ?");
        return $stmt->execute([$quantity, $id]);
    }

    /**
     * Réduire la quantité d'un produit (lors d'un achat)
     */
    public function decreaseQuantity(int $id, int $amount = 1): bool {
        if (!$this->hasQuantityColumn()) {
            return true; // Si pas de colonne quantity, considérer comme illimité
        }
        
        $stmt = $this->db->prepare("UPDATE Produit SET quantity = quantity - ? WHERE id_produit = ? AND quantity >= ?");
        return $stmt->execute([$amount, $id, $amount]);
    }

    /**
     * Vérifier si un produit est en stock
     */
    public function isInStock(int $id, int $requestedQuantity = 1): bool {
        if (!$this->hasQuantityColumn()) {
            return true; // Si pas de colonne quantity, considérer comme illimité
        }
        
        $stmt = $this->db->prepare("SELECT quantity FROM Produit WHERE id_produit = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && (int)$result['quantity'] >= $requestedQuantity;
    }

    /**
     * Obtenir la quantité disponible d'un produit
     */
    public function getAvailableQuantity(int $id): int {
        if (!$this->hasQuantityColumn()) {
            return 999; // Si pas de colonne quantity, considérer comme illimité
        }
        
        $stmt = $this->db->prepare("SELECT quantity FROM Produit WHERE id_produit = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['quantity'] : 0;
    }

    /**
     * Mettre à jour un produit
     */
    public function update(int $id, array $data): bool {
        // Construire la requête UPDATE dynamiquement
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, ['description', 'prix', 'id_categorie', 'image', 'image_alt', 'image_size', 'image_width', 'image_height', 'quantity'])) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false; // Aucun champ valide à mettre à jour
        }
        
        $values[] = $id; // Ajouter l'ID à la fin pour la clause WHERE
        
        $sql = "UPDATE Produit SET " . implode(', ', $fields) . " WHERE id_produit = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($values);
    }

    /**
     * Supprimer un produit (alias pour deleteById)
     */
    public function delete(int $id): bool {
        return $this->deleteById($id);
    }

    /**
     * Recherche avancée de produits avec filtres
     */
    public function searchProducts($search = '', $category = '', $priceMin = '', $priceMax = '') {
        $sql = "SELECT p.*, v.nom_entreprise, c.lib AS categorie, u.nom, u.prenom
                FROM Produit p
                LEFT JOIN Vendeur v ON p.id_vendeur = v.id_user
                LEFT JOIN Utilisateur u ON p.id_vendeur = u.id_user
                LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie
                WHERE 1=1";
        
        $params = [];
        
        // Filtre par recherche textuelle
        if (!empty($search)) {
            $sql .= " AND (p.description LIKE ? OR v.nom_entreprise LIKE ? OR u.nom LIKE ? OR u.prenom LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtre par catégorie
        if (!empty($category)) {
            $sql .= " AND p.id_categorie = ?";
            $params[] = $category;
        }
        
        // Filtre par prix minimum
        if (!empty($priceMin)) {
            $sql .= " AND p.prix >= ?";
            $params[] = $priceMin;
        }
        
        // Filtre par prix maximum
        if (!empty($priceMax)) {
            $sql .= " AND p.prix <= ?";
            $params[] = $priceMax;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


