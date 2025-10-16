<?php
// controllers/PrepurchaseController.php

class PrepurchaseController {
    public function create() {
        requireLogin();
        require_once MODELS_PATH . '/PrePurchase.php';
        $productId = (int)($_GET['id'] ?? 0);
        if ($productId <= 0) { die('Produit invalide'); }
        $model = new PrePurchase();
        $existing = $model->findByProductAndUser($productId, (int)($_SESSION['user_id'] ?? 0));
        if ($existing && $existing['status'] === 'pending') {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Pré-commande déjà en attente');
        }
        if ($model->create($productId, (int)$_SESSION['user_id'])) {
            redirect('/index.php?controller=product&action=show&id=' . $productId, 'Pré-commande créée, en attente de confirmation');
        }
        redirect('/index.php?controller=product&action=show&id=' . $productId, 'Erreur lors de la pré-commande', 'error');
    }
}


