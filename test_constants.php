<?php
// Test des constantes
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test des constantes</h1>";

try {
    require_once 'config/constants.php';
    
    echo "<h2>Constantes définies :</h2>";
    echo "<ul>";
    echo "<li>ROOT_PATH: " . ROOT_PATH . "</li>";
    echo "<li>PUBLIC_PATH: " . PUBLIC_PATH . "</li>";
    echo "<li>VIEWS_PATH: " . VIEWS_PATH . "</li>";
    echo "<li>CONTROLLERS_PATH: " . CONTROLLERS_PATH . "</li>";
    echo "<li>MODELS_PATH: " . MODELS_PATH . "</li>";
    echo "<li>HELPERS_PATH: " . HELPERS_PATH . "</li>";
    echo "<li>BASE_URL: " . BASE_URL . "</li>";
    echo "<li>ASSETS_URL: " . ASSETS_URL . "</li>";
    echo "</ul>";
    
    echo "<h2>Test de chargement des fichiers :</h2>";
    
    // Test de chargement des modèles
    if (file_exists(MODELS_PATH . '/Database.php')) {
        echo "<p style='color: green;'>✅ Database.php trouvé</p>";
    } else {
        echo "<p style='color: red;'>❌ Database.php non trouvé</p>";
    }
    
    if (file_exists(MODELS_PATH . '/Utilisateur.php')) {
        echo "<p style='color: green;'>✅ Utilisateur.php trouvé</p>";
    } else {
        echo "<p style='color: red;'>❌ Utilisateur.php non trouvé</p>";
    }
    
    // Test de chargement des helpers
    if (file_exists(HELPERS_PATH . '/functions.php')) {
        echo "<p style='color: green;'>✅ functions.php trouvé</p>";
    } else {
        echo "<p style='color: red;'>❌ functions.php non trouvé</p>";
    }
    
    if (file_exists(HELPERS_PATH . '/ImageUpload.php')) {
        echo "<p style='color: green;'>✅ ImageUpload.php trouvé</p>";
    } else {
        echo "<p style='color: red;'>❌ ImageUpload.php non trouvé</p>";
    }
    
    echo "<h2>Test de connexion :</h2>";
    echo "<p><a href='index.php?controller=auth&action=login'>Page de connexion</a></p>";
    echo "<p><a href='test_simple.php'>Test simple</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
}
?>
