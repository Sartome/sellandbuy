<?php
// controllers/AuctionController.php

class AuctionController {
    public function view() {
        require_once MODELS_PATH . '/Auction.php';
        $productId = (int)($_GET['product_id'] ?? 0);
        if ($productId <= 0) { die('Produit invalide'); }
        $auctionModel = new Auction();
        $auction = $auctionModel->getByProduct($productId);
        $bids = [];
        if ($auction) { $bids = $auctionModel->listBids((int)$auction['id']); }
        $pageTitle = 'Enchères';
        require_once VIEWS_PATH . '/auction/view.php';
    }

    public function create() {
        requireLogin();
        require_once MODELS_PATH . '/Vendeur.php';
        require_once MODELS_PATH . '/Auction.php';
        $vendeurModel = new Vendeur();
        $vendeur = $vendeurModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
        if (!$vendeur) { die('Accès réservé aux vendeurs'); }

        $productId = (int)($_GET['product_id'] ?? 0);
        if ($productId <= 0) { die('Produit invalide'); }

        $auctionModel = new Auction();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $starting = (float)($_POST['starting_price'] ?? 0);
            $durationHours = (int)($_POST['duration_hours'] ?? 24);
            if ($starting <= 0 || $durationHours <= 0) {
                $error = 'Vérifiez les champs';
            } else {
                $endsAt = date('Y-m-d H:i:s', time() + $durationHours * 3600);
                if ($auctionModel->create($productId, $starting, $endsAt)) {
                    redirect('/index.php?controller=auction&action=view&product_id=' . $productId, 'Enchère créée');
                } else {
                    $error = "Impossible de créer l'enchère";
                }
            }
        }
        $pageTitle = 'Créer une enchère';
        require_once VIEWS_PATH . '/auction/create.php';
    }

    public function bid() {
        requireLogin();
        require_once MODELS_PATH . '/Auction.php';
        $auctionId = (int)($_POST['auction_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        if ($auctionId <= 0 || $amount <= 0) { die('Données invalides'); }
        $auctionModel = new Auction();
        if ($auctionModel->placeBid($auctionId, (int)$_SESSION['user_id'], $amount)) {
            $auction = $auctionModel->findById($auctionId);
            redirect('/index.php?controller=auction&action=view&product_id=' . (int)$auction['id_produit'], 'Offre enregistrée');
        }
        $auction = $auctionModel->findById($auctionId);
        redirect('/index.php?controller=auction&action=view&product_id=' . (int)$auction['id_produit'], 'Offre refusée', 'error');
    }
}


