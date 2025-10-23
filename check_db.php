<?php
// Script rapide pour vérifier l'état de la base de données
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Vérification rapide de la base de données</h1>";

try {
    require_once 'config/constants.php';
    require_once 'config/database.php';
    require_once 'models/Database.php';
    
    $db = Database::getInstance();
    
    // Compter les utilisateurs
    $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM Utilisateur");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $result['count'];
    
    echo "<p><strong>Nombre d'utilisateurs : $userCount</strong></p>";
    
    if ($userCount == 0) {
        echo "<div style='background:#f8d7da;padding:15px;border-left:4px solid #dc3545;margin:20px 0;'>";
        echo "<h3>❌ Problème identifié : Base de données vide</h3>";
        echo "<p>Votre base de données ne contient aucun utilisateur. C'est pourquoi la connexion ne fonctionne pas.</p>";
        echo "<p><strong>Solution :</strong> <a href='create_test_user.php' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>Créer des utilisateurs de test</a></p>";
        echo "</div>";
    } else {
        echo "<div style='background:#d4edda;padding:15px;border-left:4px solid #28a745;margin:20px 0;'>";
        echo "<h3>✅ Base de données prête</h3>";
        echo "<p>Votre base de données contient $userCount utilisateur(s).</p>";
        echo "<p><a href='index.php?controller=auth&action=login' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>Tester la connexion</a></p>";
        echo "</div>";
        
        // Afficher les utilisateurs existants
        $stmt = $db->getConnection()->query("SELECT id_user, email, nom, prenom FROM Utilisateur LIMIT 5");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>👥 Utilisateurs existants :</h3>";
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: {$user['id_user']} - {$user['email']} ({$user['prenom']} {$user['nom']})</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da;padding:15px;border-left:4px solid #dc3545;margin:20px 0;'>";
    echo "<h3>❌ Erreur de connexion</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
