<?php $pageTitle = $pageTitle ?? 'Détail de la précommande'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<?php
require_once MODELS_PATH . '/Produit.php';
$produitModel = new Produit();
$product = $produitModel->findById((int)$prePurchase['id_produit']);
?>

<main class="container">
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&amp;action=index">Mes acquisitions</a>
        <span>›</span>
        <span>Précommande #<?php echo (int)$prePurchase['id']; ?></span>
    </div>

    <div class="prepurchase-detail">
        <div class="prepurchase-header">
            <h1>Précommande #<?php echo (int)$prePurchase['id']; ?></h1>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&amp;action=index" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à mes précommandes
            </a>
        </div>

        <div class="prepurchase-layout">
            <div class="prepurchase-main">
                <h2>Produit</h2>
                <?php if ($product): ?>
                    <div class="product-summary">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Produit">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['description']); ?></h3>
                            <p><strong>Prix unitaire:</strong> <?php echo number_format((float)$product['prix'], 2, ',', ' '); ?> €</p>
                            <a href="<?php echo BASE_URL; ?>/index.php?controller=product&amp;action=show&amp;id=<?php echo (int)$product['id_produit']; ?>" class="btn btn-sm">
                                <i class="fas fa-eye"></i> Voir le produit
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <p>Produit introuvable.</p>
                <?php endif; ?>

                <h2>Détails de la précommande</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong>Quantité:</strong>
                        <span><?php echo (int)$prePurchase['quantity']; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Statut:</strong>
                        <span class="status status-<?php echo $prePurchase['status']; ?>">
                            <?php 
                            switch($prePurchase['status']) {
                                case 'pending': echo 'En attente'; break;
                                case 'confirmed': echo 'Confirmée'; break;
                                case 'cancelled': echo 'Annulée'; break;
                                case 'expired': echo 'Expirée'; break;
                                default: echo ucfirst($prePurchase['status']);
                            }
                            ?>
                        </span>
                    </div>
                    <?php if (!empty($prePurchase['created_at'])): ?>
                        <div class="detail-item">
                            <strong>Créée le:</strong>
                            <span><?php echo date('d/m/Y à H:i', strtotime($prePurchase['created_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($prePurchase['expires_at'])): ?>
                        <div class="detail-item">
                            <strong>Expire le:</strong>
                            <span><?php echo date('d/m/Y à H:i', strtotime($prePurchase['expires_at'])); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($prePurchase['status'] === 'pending'): ?>
                    <div class="prepurchase-actions">
                        <form method="POST" action="<?php echo BASE_URL; ?>/index.php?controller=acquisition&amp;action=cancelPrePurchase" onsubmit="return confirm('Annuler cette précommande ?');">
                            <input type="hidden" name="id" value="<?php echo (int)$prePurchase['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Annuler la précommande
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<style>
.prepurchase-detail {
    margin-top: 20px;
}

.prepurchase-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.prepurchase-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-summary {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 20px;
}

.product-image {
    width: 160px;
    height: 160px;
    overflow: hidden;
    border-radius: 8px;
    background: var(--panel-2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info h3 {
    margin-top: 0;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 10px 12px;
}

.prepurchase-actions {
    margin-top: 10px;
}

@media (max-width: 768px) {
    .prepurchase-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .product-summary {
        flex-direction: column;
    }

    .product-image {
        width: 100%;
        height: 200px;
    }
}
</style>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
