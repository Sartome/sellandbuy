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
     * Gestion des paramètres du site (taxes, etc.)
     */
    public function settings() {
        requireAdmin();
        
        require_once MODELS_PATH . '/SiteSettings.php';
        $settings = new SiteSettings();
        
        // Traitement des modifications
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'update_taxes') {
                $taxRate = (float)($_POST['tax_rate'] ?? 0);
                $taxEnabled = isset($_POST['tax_enabled']) ? 1 : 0;
                $taxName = sanitize($_POST['tax_name'] ?? 'TVA');
                
                $settings->set('tax_rate', $taxRate, 'Taux de taxe en pourcentage');
                $settings->set('tax_enabled', $taxEnabled, 'Activer/désactiver les taxes');
                $settings->set('tax_name', $taxName, 'Nom de la taxe');
                
                $message = "Paramètres de taxes mis à jour avec succès";
            }
        }
        
        // Récupérer les paramètres actuels
        $currentSettings = [
            'tax_rate' => $settings->getTaxRate(),
            'tax_enabled' => $settings->isTaxEnabled(),
            'tax_name' => $settings->getTaxName()
        ];
        
        $pageTitle = 'Paramètres du Site';
        require_once VIEWS_PATH . '/admin/settings.php';
    }
}


