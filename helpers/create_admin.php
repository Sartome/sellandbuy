<?php
// helpers/create_admin.php
// Script pour créer un compte administrateur

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Gestionnaire.php';

// Configuration par défaut pour l'admin
$adminData = [
    'nom' => 'Admin',
    'prenom' => 'Super',
    'adresse' => '123 Admin Street',
    'phone' => '0123456789',
    'email' => 'admin@sellandbuy.com',
    'password' => 'admin123', // Changez ce mot de passe !
    'role' => 'admin'
];

function createAdminAccount($data) {
    try {
        $utilisateur = new Utilisateur();
        
        // Vérifier si l'admin existe déjà
        $existingAdmin = $utilisateur->findByEmail($data['email']);
        if ($existingAdmin) {
            echo "❌ Un compte admin existe déjà avec cet email.\n";
            return false;
        }
        
        // Créer le compte utilisateur
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        $userCreated = $utilisateur->create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'adresse' => $data['adresse'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'motdepasse' => $hashedPassword,
        ]);
        
        if (!$userCreated) {
            echo "❌ Erreur lors de la création du compte utilisateur.\n";
            return false;
        }
        
        // Récupérer l'ID de l'utilisateur créé
        $user = $utilisateur->findByEmail($data['email']);
        $userId = (int)$user['id_user'];
        
        // Ajouter les privilèges administrateur
        $gestionnaire = new Gestionnaire();
        $adminCreated = $gestionnaire->create($userId);
        
        if (!$adminCreated) {
            echo "❌ Erreur lors de l'ajout des privilèges admin.\n";
            return false;
        }
        
        echo "✅ Compte administrateur créé avec succès !\n";
        echo "📧 Email: " . $data['email'] . "\n";
        echo "🔑 Mot de passe: " . $data['password'] . "\n";
        echo "⚠️  IMPORTANT: Changez le mot de passe après la première connexion !\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        return false;
    }
}

// Méthode alternative : Ajouter des privilèges admin à un utilisateur existant
function makeUserAdmin($email) {
    try {
        $utilisateur = new Utilisateur();
        $user = $utilisateur->findByEmail($email);
        
        if (!$user) {
            echo "❌ Utilisateur non trouvé avec l'email: " . $email . "\n";
            return false;
        }
        
        $gestionnaire = new Gestionnaire();
        $isAlreadyAdmin = $gestionnaire->isAdminUser((int)$user['id_user']);
        
        if ($isAlreadyAdmin) {
            echo "❌ Cet utilisateur est déjà administrateur.\n";
            return false;
        }
        
        $adminCreated = $gestionnaire->create((int)$user['id_user']);
        
        if ($adminCreated) {
            echo "✅ Privilèges administrateur ajoutés à: " . $email . "\n";
            return true;
        } else {
            echo "❌ Erreur lors de l'ajout des privilèges admin.\n";
            return false;
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        return false;
    }
}

// Méthode pour lister tous les administrateurs
function listAdmins() {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT u.id_user, u.nom, u.prenom, u.email, u.created_at
            FROM Utilisateur u
            INNER JOIN Gestionnaire g ON u.id_user = g.id_user
            ORDER BY u.created_at DESC
        ");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($admins)) {
            echo "❌ Aucun administrateur trouvé.\n";
            return;
        }
        
        echo "👥 Liste des administrateurs:\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($admins as $admin) {
            echo "ID: " . $admin['id_user'] . " | ";
            echo "Nom: " . $admin['prenom'] . " " . $admin['nom'] . " | ";
            echo "Email: " . $admin['email'] . " | ";
            echo "Créé: " . $admin['created_at'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
    }
}

// Interface en ligne de commande
if (php_sapi_name() === 'cli') {
    echo "🛒 Sell & Buy - Gestion des administrateurs\n";
    echo str_repeat("=", 50) . "\n\n";
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'create':
                createAdminAccount($adminData);
                break;
                
            case 'make-admin':
                if (isset($argv[2])) {
                    makeUserAdmin($argv[2]);
                } else {
                    echo "❌ Usage: php create_admin.php make-admin email@example.com\n";
                }
                break;
                
            case 'list':
                listAdmins();
                break;
                
            case 'help':
            default:
                echo "📖 Commandes disponibles:\n";
                echo "  create        - Créer un compte admin par défaut\n";
                echo "  make-admin    - Donner les privilèges admin à un utilisateur existant\n";
                echo "  list          - Lister tous les administrateurs\n";
                echo "  help          - Afficher cette aide\n\n";
                echo "Exemples:\n";
                echo "  php create_admin.php create\n";
                echo "  php create_admin.php make-admin user@example.com\n";
                echo "  php create_admin.php list\n";
                break;
        }
    } else {
        echo "❌ Aucune commande spécifiée. Utilisez 'help' pour voir les options.\n";
    }
} else {
    // Interface web simple
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Créer un compte Admin</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .btn { background: #7c3aed; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; margin: 10px 5px; }
            .btn:hover { background: #6d28d9; }
            .success { color: #10b981; font-weight: bold; }
            .error { color: #ef4444; font-weight: bold; }
            .warning { color: #f59e0b; font-weight: bold; }
            .form-group { margin: 15px 0; }
            .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
            .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🛒 Créer un compte Administrateur</h1>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (isset($_POST['action'])) {
                    switch ($_POST['action']) {
                        case 'create':
                            $result = createAdminAccount($adminData);
                            break;
                            
                        case 'make_admin':
                            if (!empty($_POST['email'])) {
                                $result = makeUserAdmin($_POST['email']);
                            } else {
                                echo "<div class='error'>❌ Veuillez entrer un email.</div>";
                            }
                            break;
                            
                        case 'list':
                            echo "<h3>👥 Liste des administrateurs:</h3>";
                            listAdmins();
                            break;
                    }
                }
            }
            ?>
            
            <form method="post">
                <h3>Options disponibles:</h3>
                
                <div class="form-group">
                    <button type="submit" name="action" value="create" class="btn">
                        🚀 Créer un compte admin par défaut
                    </button>
                    <p><small>Crée un compte admin avec les informations par défaut</small></p>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label for="email">Email de l'utilisateur à promouvoir:</label>
                    <input type="email" id="email" name="email" placeholder="user@example.com">
                    <button type="submit" name="action" value="make_admin" class="btn">
                        👑 Donner les privilèges admin
                    </button>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <button type="submit" name="action" value="list" class="btn">
                        📋 Lister les administrateurs
                    </button>
                </div>
            </form>
            
            <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 6px;">
                <h4>📖 Instructions:</h4>
                <ol>
                    <li><strong>Créer un admin par défaut:</strong> Cliquez sur le premier bouton pour créer un compte admin avec les informations par défaut.</li>
                    <li><strong>Promouvoir un utilisateur:</strong> Entrez l'email d'un utilisateur existant et cliquez sur "Donner les privilèges admin".</li>
                    <li><strong>Lister les admins:</strong> Cliquez sur "Lister les administrateurs" pour voir tous les comptes admin.</li>
                </ol>
                
                <div class="warning">
                    <strong>⚠️ Informations par défaut:</strong><br>
                    Email: admin@sellandbuy.com<br>
                    Mot de passe: admin123<br>
                    <strong>Changez le mot de passe après la première connexion !</strong>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>
