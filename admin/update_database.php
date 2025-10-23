<?php
/**
 * Script de mise à jour de la base de données
 * Ajoute la table site_settings si elle n'existe pas
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Mise à jour BD</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}</style></head><body>";

try {
    $pdo = Database::getInstance()->getConnection();
    
    echo "<h1>🔄 Mise à jour de la base de données</h1>";
    
    // Vérifier si la table existe déjà
    $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='info'>ℹ️ La table site_settings existe déjà</p>";
    } else {
        echo "<h2>Création de la table site_settings...</h2>";
        $pdo->exec("
            CREATE TABLE site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "<p class='success'>✅ Table site_settings créée</p>";
    }
    
    // Vérifier et insérer les paramètres
    echo "<h2>Configuration des paramètres...</h2>";
    
    $settings = [
        ['tax_rate', '20.00', 'Taux de taxe en pourcentage'],
        ['tax_enabled', '1', 'Activer/désactiver les taxes'],
        ['tax_name', 'TVA', 'Nom de la taxe']
    ];
    
    foreach ($settings as $s) {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value, description) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute($s);
        echo "<p class='success'>✅ Paramètre '{$s[0]}' configuré</p>";
    }
    
    echo "<h2 class='success'>🎉 Mise à jour terminée !</h2>";
    echo "<p><a href='../index.php?controller=admin&action=settings' style='background:#7c3aed;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Accéder aux paramètres</a></p>";
    echo "<p><a href='../index.php?controller=admin&action=index' style='background:#059669;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-left:10px;'>Tableau de bord</a></p>";
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</h2>";
}

echo "</body></html>";
?>
