<?php
/**
 * Script d'initialisation des taxes
 * Crée la table des paramètres du site et initialise les taxes
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    
    // Créer la table des paramètres du site
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Insérer les paramètres par défaut
    $defaultSettings = [
        [
            'key' => 'tax_rate',
            'value' => '20.00',
            'description' => 'Taux de taxe en pourcentage (ex: 20.00 pour 20%)'
        ],
        [
            'key' => 'tax_enabled',
            'value' => '1',
            'description' => 'Activer/désactiver les taxes (1 = activé, 0 = désactivé)'
        ],
        [
            'key' => 'tax_name',
            'value' => 'TVA',
            'description' => 'Nom de la taxe (ex: TVA, Tax, etc.)'
        ]
    ];
    
    foreach ($defaultSettings as $setting) {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value, description) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value),
            description = VALUES(description)
        ");
        $stmt->execute([$setting['key'], $setting['value'], $setting['description']]);
    }
    
    echo "✅ Table des paramètres créée et initialisée avec succès !\n";
    echo "📊 Paramètres par défaut :\n";
    echo "   - Taux de taxe : 20%\n";
    echo "   - Taxes activées : Oui\n";
    echo "   - Nom de la taxe : TVA\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors de l'initialisation : " . $e->getMessage() . "\n";
}
?>
