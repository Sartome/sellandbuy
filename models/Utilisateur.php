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
}


