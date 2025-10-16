<?php
// controllers/AuthController.php

class AuthController {
    
    public function login() {
        // Logique de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Utilisateur.php';
            $email = sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';

            $userModel = new Utilisateur();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['motdepasse'])) {
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['username'] = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?: $user['email'];
                // Déterminer si admin (Gestionnaire)
                require_once MODELS_PATH . '/Gestionnaire.php';
                $gest = new Gestionnaire();
                $_SESSION['is_admin'] = $gest->isAdminUser((int)$user['id_user']);
                redirect('/index.php?controller=product&action=index', 'Connexion réussie');
            } else {
                $error = 'Identifiants invalides';
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
            $role = sanitize($_POST['role'] ?? 'client'); // client | vendeur

            if (!$email || !$password) {
                $error = 'Email et mot de passe requis';
            } elseif ($password !== $passwordConfirm) {
                $error = 'Les mots de passe ne correspondent pas';
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
    
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/index.php?controller=auth&action=login');
        exit;
    }
}
