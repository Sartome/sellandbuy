<?php
// models/ProduitImage.php

class ProduitImage {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ajouter une image à un produit
     */
    public function addImage($productId, $imageData) {
        $stmt = $this->db->prepare("
            INSERT INTO ProduitImages (id_produit, image_path, image_alt, image_size, image_width, image_height, is_primary, sort_order) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $productId,
            $imageData['webPath'],
            $imageData['alt'] ?? '',
            $imageData['size'],
            $imageData['width'],
            $imageData['height'],
            $imageData['is_primary'] ?? false,
            $imageData['sort_order'] ?? 0
        ]);
    }

    /**
     * Récupérer toutes les images d'un produit
     */
    public function getImagesByProduct($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM ProduitImages 
            WHERE id_produit = ? 
            ORDER BY is_primary DESC, sort_order ASC, created_at ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer l'image principale d'un produit
     */
    public function getPrimaryImage($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM ProduitImages 
            WHERE id_produit = ? AND is_primary = 1 
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Définir une image comme principale
     */
    public function setPrimaryImage($imageId, $productId) {
        // D'abord, retirer le statut principal de toutes les images du produit
        $stmt = $this->db->prepare("UPDATE ProduitImages SET is_primary = 0 WHERE id_produit = ?");
        $stmt->execute([$productId]);
        
        // Puis définir la nouvelle image principale
        $stmt = $this->db->prepare("UPDATE ProduitImages SET is_primary = 1 WHERE id_image = ? AND id_produit = ?");
        return $stmt->execute([$imageId, $productId]);
    }

    /**
     * Supprimer une image
     */
    public function deleteImage($imageId) {
        // Récupérer les infos de l'image avant suppression
        $stmt = $this->db->prepare("SELECT * FROM ProduitImages WHERE id_image = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$image) return false;
        
        // Supprimer de la base de données
        $stmt = $this->db->prepare("DELETE FROM ProduitImages WHERE id_image = ?");
        $success = $stmt->execute([$imageId]);
        
        if ($success && $image) {
            // Supprimer le fichier physique
            require_once HELPERS_PATH . '/ImageUpload.php';
            $imageUpload = new ImageUpload();
            $filename = basename($image['image_path']);
            $imageUpload->deleteImage($filename);
        }
        
        return $success;
    }

    /**
     * Mettre à jour l'ordre des images
     */
    public function updateImageOrder($imageId, $newOrder) {
        $stmt = $this->db->prepare("UPDATE ProduitImages SET sort_order = ? WHERE id_image = ?");
        return $stmt->execute([$newOrder, $imageId]);
    }

    /**
     * Compter le nombre d'images d'un produit
     */
    public function countImagesByProduct($productId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ProduitImages WHERE id_produit = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchColumn();
    }

    /**
     * Récupérer une image par son ID
     */
    public function getImageById($imageId) {
        $stmt = $this->db->prepare("SELECT * FROM ProduitImages WHERE id_image = ?");
        $stmt->execute([$imageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les statistiques des images
     */
    public function getImageStats() {
        $stats = [];
        
        // Nombre total d'images
        $stmt = $this->db->query("SELECT COUNT(*) FROM ProduitImages");
        $stats['total_images'] = $stmt->fetchColumn();
        
        // Taille totale des images
        $stmt = $this->db->query("SELECT SUM(image_size) FROM ProduitImages");
        $stats['total_size'] = $stmt->fetchColumn() ?? 0;
        
        // Images par produit
        $stmt = $this->db->query("
            SELECT COUNT(*) as image_count 
            FROM ProduitImages 
            GROUP BY id_produit 
            ORDER BY image_count DESC 
            LIMIT 1
        ");
        $stats['max_images_per_product'] = $stmt->fetchColumn() ?? 0;
        
        // Moyenne des dimensions
        $stmt = $this->db->query("
            SELECT AVG(image_width) as avg_width, AVG(image_height) as avg_height 
            FROM ProduitImages
        ");
        $dimensions = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['avg_dimensions'] = [
            'width' => round($dimensions['avg_width'] ?? 0),
            'height' => round($dimensions['avg_height'] ?? 0)
        ];
        
        return $stats;
    }
}
