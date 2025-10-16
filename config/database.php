<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'vente_groupe');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Initialiser la connexion via le modèle Database
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance();
} catch (Exception $e) {
    die("Erreur de connexion: " . $e->getMessage());
}
