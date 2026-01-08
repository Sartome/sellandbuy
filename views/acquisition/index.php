<?php $pageTitle = $pageTitle ?? 'Mes Acquisitions'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="acquisition-header">
        <h1>🛒 Mes Acquisitions</h1>
        <p>Gérez vos achats, précommandes et enchères</p>
    </div>

    <div class="acquisition-tabs">
        <button class="tab-btn active" onclick="showTab(this, 'purchases')">
            <i class="fas fa-shopping-cart"></i> Mes Achats
        </button>
        <button class="tab-btn" onclick="showTab(this, 'preorders')">
            <i class="fas fa-clock"></i> Précommandes
        </button>
        <button class="tab-btn" onclick="showTab(this, 'auctions')">
            <i class="fas fa-gavel"></i> Enchères
        </button>
        <?php if ($isVendor): ?>
            <button class="tab-btn" onclick="showTab(this, 'sales')">
                <i class="fas fa-store"></i> Mes Ventes
            </button>
        <?php endif; ?>
    </div>

    <!-- Onglet Achats -->
    <div id="purchases-tab" class="tab-content active">
        <div class="section-header">
            <h2>Mes Achats</h2>
            <p>Historique de vos achats effectués</p>
        </div>
        
        <?php if (empty($purchases)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>Aucun achat pour le moment</h3>
                <p>Commencez à explorer nos produits !</p>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index" class="btn">
                    <i class="fas fa-store"></i> Voir les produits
                </a>
            </div>
        <?php else: ?>
            <div class="purchases-grid">
                <?php foreach ($purchases as $purchase): ?>
                    <div class="purchase-card">
                        <div class="purchase-info">
                            <h3><?php echo htmlspecialchars($purchase['description'] ?? 'Produit'); ?></h3>
                            <div class="purchase-details">
                                <div class="detail-item">
                                    <strong>Montant:</strong>
                                    <span class="price"><?php echo number_format((float)($purchase['amount'] ?? 0), 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Date:</strong>
                                    <span><?php echo !empty($purchase['created_at']) ? date('d/m/Y à H:i', strtotime($purchase['created_at'])) : ''; ?></span>
                                </div>
                            </div>
                            <div class="purchase-actions">
                                <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo (int)($purchase['product_id'] ?? 0); ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i> Voir le produit
                                </a>
                                <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=invoice&id=<?php echo (int)($purchase['id'] ?? 0); ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-invoice"></i> Facture PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onglet Précommandes -->
    <div id="preorders-tab" class="tab-content">
        <div class="section-header">
            <h2>Mes Précommandes</h2>
            <p>Vos commandes en attente de confirmation</p>
        </div>
        
        <?php if (empty($prePurchases)): ?>
            <div class="empty-state">
                <i class="fas fa-clock"></i>
                <h3>Aucune précommande</h3>
                <p>Vous n'avez pas encore de précommandes en cours.</p>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index" class="btn">
                    <i class="fas fa-store"></i> Découvrir les produits
                </a>
            </div>
        <?php else: ?>
            <div class="preorders-grid">
                <?php foreach ($prePurchases as $prePurchase): ?>
                    <div class="preorder-card">
                        <div class="preorder-image">
                            <?php if (!empty($prePurchase['image'])): ?>
                                <img src="<?php echo htmlspecialchars($prePurchase['image']); ?>" alt="Produit">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="preorder-info">
                            <h3><?php echo htmlspecialchars($prePurchase['description']); ?></h3>
                            <div class="preorder-details">
                                <div class="detail-item">
                                    <strong>Prix:</strong>
                                    <span class="price"><?php echo number_format((float)$prePurchase['prix'], 2, ',', ' '); ?> €</span>
                                </div>
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
                                <?php if (!empty($prePurchase['expires_at'])): ?>
                                    <div class="detail-item">
                                        <strong>Expire le:</strong>
                                        <span><?php echo date('d/m/Y à H:i', strtotime($prePurchase['expires_at'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="preorder-actions">
                                <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=showPrePurchase&id=<?php echo (int)$prePurchase['id']; ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i> Voir détails
                                </a>
                                <?php if ($prePurchase['status'] === 'pending'): ?>
                                    <form method="POST" action="<?php echo BASE_URL; ?>/index.php?controller=acquisition&amp;action=cancelPrePurchase" style="display: inline;" onsubmit="return confirm('Annuler cette précommande ?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$prePurchase['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onglet Enchères -->
    <div id="auctions-tab" class="tab-content">
        <div class="section-header">
            <h2>Mes Enchères</h2>
            <p>Vos participations aux enchères</p>
        </div>
        
        <?php if (empty($userBids)): ?>
            <div class="empty-state">
                <i class="fas fa-gavel"></i>
                <h3>Aucune participation aux enchères</h3>
                <p>Vous n'avez pas encore participé à des enchères.</p>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index" class="btn">
                    <i class="fas fa-store"></i> Voir les enchères
                </a>
            </div>
        <?php else: ?>
            <div class="auctions-grid">
                <?php foreach ($userBids as $bid): ?>
                    <div class="auction-card">
                        <div class="auction-image">
                            <?php if (!empty($bid['image'])): ?>
                                <img src="<?php echo htmlspecialchars($bid['image']); ?>" alt="Produit">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="auction-info">
                            <h3><?php echo htmlspecialchars($bid['description']); ?></h3>
                            <div class="auction-details">
                                <div class="detail-item">
                                    <strong>Ma mise:</strong>
                                    <span class="price"><?php echo number_format((float)$bid['amount'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Prix actuel:</strong>
                                    <span class="price"><?php echo number_format((float)$bid['current_price'], 2, ',', ' '); ?> €</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Fin de l'enchère:</strong>
                                    <span><?php echo date('d/m/Y à H:i', strtotime($bid['ends_at'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Statut:</strong>
                                    <span class="status status-<?php echo $bid['status']; ?>">
                                        <?php 
                                        if ($bid['status'] === 'active') {
                                            echo strtotime($bid['ends_at']) > time() ? 'En cours' : 'Terminée';
                                        } else {
                                            echo ucfirst($bid['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="auction-actions">
                                <a href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=view&product_id=<?php echo (int)$bid['id_produit']; ?>" class="btn btn-sm">
                                    <i class="fas fa-eye"></i> Voir l'enchère
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Onglet Ventes (pour les vendeurs) -->
    <?php if ($isVendor): ?>
        <div id="sales-tab" class="tab-content">
            <div class="section-header">
                <h2>Mes Ventes</h2>
                <p>Gérez vos produits et précommandes</p>
            </div>
            
            <div class="sales-actions">
                <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un produit
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=sales" class="btn">
                    <i class="fas fa-chart-line"></i> Gérer les ventes
                </a>
            </div>
            
            <?php if (empty($userSales)): ?>
                <div class="empty-state">
                    <i class="fas fa-store"></i>
                    <h3>Aucun produit en vente</h3>
                    <p>Commencez à vendre vos produits !</p>
                    <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=create" class="btn">
                        <i class="fas fa-plus"></i> Créer un produit
                    </a>
                </div>
            <?php else: ?>
                <div class="sales-grid">
                    <?php foreach ($userSales as $product): ?>
                        <div class="sale-card">
                            <div class="sale-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Produit">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="sale-info">
                                <h3><?php echo htmlspecialchars($product['description']); ?></h3>
                                <div class="sale-details">
                                    <div class="detail-item">
                                        <strong>Prix:</strong>
                                        <span class="price"><?php echo number_format((float)$product['prix'], 2, ',', ' '); ?> €</span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Stock:</strong>
                                        <span class="stock"><?php echo (int)($product['quantity'] ?? 0); ?> disponible(s)</span>
                                    </div>
                                </div>
                                
                                <div class="sale-actions">
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo (int)$product['id_produit']; ?>" class="btn btn-sm">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=edit&id=<?php echo (int)$product['id_produit']; ?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<style>
.acquisition-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.acquisition-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.tab-btn {
    padding: 12px 20px;
    border: 2px solid var(--border);
    background: var(--panel);
    color: var(--text);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.tab-btn:hover {
    border-color: var(--primary);
    background: rgba(124,58,237,0.1);
}

.tab-btn.active {
    border-color: var(--primary);
    background: var(--primary);
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.section-header {
    margin-bottom: 20px;
}

.section-header h2 {
    margin: 0 0 8px 0;
    color: var(--text);
}

.section-header p {
    margin: 0;
    color: var(--muted);
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 8px 0;
    font-size: 1.5rem;
}

.empty-state p {
    margin: 0 0 20px 0;
}

.preorders-grid,
.auctions-grid,
.sales-grid,
.purchases-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.preorder-card,
.auction-card,
.sale-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    transition: all 0.3s ease;
}

.preorder-card:hover,
.auction-card:hover,
.sale-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.preorder-image,
.auction-image,
.sale-image {
    height: 200px;
    overflow: hidden;
    position: relative;
}

.preorder-image img,
.auction-image img,
.sale-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--panel-2);
    color: var(--muted);
    font-size: 2rem;
}

.preorder-info,
.auction-info,
.sale-info {
    padding: 20px;
}

.preorder-info h3,
.auction-info h3,
.sale-info h3 {
    margin: 0 0 16px 0;
    font-size: 1.1rem;
    line-height: 1.3;
}

.preorder-details,
.auction-details,
.sale-details {
    display: grid;
    gap: 8px;
    margin-bottom: 16px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
}

.detail-item strong {
    color: var(--muted);
    font-size: 0.9rem;
}

.price {
    color: var(--money);
    font-weight: 600;
}

.stock {
    color: var(--success);
    font-weight: 500;
}

.status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-pending {
    background: rgba(245,158,11,0.2);
    color: #f59e0b;
}

.status-confirmed {
    background: rgba(16,185,129,0.2);
    color: #10b981;
}

.status-cancelled,
.status-expired {
    background: rgba(239,68,68,0.2);
    color: #ef4444;
}

.status-active {
    background: rgba(34,197,94,0.2);
    color: #22c55e;
}

.preorder-actions,
.auction-actions,
.sale-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
}

.sales-actions {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .acquisition-tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        justify-content: center;
    }
    
    .preorders-grid,
    .auctions-grid,
    .sales-grid {
        grid-template-columns: 1fr;
    }
    
    .sales-actions {
        flex-direction: column;
    }
}
</style>

<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
function showTab(buttonEl, tabName) {
    // Masquer tous les onglets
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Désactiver tous les boutons
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Afficher l'onglet sélectionné
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Activer le bouton correspondant
    if (buttonEl) {
        buttonEl.classList.add('active');
    }
}
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
