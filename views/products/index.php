<?php $pageTitle = $pageTitle ?? 'Produits'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Produits</h1>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert <?php echo $_SESSION['message_type'] ?? 'success'; ?>"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
        <?php $_SESSION['message'] = null; $_SESSION['message_type'] = null; ?>
    <?php endif; ?>

    <div class="actions">
        <?php 
        // Vérifier si l'utilisateur est un vendeur ou un admin
        $isSeller = false;
        if (!empty($_SESSION['user_id'])) {
            require_once MODELS_PATH . '/Vendeur.php';
            $vendeur = new Vendeur();
            $isSeller = $vendeur->findByUserId((int)$_SESSION['user_id']) !== false;
        }
        ?>
        <?php if ($isSeller || !empty($_SESSION['is_admin'])): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=create" class="btn">
                <i class="fas fa-plus"></i> Vendre un produit
            </a>
        <?php endif; ?>
    </div>

    <div class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="grid-column: span 12;">
                <div class="body">
                    <p class="desc">Aucun produit pour le moment.</p>
                    <?php if (!$isSeller): ?>
                        <p class="seller">Connectez-vous en tant que vendeur pour en publier un.</p>
                    <?php else: ?>
                        <p class="seller">Soyez le premier à publier un produit !</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <div class="card animate-on-scroll card-hover">
                <div class="thumb">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="Produit" class="thumbnail">
                    <?php else: ?>
                        <div class="placeholder">Aucune image</div>
                    <?php endif; ?>
                </div>
                <div class="body">
                    <p class="desc"><?php echo htmlspecialchars($p['description']); ?></p>
                    <?php if (!empty($p['categorie'])): ?>
                        <p class="seller">Catégorie: <?php echo htmlspecialchars($p['categorie']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($p['nom_entreprise'])): ?>
                        <p class="seller seller-with-avatar">
                            <span class="seller-avatar-small">
                                <?php if (!empty($p['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['avatar']); ?>" alt="Photo du vendeur">
                                <?php else: ?>
                                    <i class="fas fa-store"></i>
                                <?php endif; ?>
                            </span>
                            <span>Vendeur: <?php echo htmlspecialchars($p['nom_entreprise']); ?></span>
                        </p>
                    <?php endif; ?>
                    <?php 
                    // Vérifier si le vendeur est certifié
                    $isVendorCertified = false;
                    if (!empty($p['id_vendeur'])) {
                        require_once MODELS_PATH . '/Vendeur.php';
                        $vendeur = new Vendeur();
                        $isVendorCertified = $vendeur->isCertified((int)$p['id_vendeur']);
                    }
                    ?>
                    <?php if ($isVendorCertified): ?>
                        <p class="seller-badge">
                            <span class="badge certified">
                                <i class="fas fa-check-circle"></i> Vendeur Certifié
                            </span>
                        </p>
                    <?php endif; ?>
                    <?php 
                    // Vérifier si c'est une enchère
                    $isAuction = false;
                    $auction = null;
                    if (!empty($p['id_produit'])) {
                        require_once MODELS_PATH . '/Auction.php';
                        $auctionModel = new Auction();
                        $auction = $auctionModel->getByProduct((int)$p['id_produit']);
                        $isAuction = $auction !== false;
                    }
                    ?>
                    
                    <?php if ($isAuction && $auction): ?>
                        <div class="auction-info">
                            <p class="price">Prix actuel: <?php echo number_format((float)$auction['current_price'], 2, ',', ' '); ?> €</p>
                            <p class="auction-end">Fin: <?php echo date('d/m/Y à H:i', strtotime($auction['ends_at'])); ?></p>
                            <?php if (strtotime($auction['ends_at']) > time()): ?>
                                <span class="auction-status active">🕐 En cours</span>
                            <?php else: ?>
                                <span class="auction-status ended">⏰ Terminée</span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="price"><?php echo number_format((float)$p['prix'], 2, ',', ' '); ?> €</p>
                    <?php endif; ?>
                    
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo (int)$p['id_produit']; ?>">Voir</a>
                        <?php if (!empty($_SESSION['user_id']) && (int)($_SESSION['user_id']) !== (int)$p['id_vendeur']): ?>
                            <?php if ($isAuction && $auction && strtotime($auction['ends_at']) > time()): ?>
                                <a class="btn btn-auction" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=view&product_id=<?php echo (int)$p['id_produit']; ?>">
                                    <i class="fas fa-gavel"></i> Enchérir
                                </a>
                            <?php elseif (!$isAuction): ?>
                                <?php 
                                $productModel = new Produit();
                                $isInStock = $productModel->isInStock((int)$p['id_produit'], 1);
                                $availableQuantity = $productModel->getAvailableQuantity((int)$p['id_produit']);
                                ?>
                                <?php if ($isInStock): ?>
                                    <a class="btn btn-buy" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=buy&id=<?php echo (int)$p['id_produit']; ?>">Acheter</a>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fas fa-times"></i> Rupture de stock
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($availableQuantity > 0 && $availableQuantity < 10): ?>
                                    <small class="stock-warning">Plus que <?php echo $availableQuantity; ?> disponible(s)</small>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$p['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                            <a class="btn btn-danger" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=delete&id=<?php echo (int)$p['id_produit']; ?>" onclick="return confirm('Supprimer ce produit ?');">Supprimer</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>



<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


