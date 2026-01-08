<?php

// Définir l'environnement de test
putenv('APP_ENV=testing');

// Charger l'autoloader de Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Constantes de l'application
define('TEST_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__));

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Charger la configuration de la base de données
require_once APP_ROOT . '/config/database.php';

// Configuration de la base de données de test
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'vente_groupe';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';

// Établir la connexion à la base de données
try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $db = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    // Stocker la connexion dans une variable globale
    $GLOBALS['db'] = $db;
    
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Fonctions utilitaires pour les tests
if (!function_exists('dd')) {
    /**
     * Dump and die - utile pour le débogage
     */
    function dd() {
        array_map(function($x) { 
            var_dump($x); 
        }, func_get_args());
        die(1);
    }
}
