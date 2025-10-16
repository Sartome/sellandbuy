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
            "INSERT INTO Produit (description, prix, image, id_vendeur, id_categorie) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['description'],
            $data['prix'],
            $data['image'] ?? null,
            $data['id_vendeur'],
            $data['id_categorie'] ?? null,
        ]);
    }

    public function deleteById(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM Produit WHERE id_produit = ?");
        return $stmt->execute([$id]);
    }
}


