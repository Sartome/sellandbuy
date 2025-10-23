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
}


