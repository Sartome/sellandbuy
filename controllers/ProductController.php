<?php
// controllers/ProductController.php

class ProductController {
    public function index() {
        require_once MODELS_PATH . '/Produit.php';
        $productModel = new Produit();
        $products = $productModel->getAll();
        $pageTitle = 'Produits';
        require_once VIEWS_PATH . '/products/index.php';
    }

    public function show() {
        require_once MODELS_PATH . '/Produit.php';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productModel = new Produit();
        $product = $productModel->findById($id);
        if (!$product) {
            die('Produit introuvable');
        }
        $pageTitle = 'Détail produit';
        require_once VIEWS_PATH . '/products/show.php';
    }

    public function create() {
        requireLogin();
        require_once MODELS_PATH . '/Vendeur.php';
        require_once MODELS_PATH . '/Categorie.php';
        $vendeurModel = new Vendeur();
        $vendeur = $vendeurModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
        if (!$vendeur) {
            die('Accès réservé aux vendeurs');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Produit.php';
            $catModel = new Categorie();
            $defaultCatId = $catModel->ensureDefaultAcquisition();
            $data = [
                'description' => sanitize($_POST['description'] ?? ''),
                'prix' => (float)($_POST['prix'] ?? 0),
                'image' => sanitize($_POST['image'] ?? ''),
                'id_vendeur' => (int)$_SESSION['user_id'],
                'id_categorie' => (int)($_POST['id_categorie'] ?? $defaultCatId),
            ];
            $productModel = new Produit();
            if ($data['description'] && $data['prix'] > 0 && $productModel->create($data)) {
                redirect('/index.php?controller=product&action=index', 'Produit créé');
            } else {
                $error = 'Vérifiez les champs';
            }
        }

        $catModel = new Categorie();
        $categories = $catModel->getAll();
        $pageTitle = 'Nouveau produit';
        require_once VIEWS_PATH . '/products/create.php';
    }

    public function buy() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/Sale.php';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productModel = new Produit();
        $product = $productModel->findById($id);
        if (!$product) {
            redirect('/index.php?controller=product&action=index', 'Produit introuvable', 'error');
        }
        // Empêcher l’achat de son propre produit
        if ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0)) {
            redirect('/index.php?controller=product&action=index', 'Vous ne pouvez pas acheter votre propre produit', 'error');
        }
        // Enregistrer la vente
        $sale = new Sale();
        $sale->create($id, (int)$_SESSION['user_id'], (float)$product['prix']);
        redirect('/index.php?controller=product&action=show&id=' . $id, 'Achat enregistré avec succès');
    }

    public function delete() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productModel = new Produit();
        $product = $productModel->findById($id);
        if (!$product) {
            redirect('/index.php?controller=product&action=index', 'Produit introuvable', 'error');
        }
        // Autoriser suppression uniquement au propriétaire vendeur ou admin
        $isOwner = ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isOwner && !$isAdmin) {
            redirect('/index.php?controller=product&action=index', 'Action non autorisée', 'error');
        }
        // Suppression
        if ($productModel->deleteById($id)) {
            redirect('/index.php?controller=product&action=index', 'Produit supprimé');
        } else {
            redirect('/index.php?controller=product&action=show&id=' . $id, 'Erreur lors de la suppression', 'error');
        }
    }
}


