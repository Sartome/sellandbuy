<?php
// config/database.php

// Configuration EXACTE pour ton environnement DDEV
define('DB_HOST', 'db'); 
define('DB_NAME', 'db');    // On met 'db' car ton terminal affiche "Tables_in_db"
define('DB_USER', 'db');    // L'utilisateur par défaut DDEV
define('DB_PASS', 'db');    // Le mot de passe par défaut DDEV
define('DB_CHARSET', 'utf8mb4');

// Le reste de ton code pour initialiser la connexion...
require_once __DIR__ . '/../models/Database.php';

try {
    $db = Database::getInstance();
    // Si tu arrives ici sans message d'erreur, c'est que c'est gagné !
} catch (Exception $e) {
    die("Erreur de connexion: " . $e->getMessage());
}