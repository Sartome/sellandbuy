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
        
        // Récupérer les paramètres
        $quantity = (int)($_POST['quantity'] ?? 1);
        $expiresAt = null;
        
        // Vérifier si un temps limite est spécifié
        if (!empty($_POST['expires_at'])) {
            $expiresAt = date('Y-m-d H:i:s', strtotime($_POST['expires_at']));
            if (strtotime($expiresAt) <= time()) {
                redirect('/index.php?controller=product&action=show&id=' . $productId, 'La date d\'expiration doit être dans le futur', 'error');
            }
        }
        
        if ($model->create($productId, (int)$_SESSION['user_id'], $quantity, $expiresAt)) {
            $message = 'Pré-commande créée, en attente de confirmation';
            if ($expiresAt) {
                $message .= ' (expire le ' . date('d/m/Y à H:i', strtotime($expiresAt)) . ')';
            }
            redirect('/index.php?controller=product&action=show&id=' . $productId, $message);
        }
        redirect('/index.php?controller=product&action=show&id=' . $productId, 'Erreur lors de la pré-commande', 'error');
    }
}


