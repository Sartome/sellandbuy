<?php
// Test simple de la connexion
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple de Connexion</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST reçu</h2>";
    echo "<p>Email: " . htmlspecialchars($_POST['email'] ?? '') . "</p>";
    echo "<p>Password: " . str_repeat('*', strlen($_POST['password'] ?? '')) . "</p>";
    
    // Simuler le processus de connexion
    session_start();
    
    try {
        require_once 'config/constants.php';
        require_once 'config/database.php';
        require_once 'models/Database.php';
        require_once 'models/Utilisateur.php';
        require_once 'helpers/functions.php';
        
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $userModel = new Utilisateur();
        $user = $userModel->findByEmail($email);
        
        if ($user && password_verify($password, $user['motdepasse'])) {
            echo "<p style='color: green;'>✅ Connexion réussie !</p>";
            
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email'];
            
            echo "<p>Session créée pour : " . htmlspecialchars($_SESSION['username']) . "</p>";
            echo "<p><a href='index.php?controller=product&action=index'>Aller aux produits</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Identifiants invalides</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Erreur: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<form method='post'>";
    echo "<p><input type='email' name='email' placeholder='Email' value='test@example.com' required></p>";
    echo "<p><input type='password' name='password' placeholder='Mot de passe' value='password123' required></p>";
    echo "<p><button type='submit'>Se connecter</button></p>";
    echo "</form>";
    
    echo "<p><a href='create_test_user.php'>Créer utilisateurs test</a></p>";
}
?>
