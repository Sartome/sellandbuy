<?php
// models/Vendeur.php

class Vendeur {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $idUser, array $data) {
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

    public function findByUserId(int $idUser) {
        $stmt = $this->db->prepare("SELECT * FROM Vendeur WHERE id_user = ?");
        $stmt->execute([$idUser]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


