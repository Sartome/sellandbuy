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

    /**
     * Signaler un produit (utilisateur connecté)
     */
    public function signal() {
        requireLogin();
        $productId = (int)($_GET['id'] ?? 0);
        if ($productId <= 0) {
            redirect('/index.php?controller=product&action=index', 'Produit invalide', 'error');
        }

        require_once MODELS_PATH . '/Signaler.php';
        $model = new Signaler();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if ($model->create($userId, $productId)) {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Produit signalé. Merci de nous aider à maintenir la qualité.', 'success');
        }

        redirect('/index.php?controller=product&action=show&id=' . $productId, 'Erreur lors de l\'envoi du signalement', 'error');
    }

    public function create() {
        requireLogin();
        require_once MODELS_PATH . '/Vendeur.php';
        require_once MODELS_PATH . '/Categorie.php';
        require_once HELPERS_PATH . '/ImageUpload.php';
        require_once MODELS_PATH . '/ProduitImage.php';
        
        // Vérifier si l'utilisateur peut créer des produits
        if (!canCreateProducts()) {
            die('Accès réservé aux vendeurs et administrateurs');
        }
        
        $vendeurModel = new Vendeur();
        $vendeur = $vendeurModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
        $isAdmin = isAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once MODELS_PATH . '/Produit.php';
            $catModel = new Categorie();
            $defaultCatId = $catModel->ensureDefaultAcquisition();
            
            $saleType = sanitize($_POST['sale_type'] ?? 'buy');
            // Déterminer l'ID du vendeur à utiliser
            $vendorId = null;
            if ($vendeur) {
                $vendorId = $vendeur['id_user'];
            } elseif ($isAdmin) {
                // Pour les admins, créer un profil vendeur s'il n'existe pas
                $adminVendeurData = [
                    'nom_entreprise' => 'Administrateur',
                    'siret' => null,
                    'adresse_entreprise' => '',
                    'email_pro' => $_SESSION['email'] ?? null,
                ];

                if ($vendeurModel->create((int)$_SESSION['user_id'], $adminVendeurData)) {
                    $vendorId = (int)$_SESSION['user_id'];
                } else {
                    die('Erreur: Impossible de créer le profil vendeur pour l\'administrateur');
                }
            }
            
            if (!$vendorId) {
                die('Erreur: Impossible de déterminer l\'ID du vendeur');
            }
            
            $prixHt = (float)($_POST['prix_ht'] ?? 0);
            $tauxTva = (float)($_POST['taux_tva'] ?? 0);

            $data = [
                'description' => sanitize($_POST['description'] ?? ''),
                'prix' => (float)($_POST['prix'] ?? 0),
                'prix_ht' => $prixHt,
                'taux_tva' => $tauxTva,
                'image' => '', // Sera mis à jour après upload
                'image_alt' => sanitize($_POST['image_alt'] ?? ''),
                'id_vendeur' => $vendorId,
                'id_categorie' => (int)($_POST['id_categorie'] ?? $defaultCatId),
                'quantity' => (int)($_POST['quantity'] ?? 1),
            ];
            
            $productModel = new Produit();
            $imageUpload = new ImageUpload();
            $produitImageModel = new ProduitImage();
            
            // Validation selon le type de vente
            $isValid = false;
            if ($saleType === 'buy') {
                $isValid = $data['description'] && $data['prix'] > 0;
            } else if ($saleType === 'auction') {
                $startingPrice = (float)($_POST['starting_price'] ?? 0);
                $auctionEnd = sanitize($_POST['auction_end'] ?? '');
                $isValid = $data['description'] && $startingPrice > 0 && $auctionEnd && strtotime($auctionEnd) > time();
                
                if ($isValid) {
                    $data['prix'] = $startingPrice; // Utiliser le prix de départ pour l'affichage
                }
            }
            
            if ($isValid) {
                // Créer le produit d'abord
                if ($productModel->create($data)) {
                    $productId = $productModel->getLastInsertId();
                    
                    // Gérer l'upload d'images
                    $uploadedImages = [];
                    if (!empty($_FILES['images']['name'][0])) {
                        foreach ($_FILES['images']['name'] as $key => $filename) {
                            if (!empty($filename)) {
                                $file = [
                                    'name' => $_FILES['images']['name'][$key],
                                    'type' => $_FILES['images']['type'][$key],
                                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                                    'error' => $_FILES['images']['error'][$key],
                                    'size' => $_FILES['images']['size'][$key]
                                ];
                                
                                $uploadResult = $imageUpload->uploadImage($file, $productId);
                                if ($uploadResult['success']) {
                                    $uploadedImages[] = $uploadResult;
                                }
                            }
                        }
                    }
                    
                    // Ajouter les images à la base de données
                    foreach ($uploadedImages as $index => $imageData) {
                        $produitImageModel->addImage($productId, [
                            'webPath' => $imageData['webPath'],
                            'alt' => $data['image_alt'],
                            'size' => $imageData['size'],
                            'width' => $imageData['width'],
                            'height' => $imageData['height'],
                            'is_primary' => $index === 0, // Première image = principale
                            'sort_order' => $index
                        ]);
                    }
                    
                    // Mettre à jour l'image principale dans la table Produit
                    if (!empty($uploadedImages)) {
                        $productModel->updateImage($productId, $uploadedImages[0]['webPath']);
                    }
                    
                    // Si c'est une enchère, créer l'enchère
                    if ($saleType === 'auction') {
                        require_once MODELS_PATH . '/Auction.php';
                        $auctionModel = new Auction();
                        $startingPrice = (float)($_POST['starting_price'] ?? 0);
                        $auctionEnd = date('Y-m-d H:i:s', strtotime($_POST['auction_end']));
                        
                        if ($auctionModel->create($productId, $startingPrice, $auctionEnd)) {
                            redirect('/index.php?controller=auction&action=view&product_id=' . $productId, 'Enchère créée avec succès');
                        } else {
                            redirect('/index.php?controller=product&action=index', 'Produit créé mais erreur lors de la création de l\'enchère');
                        }
                    } else {
                        redirect('/index.php?controller=product&action=index', 'Produit créé avec succès');
                    }
                } else {
                    $error = 'Erreur lors de la création du produit';
                }
            } else {
                $error = 'Vérifiez les champs obligatoires';
            }
        }

        $catModel = new Categorie();
        $categories = $catModel->getAll();
        $imageUpload = new ImageUpload();
        $sizeRecommendations = $imageUpload->getSizeRecommendations();

        require_once MODELS_PATH . '/SiteSettings.php';
        $settings = new SiteSettings();
        $defaultTaxRate = $settings->getTaxRate();
        $defaultTaxName = $settings->getTaxName();
        $defaultTaxEnabled = $settings->isTaxEnabled();

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
        // Empêcher l'achat de son propre produit
        if ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0)) {
            redirect('/index.php?controller=product&action=index', 'Vous ne pouvez pas acheter votre propre produit', 'error');
        }
        
        // Vérifier le stock
        if (!$productModel->isInStock($id, 1)) {
            redirect('/index.php?controller=product&action=show&id=' . $id, 'Ce produit n\'est plus en stock', 'error');
        }
        
        // Enregistrer la vente et réduire le stock
        $sale = new Sale();
        if ($sale->create($id, (int)$_SESSION['user_id'], (float)$product['prix']) && $productModel->decreaseQuantity($id, 1)) {
            redirect('/index.php?controller=product&action=show&id=' . $id, 'Achat enregistré avec succès');
        } else {
            redirect('/index.php?controller=product&action=show&id=' . $id, 'Erreur lors de l\'achat', 'error');
        }
    }

    public function delete() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/ProduitImage.php';
        require_once HELPERS_PATH . '/ImageUpload.php';
        
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
        
        // Supprimer les images associées
        $produitImageModel = new ProduitImage();
        $images = $produitImageModel->getImagesByProduct($id);
        $imageUpload = new ImageUpload();
        
        foreach ($images as $image) {
            $filename = basename($image['image_path']);
            $imageUpload->deleteImage($filename);
        }
        
        // Suppression du produit (les images seront supprimées par CASCADE)
        if ($productModel->deleteById($id)) {
            redirect('/index.php?controller=product&action=index', 'Produit supprimé');
        } else {
            redirect('/index.php?controller=product&action=show&id=' . $id, 'Erreur lors de la suppression', 'error');
        }
    }

    /**
     * Gérer l'upload d'images supplémentaires pour un produit existant
     */
    public function addImages() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/ProduitImage.php';
        require_once HELPERS_PATH . '/ImageUpload.php';
        
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $productModel = new Produit();
        $product = $productModel->findById($productId);
        
        if (!$product) {
            redirect('/index.php?controller=product&action=index', 'Produit introuvable', 'error');
        }
        
        // Vérifier les permissions
        $isOwner = ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isOwner && !$isAdmin) {
            redirect('/index.php?controller=product&action=index', 'Action non autorisée', 'error');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imageUpload = new ImageUpload();
            $produitImageModel = new ProduitImage();
            $uploadedImages = [];
            
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    if (!empty($filename)) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $uploadResult = $imageUpload->uploadImage($file, $productId);
                        if ($uploadResult['success']) {
                            $uploadedImages[] = $uploadResult;
                        }
                    }
                }
                
                // Ajouter les images à la base de données
                $currentImageCount = $produitImageModel->countImagesByProduct($productId);
                foreach ($uploadedImages as $index => $imageData) {
                    $produitImageModel->addImage($productId, [
                        'webPath' => $imageData['webPath'],
                        'alt' => sanitize($_POST['image_alt'] ?? ''),
                        'size' => $imageData['size'],
                        'width' => $imageData['width'],
                        'height' => $imageData['height'],
                        'is_primary' => false,
                        'sort_order' => $currentImageCount + $index
                    ]);
                }
                
                redirect('/index.php?controller=product&action=show&id=' . $productId, 'Images ajoutées avec succès');
            }
        }
        
        $produitImageModel = new ProduitImage();
        $existingImages = $produitImageModel->getImagesByProduct($productId);
        
        $pageTitle = 'Ajouter des images';
        require_once VIEWS_PATH . '/products/add_images.php';
    }

    /**
     * Supprimer une image spécifique
     */
    public function deleteImage() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/ProduitImage.php';
        require_once HELPERS_PATH . '/ImageUpload.php';
        
        $imageId = isset($_GET['image_id']) ? (int)$_GET['image_id'] : 0;
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        
        $produitImageModel = new ProduitImage();
        $image = $produitImageModel->getImageById($imageId);
        
        if (!$image || $image['id_produit'] !== $productId) {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Image introuvable', 'error');
        }
        
        // Vérifier les permissions
        $productModel = new Produit();
        $product = $productModel->findById($productId);
        $isOwner = ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = !empty($_SESSION['is_admin']);
        
        if (!$isOwner && !$isAdmin) {
            redirect('/index.php?controller=product&action=index', 'Action non autorisée', 'error');
        }
        
        if ($produitImageModel->deleteImage($imageId)) {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Image supprimée');
        } else {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Erreur lors de la suppression', 'error');
        }
    }

    /**
     * Définir une image comme principale
     */
    public function setPrimaryImage() {
        requireLogin();
        require_once MODELS_PATH . '/Produit.php';
        require_once MODELS_PATH . '/ProduitImage.php';
        
        $imageId = isset($_GET['image_id']) ? (int)$_GET['image_id'] : 0;
        $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
        
        $produitImageModel = new ProduitImage();
        $image = $produitImageModel->getImageById($imageId);
        
        if (!$image || $image['id_produit'] !== $productId) {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Image introuvable', 'error');
        }
        
        // Vérifier les permissions
        $productModel = new Produit();
        $product = $productModel->findById($productId);
        $isOwner = ((int)$product['id_vendeur'] === (int)($_SESSION['user_id'] ?? 0));
        $isAdmin = !empty($_SESSION['is_admin']);
        
        if (!$isOwner && !$isAdmin) {
            redirect('/index.php?controller=product&action=index', 'Action non autorisée', 'error');
        }
        
        if ($produitImageModel->setPrimaryImage($imageId, $productId)) {
            // Mettre à jour l'image principale dans la table Produit
            $productModel->updateImage($productId, $image['image_path']);
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Image principale mise à jour');
        } else {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Erreur lors de la mise à jour', 'error');
        }
    }
}


