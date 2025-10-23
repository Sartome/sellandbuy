<?php
// controllers/AcquisitionController.php

class AcquisitionController {
    
    /**
     * Affiche les acquisitions de l'utilisateur connecté
     */
    public function index() {
        requireLogin();
        
        $userId = (int)$_SESSION['user_id'];
        
        // Récupérer les précommandes de l'utilisateur
        require_once MODELS_PATH . '/PrePurchase.php';
        $prePurchaseModel = new PrePurchase();
        $prePurchases = $prePurchaseModel->getByUser($userId);
        
        // Récupérer les enchères de l'utilisateur
        require_once MODELS_PATH . '/Auction.php';
        $auctionModel = new Auction();
        $userBids = $auctionModel->getUserBids($userId);
        
        // Récupérer les ventes de l'utilisateur (si vendeur)
        require_once MODELS_PATH . '/Vendeur.php';
        $vendeurModel = new Vendeur();
        $isVendor = $vendeurModel->findByUserId($userId) !== false;
        
        $userSales = [];
        if ($isVendor) {
            require_once MODELS_PATH . '/Produit.php';
            $productModel = new Produit();
            $userSales = $productModel->getByVendor($userId);
        }
        
        $pageTitle = 'Mes Acquisitions';
        require_once VIEWS_PATH . '/acquisition/index.php';
    }
    
    /**
     * Affiche les détails d'une précommande
     */
    public function showPrePurchase() {
        requireLogin();
        
        $prePurchaseId = (int)($_GET['id'] ?? 0);
        if ($prePurchaseId <= 0) {
            redirect('/index.php?controller=acquisition&action=index', 'Précommande introuvable', 'error');
        }
        
        require_once MODELS_PATH . '/PrePurchase.php';
        $prePurchaseModel = new PrePurchase();
        $prePurchase = $prePurchaseModel->findById($prePurchaseId);
        
        if (!$prePurchase || (int)$prePurchase['id_client'] !== (int)$_SESSION['user_id']) {
            redirect('/index.php?controller=acquisition&action=index', 'Accès non autorisé', 'error');
        }
        
        $pageTitle = 'Détail de la précommande';
        require_once VIEWS_PATH . '/acquisition/show_prepurchase.php';
    }
    
    /**
     * Annuler une précommande
     */
    public function cancelPrePurchase() {
        requireLogin();
        
        $prePurchaseId = (int)($_POST['id'] ?? 0);
        if ($prePurchaseId <= 0) {
            redirect('/index.php?controller=acquisition&action=index', 'Précommande invalide', 'error');
        }
        
        require_once MODELS_PATH . '/PrePurchase.php';
        $prePurchaseModel = new PrePurchase();
        $prePurchase = $prePurchaseModel->findById($prePurchaseId);
        
        if (!$prePurchase || (int)$prePurchase['id_client'] !== (int)$_SESSION['user_id']) {
            redirect('/index.php?controller=acquisition&action=index', 'Accès non autorisé', 'error');
        }
        
        if ($prePurchaseModel->cancel($prePurchaseId)) {
            redirect('/index.php?controller=acquisition&action=index', 'Précommande annulée avec succès');
        } else {
            redirect('/index.php?controller=acquisition&action=index', 'Erreur lors de l\'annulation', 'error');
        }
    }
    
    /**
     * Affiche les ventes de l'utilisateur (pour les vendeurs)
     */
    public function sales() {
        requireLogin();
        
        $userId = (int)$_SESSION['user_id'];
        
        // Vérifier si l'utilisateur est vendeur
        require_once MODELS_PATH . '/Vendeur.php';
        $vendeurModel = new Vendeur();
        $isVendor = $vendeurModel->findByUserId($userId) !== false;
        
        if (!$isVendor) {
            redirect('/index.php?controller=acquisition&action=index', 'Accès réservé aux vendeurs', 'error');
        }
        
        // Récupérer les produits du vendeur
        require_once MODELS_PATH . '/Produit.php';
        $productModel = new Produit();
        $userProducts = $productModel->getByVendor($userId);
        
        // Récupérer les précommandes pour les produits du vendeur
        require_once MODELS_PATH . '/PrePurchase.php';
        $prePurchaseModel = new PrePurchase();
        $productPrePurchases = [];
        
        foreach ($userProducts as $product) {
            $prePurchases = $prePurchaseModel->getForProduct($product['id_produit']);
            if (!empty($prePurchases)) {
                $productPrePurchases[$product['id_produit']] = $prePurchases;
            }
        }
        
        $pageTitle = 'Mes Ventes';
        require_once VIEWS_PATH . '/acquisition/sales.php';
    }
    
    /**
     * Confirmer une précommande (pour les vendeurs)
     */
    public function confirmPrePurchase() {
        requireLogin();
        
        $prePurchaseId = (int)($_POST['id'] ?? 0);
        if ($prePurchaseId <= 0) {
            redirect('/index.php?controller=acquisition&action=sales', 'Précommande invalide', 'error');
        }
        
        require_once MODELS_PATH . '/PrePurchase.php';
        $prePurchaseModel = new PrePurchase();
        $prePurchase = $prePurchaseModel->findById($prePurchaseId);
        
        if (!$prePurchase) {
            redirect('/index.php?controller=acquisition&action=sales', 'Précommande introuvable', 'error');
        }
        
        // Vérifier que l'utilisateur est le vendeur du produit
        require_once MODELS_PATH . '/Produit.php';
        $productModel = new Produit();
        $product = $productModel->findById($prePurchase['id_produit']);
        
        if (!$product || (int)$product['id_vendeur'] !== (int)$_SESSION['user_id']) {
            redirect('/index.php?controller=acquisition&action=sales', 'Accès non autorisé', 'error');
        }
        
        // Vérifier le stock
        if (!$productModel->isInStock($prePurchase['id_produit'], $prePurchase['quantity'])) {
            redirect('/index.php?controller=acquisition&action=sales', 'Stock insuffisant', 'error');
        }
        
        // Confirmer la précommande et réduire le stock
        if ($prePurchaseModel->confirm($prePurchaseId) && $productModel->decreaseQuantity($prePurchase['id_produit'], $prePurchase['quantity'])) {
            redirect('/index.php?controller=acquisition&action=sales', 'Précommande confirmée avec succès');
        } else {
            redirect('/index.php?controller=acquisition&action=sales', 'Erreur lors de la confirmation', 'error');
        }
    }
}
