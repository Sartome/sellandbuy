<?php
// controllers/AuthController.php

class AuthController {
    
    public function login() {
        // Debug: Log de l'appel de la méthode
        error_log("AuthController::login() called - Method: " . $_SERVER['REQUEST_METHOD']);
        
        $error = ''; // Initialiser la variable d'erreur
        
        // Logique de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST request received");
            error_log("POST data: " . print_r($_POST, true));
            
            require_once MODELS_PATH . '/Utilisateur.php';
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Debug: Log des données reçues
            error_log("Login attempt - Email: " . $email);
            error_log("Login attempt - Password length: " . strlen($password));

            $userModel = new Utilisateur();
            $user = $userModel->findByEmail($email);

            // Debug: Log du résultat de la recherche utilisateur
            if ($user) {
                error_log("User found - ID: " . $user['id_user'] . ", Email: " . $user['email']);
                error_log("Stored password hash: " . substr($user['motdepasse'], 0, 20) . "...");
            } else {
                error_log("No user found with email: " . $email);
            }

            // Validation des champs
            if (empty($email) || empty($password)) {
                $error = 'Veuillez remplir tous les champs';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Adresse email invalide';
            } elseif (!$user) {
                $error = 'Aucun compte trouvé avec cette adresse email';
            } elseif (!password_verify($password, $user['motdepasse'])) {
                $error = 'Mot de passe incorrect';
            } else {
                // Connexion réussie
                error_log("Password verification successful");
                
                // Créer la session
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email'];
                $_SESSION['avatar'] = $user['avatar'] ?? null;
                
                error_log("Session variables set: user_id=" . $_SESSION['user_id'] . ", email=" . $_SESSION['email']);
                
                // Déterminer si admin (Gestionnaire)
                require_once MODELS_PATH . '/Gestionnaire.php';
                $gest = new Gestionnaire();
                $_SESSION['is_admin'] = $gest->isAdminUser((int)$user['id_user']);
                
                error_log("Admin check completed: " . ($_SESSION['is_admin'] ? 'true' : 'false'));
                error_log("Session data before redirect: " . print_r($_SESSION, true));
                
                error_log("About to redirect to: " . BASE_URL . "/index.php?controller=product&action=index");
                redirect('/index.php?controller=product&action=index', 'Connexion réussie');
            }
        }
        require_once VIEWS_PATH . '/auth/login.php';
    }
    
    public function register() {
        // Logique d'inscription
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Utilisateur.php';
            require_once MODELS_PATH . '/Client.php';
            require_once MODELS_PATH . '/Vendeur.php';

            $nom = sanitize($_POST['nom'] ?? '');
            $prenom = sanitize($_POST['prenom'] ?? '');
            $adresse = sanitize($_POST['adresse'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $role = sanitize($_POST['role'] ?? 'client'); // client | vendeur

            // Validation des champs
            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = 'Veuillez remplir tous les champs obligatoires';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Adresse email invalide';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas';
            } elseif (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères';
            } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d)/', $password)) {
                $error = 'Le mot de passe doit contenir au moins une lettre et un chiffre';
            } else {
                $utilisateur = new Utilisateur();
                $exists = $utilisateur->findByEmail($email);
                if ($exists) {
                    $error = 'Un compte existe déjà avec cet email';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $created = $utilisateur->create([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'adresse' => $adresse,
                        'phone' => $phone,
                        'email' => $email,
                        'motdepasse' => $hashed,
                    ]);

                    if ($created) {
                        // Récupérer l'utilisateur créé
                        $user = $utilisateur->findByEmail($email);
                        $userId = (int)$user['id_user'];

                        if ($role === 'vendeur') {
                            $vendeur = new Vendeur();
                            $vendeur->create($userId, [
                                'nom_entreprise' => sanitize($_POST['nom_entreprise'] ?? ''),
                                'siret' => sanitize($_POST['siret'] ?? ''),
                                'adresse_entreprise' => sanitize($_POST['adresse_entreprise'] ?? ''),
                                'email_pro' => sanitize($_POST['email_pro'] ?? ''),
                            ]);
                        } else {
                            $client = new Client();
                            $client->create($userId);
                        }

                        $_SESSION['user_id'] = $userId;
                        $_SESSION['email'] = $email;
                        $_SESSION['username'] = trim($prenom . ' ' . $nom) ?: $email;
                        redirect('/index.php?controller=product&action=index', 'Inscription réussie');
                    } else {
                        $error = "Erreur lors de l'inscription";
                    }
                }
            }
        }
        require_once VIEWS_PATH . '/auth/register.php';
    }

    public function account() {
        requireLogin();
        require_once MODELS_PATH . '/Utilisateur.php';
        $userModel = new Utilisateur();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $user = $userModel->findById($userId);
        if (!$user) {
            redirect('/index.php?controller=auth&action=login', 'Utilisateur introuvable', 'error');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = sanitize($_POST['nom'] ?? '');
            $prenom = sanitize($_POST['prenom'] ?? '');
            $adresse = sanitize($_POST['adresse'] ?? '');
            $phone = sanitize($_POST['phone'] ?? '');
            $deleteAvatar = !empty($_POST['delete_avatar']);

            $avatarPath = null;
            if (!empty($_FILES['avatar']['name'] ?? '')) {
                require_once HELPERS_PATH . '/ImageUpload.php';
                $uploader = new ImageUpload();
                $uploadResult = $uploader->uploadAvatar($_FILES['avatar']);
                if (!empty($uploadResult['success'])) {
                    $avatarPath = $uploadResult['webPath'];
                } else {
                    redirect('/index.php?controller=auth&action=account', 'Erreur lors de l\'upload de la photo de profil', 'error');
                }
            }

            $updateData = [
                'nom' => $nom,
                'prenom' => $prenom,
                'adresse' => $adresse,
                'phone' => $phone,
            ];

            // Gestion de l'avatar : nouveau fichier, ou suppression demandée
            if ($avatarPath !== null) {
                $updateData['avatar'] = $avatarPath;
            } elseif ($deleteAvatar) {
                // Supprimer l'ancien fichier si présent
                if (!empty($user['avatar'])) {
                    require_once HELPERS_PATH . '/ImageUpload.php';
                    $uploader = new ImageUpload();
                    $filename = basename($user['avatar']);
                    $uploader->deleteImage($filename);
                }
                $updateData['avatar'] = '';
            }

            $userModel->updateProfile($userId, $updateData);
            $_SESSION['username'] = trim($prenom . ' ' . $nom) ?: ($user['email'] ?? '');
            if ($avatarPath !== null) {
                $_SESSION['avatar'] = $avatarPath;
            } elseif ($deleteAvatar) {
                $_SESSION['avatar'] = null;
            }
            redirect('/index.php?controller=auth&action=account', 'Profil mis à jour');
        }
        $pageTitle = 'Mon compte';
        require_once VIEWS_PATH . '/auth/account.php';
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?controller=auth&action=login');
        exit;
    }
}
