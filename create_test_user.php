<?php
// Script pour créer un utilisateur de test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Création utilisateur test</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .info{background:#e7f3ff;padding:15px;border-left:4px solid #007cba;margin:10px 0;}</style></head><body>";

echo "<h1>🔧 Création d'un utilisateur de test</h1>";

try {
    // Charger la configuration
    require_once 'config/constants.php';
    require_once 'config/database.php';
    require_once 'models/Database.php';
    require_once 'models/Utilisateur.php';
    require_once 'models/Client.php';
    require_once 'models/Vendeur.php';
    
    $db = Database::getInstance();
    echo "<div class='info'><p><strong>Connexion à la base de données réussie</strong></p></div>";
    
    // Vérifier si la table Utilisateur existe et est vide
    $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM Utilisateur");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $userCount = $result['count'];
    
    echo "<p>Nombre d'utilisateurs actuels : <strong>$userCount</strong></p>";
    
    if ($userCount == 0) {
        echo "<div class='warning'><p><strong>⚠️ La base de données est vide !</strong></p></div>";
    }
    
    // Créer plusieurs utilisateurs de test
    $testUsers = [
        [
            'email' => 'test@example.com',
            'password' => 'password123',
            'nom' => 'Test',
            'prenom' => 'User',
            'role' => 'client'
        ],
        [
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'nom' => 'Admin',
            'prenom' => 'User',
            'role' => 'admin'
        ],
        [
            'email' => 'vendeur@example.com',
            'password' => 'vendeur123',
            'nom' => 'Vendeur',
            'prenom' => 'Test',
            'role' => 'vendeur'
        ]
    ];
    
    $userModel = new Utilisateur();
    $createdCount = 0;
    
    foreach ($testUsers as $userData) {
        $existingUser = $userModel->findByEmail($userData['email']);
        
        if ($existingUser) {
            echo "<p><span class='warning'>⚠️</span> Utilisateur {$userData['email']} existe déjà (ID: {$existingUser['id_user']})</p>";
            
            // Mettre à jour le mot de passe si nécessaire
            if (!password_verify($userData['password'], $existingUser['motdepasse'])) {
                $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
                $stmt = $db->getConnection()->prepare("UPDATE Utilisateur SET motdepasse = ? WHERE email = ?");
                if ($stmt->execute([$hashedPassword, $userData['email']])) {
                    echo "<p><span class='ok'>✅</span> Mot de passe mis à jour pour {$userData['email']}</p>";
                }
            }
        } else {
            echo "<p>Création de l'utilisateur {$userData['email']}...</p>";
            
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            $created = $userModel->create([
                'nom' => $userData['nom'],
                'prenom' => $userData['prenom'],
                'adresse' => '123 Test Street',
                'phone' => '0123456789',
                'email' => $userData['email'],
                'motdepasse' => $hashedPassword,
            ]);
            
            if ($created) {
                echo "<p><span class='ok'>✅</span> Utilisateur {$userData['email']} créé avec succès</p>";
                $createdCount++;
                
                // Récupérer l'utilisateur créé et créer le profil approprié
                $user = $userModel->findByEmail($userData['email']);
                if ($user) {
                    if ($userData['role'] === 'client') {
                        $clientModel = new Client();
                        $clientModel->create($user['id_user']);
                        echo "<p><span class='ok'>✅</span> Profil client créé</p>";
                    } elseif ($userData['role'] === 'vendeur') {
                        $vendeurModel = new Vendeur();
                        $vendeurModel->create($user['id_user'], [
                            'nom_entreprise' => 'Test Entreprise',
                            'siret' => '12345678901234',
                            'adresse_entreprise' => '123 Entreprise Street',
                            'email_pro' => $userData['email']
                        ]);
                        echo "<p><span class='ok'>✅</span> Profil vendeur créé</p>";
                    } elseif ($userData['role'] === 'admin') {
                        require_once 'models/Gestionnaire.php';
                        $gestModel = new Gestionnaire();
                        $stmt = $db->getConnection()->prepare("INSERT INTO Gestionnaire (id_user) VALUES (?)");
                        $stmt->execute([$user['id_user']]);
                        echo "<p><span class='ok'>✅</span> Profil administrateur créé</p>";
                    }
                }
            } else {
                echo "<p><span class='error'>❌</span> Erreur lors de la création de {$userData['email']}</p>";
            }
        }
    }
    
    echo "<h2>📋 Résumé</h2>";
    echo "<div class='info'>";
    echo "<p><strong>Utilisateurs créés/mis à jour : $createdCount</strong></p>";
    echo "<p>Vous pouvez maintenant tester la connexion avec les identifiants suivants :</p>";
    echo "<ul>";
    echo "<li><strong>Client :</strong> test@example.com / password123</li>";
    echo "<li><strong>Admin :</strong> admin@example.com / admin123</li>";
    echo "<li><strong>Vendeur :</strong> vendeur@example.com / vendeur123</li>";
    echo "</ul>";
    echo "<p><a href='index.php?controller=auth&action=login' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>🔐 Tester la connexion</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>Le serveur web local est démarré</li>";
    echo "<li>MySQL est en cours d'exécution</li>";
    echo "<li>La base de données 'vente_groupe' existe</li>";
    echo "<li>La table 'Utilisateur' existe</li>";
    echo "</ul>";
}
?>
