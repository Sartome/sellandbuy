<?php
/**
 * Script d'initialisation des catégories
 * Crée les catégories par défaut si elles n'existent pas
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Categorie.php';

echo "<h1>🏷️ Initialisation des Catégories</h1>";

try {
    $categorie = new Categorie();
    
    // Vérifier les catégories existantes
    $categories = $categorie->getAll();
    echo "<p><strong>Catégories existantes :</strong> " . count($categories) . "</p>";
    
    if (!empty($categories)) {
        echo "<ul>";
        foreach ($categories as $cat) {
            echo "<li>ID: {$cat['id_categorie']} - Libellé: {$cat['lib']}</li>";
        }
        echo "</ul>";
    }
    
    // Créer la catégorie par défaut si nécessaire
    if (empty($categories)) {
        echo "<p><strong>Création de la catégorie par défaut...</strong></p>";
        $defaultId = $categorie->ensureDefaultAcquisition();
        echo "<p>✅ Catégorie 'Acquisition' créée avec l'ID: $defaultId</p>";
    } else {
        echo "<p>✅ Catégories déjà présentes</p>";
    }
    
    // Créer des catégories supplémentaires si nécessaire
    $additionalCategories = [
        'Électronique',
        'Vêtements',
        'Maison & Jardin',
        'Sports & Loisirs',
        'Livres & Médias',
        'Automobile',
        'Informatique'
    ];
    
    $pdo = Database::getInstance()->getConnection();
    $created = 0;
    
    foreach ($additionalCategories as $catName) {
        // Vérifier si la catégorie existe déjà
        $stmt = $pdo->prepare("SELECT id_categorie FROM Categorie WHERE lib = ? LIMIT 1");
        $stmt->execute([$catName]);
        $exists = $stmt->fetchColumn();
        
        if (!$exists) {
            $stmt = $pdo->prepare("INSERT INTO Categorie (id_gestionnaire, lib) VALUES (NULL, ?)");
            $stmt->execute([$catName]);
            echo "<p>✅ Catégorie '$catName' créée</p>";
            $created++;
        } else {
            echo "<p>ℹ️ Catégorie '$catName' existe déjà</p>";
        }
    }
    
    echo "<p><strong>Résultat :</strong> $created nouvelle(s) catégorie(s) créée(s)</p>";
    
    // Afficher toutes les catégories finales
    $finalCategories = $categorie->getAll();
    echo "<h2>📋 Liste Finale des Catégories</h2>";
    echo "<ul>";
    foreach ($finalCategories as $cat) {
        echo "<li>ID: {$cat['id_categorie']} - Libellé: {$cat['lib']}</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erreur :</strong> " . $e->getMessage() . "</p>";
}

echo "<p><a href='index.php?controller=admin&action=debug'>🔧 Retour au Debug Système</a></p>";
?>
