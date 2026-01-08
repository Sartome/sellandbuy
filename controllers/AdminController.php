<?php
/**
 * Contrôleur d'administration
 * 
 * Gère toutes les fonctionnalités administratives du marketplace :
 * - Tableau de bord principal
 * - Gestion des catégories
 * - Analyses et statistiques
 * - Debug système
 */

class AdminController {
    /**
     * Affiche le tableau de bord principal de l'administration
     * Vérifie les droits d'administrateur avant d'afficher la page
     */
    public function index() {
        requireAdmin();
        $pageTitle = 'Administration';
        require_once VIEWS_PATH . '/admin/index.php';
    }

    /**
     * Affiche les analyses et statistiques du marketplace
     * Récupère les données de ventes, utilisateurs et produits
     */
    public function analytics() {
        requireAdmin();
        require_once MODELS_PATH . '/Sale.php';
        require_once MODELS_PATH . '/Database.php';

        $sale = new Sale();
        $salesCount = $sale->countByDayLast30();
        $salesAmount = $sale->totalAmountByDayLast30();

        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT DATE(created_at) d, COUNT(*) c FROM Utilisateur WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY d ORDER BY d");
        $usersCount = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Analyses';
        require_once VIEWS_PATH . '/admin/analytics.php';
    }

    /**
     * Interface de debug système intégrée
     * Effectue des tests complets du système et affiche les résultats
     */
    public function debug() {
        requireAdmin();
        
        // Tests système
        $testResults = [];
        $errors = [];
        $warnings = [];
        $routeTests = [];
        
        // Fonction pour ajouter un résultat de test
        function addTestResult($category, $test, $status, $message = '') {
            global $testResults;
            $testResults[] = [
                'category' => $category,
                'test' => $test,
                'status' => $status,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
        
        // Test des extensions PHP
        $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo', 'session'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                addTestResult('Extensions PHP', "Extension $ext", 'success', 'Chargée');
            } else {
                addTestResult('Extensions PHP', "Extension $ext", 'error', 'Manquante');
            }
        }
        
        // Test de la base de données
        try {
            $pdo = Database::getInstance()->getConnection();
            addTestResult('Base de données', 'Connexion PDO', 'success', 'Connexion réussie');
            
            // Test des tables
            $tables = ['Utilisateur', 'Produit', 'Categorie', 'Client', 'Vendeur', 'Gestionnaire'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    addTestResult('Base de données', "Table $table", 'success', "$count enregistrement(s)");
                } catch (Exception $e) {
                    addTestResult('Base de données', "Table $table", 'error', $e->getMessage());
                }
            }
        } catch (Exception $e) {
            addTestResult('Base de données', 'Connexion PDO', 'error', $e->getMessage());
        }
        
        // Test des catégories spécifiquement
        try {
            require_once MODELS_PATH . '/Categorie.php';
            $categorie = new Categorie();
            $categories = $categorie->getAll();
            addTestResult('Catégories', 'Récupération des catégories', 'success', count($categories) . ' catégorie(s) trouvée(s)');
            
            if (empty($categories)) {
                addTestResult('Catégories', 'Création catégorie par défaut', 'warning', 'Aucune catégorie trouvée, création de la catégorie par défaut');
                $defaultId = $categorie->ensureDefaultAcquisition();
                addTestResult('Catégories', 'Catégorie par défaut créée', 'success', "ID: $defaultId");
            }
        } catch (Exception $e) {
            addTestResult('Catégories', 'Gestion des catégories', 'error', $e->getMessage());
        }
        
        // Test des dossiers
        $directories = [
            'public/images/uploads' => 'Dossier d\'upload d\'images',
            'public/css' => 'Dossier CSS',
            'public/js' => 'Dossier JavaScript'
        ];
        
        foreach ($directories as $dir => $description) {
            if (is_dir($dir)) {
                $writable = is_writable($dir);
                $status = $writable ? 'success' : 'warning';
                $message = $writable ? 'Existe et accessible en écriture' : 'Existe mais non accessible en écriture';
                addTestResult('Système de fichiers', $description, $status, $message);
            } else {
                addTestResult('Système de fichiers', $description, 'error', 'Dossier manquant');
            }
        }

        // Test des routes principales (pages clés du site)
        $routesToTest = [
            [
                'name' => "Page d'accueil produits",
                'url' => BASE_URL . '/index.php?controller=product&action=index',
            ],
            [
                'name' => 'Page de connexion',
                'url' => BASE_URL . '/index.php?controller=auth&action=login',
            ],
            [
                'name' => 'Mes acquisitions',
                'url' => BASE_URL . '/index.php?controller=acquisition&action=index',
            ],
            [
                'name' => 'Tableau de bord admin',
                'url' => BASE_URL . '/index.php?controller=admin&action=index',
            ],
            [
                'name' => 'Gestion des annonces (admin)',
                'url' => BASE_URL . '/index.php?controller=admin&action=ads',
            ],
            [
                'name' => 'Gestion des vendeurs (admin)',
                'url' => BASE_URL . '/index.php?controller=admin&action=vendors',
            ],
            [
                'name' => 'Paramètres du site (admin)',
                'url' => BASE_URL . '/index.php?controller=admin&action=settings',
            ],
        ];

        $sessionId = session_id();
        $sessionName = session_name();
        $headers = [];
        if ($sessionId && $sessionName) {
            $headers[] = 'Cookie: ' . $sessionName . '=' . $sessionId;
        }

        $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN);
        $hasCurl = function_exists('curl_init');
        $httpTestingAvailable = $hasCurl || $allowUrlFopen;

        foreach ($routesToTest as $route) {
            if (!$httpTestingAvailable) {
                // Impossible de tester les routes HTTP sur ce serveur
                $routeTests[] = [
                    'name' => $route['name'],
                    'url' => $route['url'],
                    'status_code' => null,
                    'ok' => null,
                    'duration_ms' => null,
                    'skipped' => true,
                    'error_snippet' => null,
                ];
                continue;
            }

            $start = microtime(true);
            $statusCode = null;
            $content = false;

            if ($hasCurl) {
                // Utiliser cURL si disponible (plus fiable)
                $ch = curl_init($route['url']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                if (!empty($headers)) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }
                $content = curl_exec($ch);
                if ($content !== false) {
                    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                }
                curl_close($ch);
            } elseif ($allowUrlFopen) {
                // Fallback sur file_get_contents
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => implode("\r\n", $headers),
                        'ignore_errors' => true,
                        'timeout' => 3,
                    ],
                ]);
                $content = @file_get_contents($route['url'], false, $context);
                if (isset($http_response_header[0]) && preg_match('#HTTP/\\S+\\s(\\d{3})#', $http_response_header[0], $matches)) {
                    $statusCode = (int) $matches[1];
                }
            }

            $durationMs = (int) ((microtime(true) - $start) * 1000);
            $ok = $content !== false && $statusCode !== null && $statusCode < 400;

            // Extraire un court message d'erreur PHP éventuel
            $errorSnippet = null;
            if (!$ok && is_string($content) && $content !== '') {
                $plain = trim(strip_tags($content));
                if ($plain !== '') {
                    if (function_exists('mb_substr')) {
                        $errorSnippet = mb_substr($plain, 0, 300);
                    } else {
                        $errorSnippet = substr($plain, 0, 300);
                    }
                }
            }

            $routeTests[] = [
                'name' => $route['name'],
                'url' => $route['url'],
                'status_code' => $statusCode,
                'ok' => $ok,
                'duration_ms' => $durationMs,
                'skipped' => false,
                'error_snippet' => $errorSnippet,
            ];
        }

        $pageTitle = 'Debug Système';
        require_once VIEWS_PATH . '/admin/debug.php';
    }

    /**
     * Gestion des catégories de produits
     * Permet de créer, modifier et supprimer des catégories
     */
    public function categories() {
        requireAdmin();
        
        // Traitement des actions sur les catégories
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Categorie.php';
            $categorie = new Categorie();
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'create':
                        $libelle = sanitize($_POST['libelle'] ?? '');
                        if (!empty($libelle)) {
                            try {
                                $pdo = Database::getInstance()->getConnection();
                                $stmt = $pdo->prepare("INSERT INTO Categorie (id_gestionnaire, lib) VALUES (NULL, ?)");
                                $stmt->execute([$libelle]);
                                $message = "Catégorie '$libelle' créée avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la création : " . $e->getMessage();
                            }
                        } else {
                            $error = "Le libellé de la catégorie est requis";
                        }
                        break;
                        
                    case 'delete':
                        $id = (int)($_POST['id'] ?? 0);
                        if ($id > 0) {
                            try {
                                $pdo = Database::getInstance()->getConnection();
                                $stmt = $pdo->prepare("DELETE FROM Categorie WHERE id_categorie = ?");
                                $stmt->execute([$id]);
                                $message = "Catégorie supprimée avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la suppression : " . $e->getMessage();
                            }
                        }
                        break;
                }
            }
        }
        
        // Récupérer toutes les catégories
        require_once MODELS_PATH . '/Categorie.php';
        $categorie = new Categorie();
        $categories = $categorie->getAll();
        
        $pageTitle = 'Gestion des Catégories';
        require_once VIEWS_PATH . '/admin/categories.php';
    }

    /**
     * Gestion des vendeurs et certification
     * Permet aux administrateurs de certifier/décertifier les vendeurs
     */
    public function vendors() {
        requireAdmin();
        
        // Traitement des actions sur les vendeurs et utilisateurs
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Vendeur.php';
            require_once MODELS_PATH . '/Utilisateur.php';
            $vendeur = new Vendeur();
            $utilisateur = new Utilisateur();
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'certify':
                        $vendorId = (int)($_POST['vendor_id'] ?? 0);
                        if ($vendorId > 0) {
                            try {
                                $vendeur->setCertification($vendorId, true);
                                $message = "Vendeur certifié avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la certification : " . $e->getMessage();
                            }
                        }
                        break;
                        
                    case 'uncertify':
                        $vendorId = (int)($_POST['vendor_id'] ?? 0);
                        if ($vendorId > 0) {
                            try {
                                $vendeur->setCertification($vendorId, false);
                                $message = "Certification retirée avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la décertification : " . $e->getMessage();
                            }
                        }
                        break;
                        
                    case 'delete_vendor':
                        $vendorId = (int)($_POST['vendor_id'] ?? 0);
                        if ($vendorId > 0) {
                            try {
                                $vendeur->delete($vendorId);
                                $message = "Vendeur supprimé avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la suppression : " . $e->getMessage();
                            }
                        }
                        break;
                        
                    case 'delete_user':
                        $userId = (int)($_POST['user_id'] ?? 0);
                        if ($userId > 0) {
                            try {
                                $utilisateur->delete($userId);
                                $message = "Utilisateur supprimé avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la suppression : " . $e->getMessage();
                            }
                        }
                        break;
                }
            }
        }
        
        // Récupérer les données
        require_once MODELS_PATH . '/Vendeur.php';
        require_once MODELS_PATH . '/Utilisateur.php';
        $vendeur = new Vendeur();
        $utilisateur = new Utilisateur();
        
        // Gestion de la recherche
        $search = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? 'all'; // all, vendors, users
        
        if (!empty($search)) {
            if ($type === 'vendors' || $type === 'all') {
                $vendors = $vendeur->searchVendors($search);
            } else {
                $vendors = [];
            }
            
            if ($type === 'users' || $type === 'all') {
                $users = $utilisateur->searchUsers($search);
            } else {
                $users = [];
            }
        } else {
            $vendors = $vendeur->getAllVendors();
            $users = $utilisateur->getAllUsers();
        }
        
        // Debug: vérifier que les données sont bien des tableaux
        if (!is_array($vendors)) {
            $vendors = [];
        }
        if (!is_array($users)) {
            $users = [];
        }
        
        $pageTitle = 'Gestion des Vendeurs et Utilisateurs';
        require_once VIEWS_PATH . '/admin/vendors.php';
    }

    /**
     * Gestion des annonces par les administrateurs
     * Permet aux administrateurs de modifier et gérer toutes les annonces
     */
    public function ads() {
        requireAdmin();
        
        // Traitement des actions sur les annonces
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Produit.php';
            $produit = new Produit();
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update':
                        $productId = (int)($_POST['product_id'] ?? 0);
                        if ($productId > 0) {
                            try {
                                $data = [
                                    'description' => sanitize($_POST['description'] ?? ''),
                                    'prix' => (float)($_POST['prix'] ?? 0),
                                    'id_categorie' => (int)($_POST['id_categorie'] ?? 0)
                                ];
                                $produit->update($productId, $data);
                                $message = "Annonce mise à jour avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la mise à jour : " . $e->getMessage();
                            }
                        }
                        break;
                        
                    case 'delete':
                        $productId = (int)($_POST['product_id'] ?? 0);
                        if ($productId > 0) {
                            try {
                                $produit->delete($productId);
                                $message = "Annonce supprimée avec succès";
                            } catch (Exception $e) {
                                $error = "Erreur lors de la suppression : " . $e->getMessage();
                            }
                        }
                        break;
                }
            }
        }
        
        // Récupérer les annonces avec recherche
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/Categorie.php';
        $produit = new Produit();
        $categorie = new Categorie();
        
        // Gestion de la recherche
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';
        $priceMin = $_GET['price_min'] ?? '';
        $priceMax = $_GET['price_max'] ?? '';
        
        if (!empty($search) || !empty($categoryFilter) || !empty($priceMin) || !empty($priceMax)) {
            $products = $produit->searchProducts($search, $categoryFilter, $priceMin, $priceMax);
        } else {
            $products = $produit->getAll();
        }
        
        $categories = $categorie->getAll();
        
        $pageTitle = 'Gestion des Annonces';
        require_once VIEWS_PATH . '/admin/ads.php';
    }

    /**
     * Création rapide d'un utilisateur par un administrateur
     * Ne modifie pas la session courante (l'admin reste connecté)
     */
    public function createUser() {
        requireAdmin();

        $error = '';

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

            // Validations de base (reprennent la logique d'inscription)
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

                        // Ne pas connecter le nouvel utilisateur, rester sur l'admin
                        redirect('/index.php?controller=admin&action=vendors', 'Utilisateur créé avec succès');
                    } else {
                        $error = "Erreur lors de la création de l'utilisateur";
                    }
                }
            }
        }

        $pageTitle = 'Créer un utilisateur';
        require_once VIEWS_PATH . '/admin/create_user.php';
    }

    /**
     * Gestion des taxes
     */
    public function settings() {
        requireAdmin();
        
        require_once MODELS_PATH . '/SiteSettings.php';
        $settings = new SiteSettings();
        
        // Traitement des modifications
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'update_taxes':
                        $taxRate = (float)($_POST['tax_rate'] ?? 0);
                        $taxEnabled = isset($_POST['tax_enabled']) ? 1 : 0;
                        $taxName = sanitize($_POST['tax_name'] ?? 'TVA');
                        
                        $settings->set('tax_rate', $taxRate, 'Taux de taxe en pourcentage');
                        $settings->set('tax_enabled', $taxEnabled, 'Activer/désactiver les taxes');
                        $settings->set('tax_name', $taxName, 'Nom de la taxe');
                        
                        $message = "Paramètres de taxes mis à jour avec succès";
                        break;
                        
                    case 'add_tax':
                        $taxName = sanitize($_POST['tax_name'] ?? '');
                        $taxRate = (float)($_POST['tax_rate'] ?? 0);
                        $taxDescription = sanitize($_POST['tax_description'] ?? '');
                        
                        if (!empty($taxName) && $taxRate > 0) {
                            $taxId = $settings->addTax($taxName, $taxRate, $taxDescription);
                            if ($taxId) {
                                $message = "Taxe ajoutée avec succès";
                            } else {
                                $error = "Erreur lors de l'ajout de la taxe";
                            }
                        } else {
                            $error = "Veuillez remplir tous les champs obligatoires";
                        }
                        break;
                        
                    case 'delete_tax':
                        $taxId = (int)($_POST['tax_id'] ?? 0);
                        if ($taxId > 0) {
                            if ($settings->deleteTax($taxId)) {
                                $message = "Taxe supprimée avec succès";
                            } else {
                                $error = "Erreur lors de la suppression de la taxe";
                            }
                        }
                        break;
                }
            }
        }
        
        // Récupérer les paramètres actuels
        $currentSettings = [
            'tax_rate' => $settings->getTaxRate(),
            'tax_enabled' => $settings->isTaxEnabled(),
            'tax_name' => $settings->getTaxName()
        ];
        
        // Récupérer toutes les taxes
        $allTaxes = $settings->getAllTaxes();
        
        $pageTitle = 'Gestion des Taxes';
        require_once VIEWS_PATH . '/admin/settings.php';
    }

    /**
     * Centre de tickets pour les administrateurs
     * Permet de voir et traiter les tickets envoyés par les utilisateurs/vendeurs
     */
    public function tickets() {
        requireAdmin();

        require_once MODELS_PATH . '/Ticket.php';
        $ticketModel = new Ticket();

        $message = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ticketId = (int)($_POST['ticket_id'] ?? 0);
            $action = $_POST['action'] ?? '';

            if ($ticketId <= 0) {
                $error = 'Ticket invalide';
            } else {
                if ($action === 'answer') {
                    $adminResponse = trim(sanitize($_POST['admin_response'] ?? ''));
                    if ($adminResponse === '') {
                        $error = 'La réponse ne peut pas être vide';
                    } else {
                        if ($ticketModel->answer($ticketId, $adminResponse)) {
                            $message = 'Réponse envoyée';
                        } else {
                            $error = 'Erreur lors de l\'enregistrement de la réponse';
                        }
                    }
                } elseif ($action === 'close') {
                    if ($ticketModel->close($ticketId)) {
                        $message = 'Ticket fermé';
                    } else {
                        $error = 'Erreur lors de la fermeture du ticket';
                    }
                }
            }
        }

        $tickets = $ticketModel->getAll();
        $pageTitle = 'Tickets support';
        require_once VIEWS_PATH . '/admin/tickets.php';
    }

    /**
     * Liste des factures (admin)
     */
    public function invoices() {
        requireAdmin();
        require_once MODELS_PATH . '/Facture.php';
        $facture = new Facture();

        // Handle simple delete action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_facture'])) {
            $id = (int)($_POST['id_facture'] ?? 0);
            if ($id > 0 && $facture->delete($id)) {
                redirect('/index.php?controller=admin&action=invoices', 'Facture supprimée');
            }
            redirect('/index.php?controller=admin&action=invoices', 'Erreur lors de la suppression', 'error');
        }

        $invoices = $facture->getAll();
        $pageTitle = 'Factures';
        require_once VIEWS_PATH . '/admin/invoices.php';
    }

    /**
     * Liste des signalements (admin)
     */
    public function signals() {
        requireAdmin();
        require_once MODELS_PATH . '/Signaler.php';
        $signaler = new Signaler();
        $signals = $signaler->getAll();
        $pageTitle = 'Signalements';
        require_once VIEWS_PATH . '/admin/signals.php';
    }

    /**
     * Générer et afficher le diagramme ER (admin)
     */
    public function erDiagram() {
        requireAdmin();
        require_once HELPERS_PATH . '/ERDiagram.php';
        $er = new ERDiagram();
        $svg = $er->generateSvg();

        // Info on saved file if exists
        $savedPath = ROOT_PATH . '/database/er_diagram.svg';
        $savedInfo = null;
        if (file_exists($savedPath)) {
            $savedInfo = [
                'path' => $savedPath,
                'mtime' => date('Y-m-d H:i:s', filemtime($savedPath)),
                'size' => filesize($savedPath)
            ];
        }

        $pageTitle = 'ER Diagram';
        require_once VIEWS_PATH . '/admin/er_diagram.php';
    }

    /**
     * Enregistrer le diagramme ER en SVG dans le dossier `database/`
     */
    public function saveDiagram() {
        requireAdmin();
        require_once HELPERS_PATH . '/ERDiagram.php';

        $er = new ERDiagram();
        $svg = $er->generateSvg();
        $savedPath = ROOT_PATH . '/database/er_diagram.svg';

        try {
            if (!is_dir(dirname($savedPath))) {
                mkdir(dirname($savedPath), 0755, true);
            }

            $bytes = file_put_contents($savedPath, $svg);
            if ($bytes === false) {
                redirect('/index.php?controller=admin&action=erDiagram', 'Impossible d\'écrire le fichier', 'error');
            }

            redirect('/index.php?controller=admin&action=erDiagram', 'Diagramme enregistré dans ' . $savedPath);
        } catch (Exception $e) {
            redirect('/index.php?controller=admin&action=erDiagram', 'Erreur lors de la sauvegarde: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Télécharger le diagramme ER en SVG (préférer le fichier sauvegardé si présent)
     */
    public function downloadDiagram() {
        requireAdmin();
        $savedPath = ROOT_PATH . '/database/er_diagram.svg';
        if (file_exists($savedPath)) {
            header('Content-Type: image/svg+xml');
            header('Content-Disposition: attachment; filename="er_diagram.svg"');
            readfile($savedPath);
            exit;
        }

        require_once HELPERS_PATH . '/ERDiagram.php';
        $er = new ERDiagram();
        $svg = $er->generateSvg();
        header('Content-Type: image/svg+xml');
        header('Content-Disposition: attachment; filename="er_diagram.svg"');
        echo $svg;
        exit;
    }

    /**
     * Bloquer un vendeur (action admin)
     */
    public function blockVendor() {
        requireAdmin();
        $vendorId = (int)($_GET['vendor_id'] ?? 0);
        if ($vendorId <= 0) {
            redirect('/index.php?controller=admin&action=vendors', 'Vendeur invalide', 'error');
        }

        require_once MODELS_PATH . '/Bloquer.php';
        $bloquer = new Bloquer();
        if ($bloquer->exists($vendorId)) {
            redirect('/index.php?controller=admin&action=vendors', 'Vendeur déjà bloqué', 'warning');
        }

        if ($bloquer->create((int)$_SESSION['user_id'], $vendorId)) {
            redirect('/index.php?controller=admin&action=vendors', 'Vendeur bloqué avec succès');
        }

        redirect('/index.php?controller=admin&action=vendors', 'Erreur lors du blocage', 'error');
    }

    /**
     * Débloquer un vendeur (action admin)
     */
    public function unblockVendor() {
        requireAdmin();
        $vendorId = (int)($_GET['vendor_id'] ?? 0);
        if ($vendorId <= 0) {
            redirect('/index.php?controller=admin&action=vendors', 'Vendeur invalide', 'error');
        }

        require_once MODELS_PATH . '/Debloquer.php';
        require_once MODELS_PATH . '/Bloquer.php';
        $debloquer = new Debloquer();
        $bloquer = new Bloquer();

        $debloquer->create((int)$_SESSION['user_id'], $vendorId);
        $bloquer->removeByVendor($vendorId);

        redirect('/index.php?controller=admin&action=vendors', 'Vendeur débloqué');
    }

    /**
     * Supprimer un signalement
     */
    public function deleteSignal() {
        requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect('/index.php?controller=admin&action=signals', 'Signalement invalide', 'error');
        }

        require_once MODELS_PATH . '/Signaler.php';
        $signaler = new Signaler();
        if ($signaler->delete($id)) {
            redirect('/index.php?controller=admin&action=signals', 'Signalement supprimé');
        }
        redirect('/index.php?controller=admin&action=signals', 'Erreur lors de la suppression', 'error');
    }
}



