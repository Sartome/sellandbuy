<?php
// models/Client.php

class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(int $idUser) {
        $stmt = $this->db->prepare("INSERT INTO Client (id_user) VALUES (?)");
        return $stmt->execute([$idUser]);
    }
}


