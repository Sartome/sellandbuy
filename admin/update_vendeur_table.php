<?php
/**
 * Script de mise à jour de la table Vendeur
 * Ajoute la colonne is_certified et les timestamps
 */

require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🔄 Mise à jour de la table Vendeur...\n";
    
    // Vérifier si la colonne is_certified existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM Vendeur LIKE 'is_certified'");
    if ($stmt->rowCount() == 0) {
        // Ajouter la colonne is_certified
        $db->exec("ALTER TABLE Vendeur ADD COLUMN is_certified BOOLEAN DEFAULT FALSE");
        echo "✅ Colonne is_certified ajoutée\n";
    } else {
        echo "ℹ️  Colonne is_certified existe déjà\n";
    }
    
    // Vérifier si la colonne created_at existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM Vendeur LIKE 'created_at'");
    if ($stmt->rowCount() == 0) {
        // Ajouter les colonnes de timestamp
        $db->exec("ALTER TABLE Vendeur ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        $db->exec("ALTER TABLE Vendeur ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✅ Colonnes de timestamp ajoutées\n";
    } else {
        echo "ℹ️  Colonnes de timestamp existent déjà\n";
    }
    
    echo "🎉 Mise à jour terminée avec succès !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>
