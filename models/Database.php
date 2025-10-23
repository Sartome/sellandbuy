<?php
/**
 * Singleton de connexion à la base de données
 * 
 * Implémente le pattern Singleton pour garantir une seule instance
 * de connexion PDO dans toute l'application.
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     * Établit la connexion PDO avec la base de données
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception("Erreur de connexion: " . $e->getMessage());
        }
    }
    
    /**
     * Retourne l'instance unique de la classe
     * Crée une nouvelle instance si elle n'existe pas
     * @return Database Instance unique de la classe
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retourne la connexion PDO
     * @return PDO Connexion à la base de données
     */
    public function getConnection() {
        return $this->pdo;
    }
}
