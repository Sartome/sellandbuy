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
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=create" class="btn">Vendre un produit</a>
    </div>

    <div class="grid">
        <?php if (empty($products)): ?>
            <div class="card" style="grid-column: span 12;">
                <div class="body">
                    <p class="desc">Aucun produit pour le moment.</p>
                    <p class="seller">Connectez-vous en tant que vendeur pour en publier un.</p>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
            <div class="card">
                <div class="thumb">
                    <?php if (!empty($p['image'])): ?>
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="Produit">
                    <?php else: ?>
                        <div class="placeholder">Aucune image</div>
                    <?php endif; ?>
                </div>
                <div class="body">
                    <p class="desc"><?php echo htmlspecialchars($p['description']); ?></p>
                    <?php if (!empty($p['categorie'])): ?>
                        <p class="seller">Catégorie: <?php echo htmlspecialchars($p['categorie']); ?></p>
                    <?php endif; ?>
                    <p class="price"><?php echo number_format((float)$p['prix'], 2, ',', ' '); ?> €</p>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo (int)$p['id_produit']; ?>">Voir</a>
                        <?php if (!empty($_SESSION['user_id']) && (int)($_SESSION['user_id']) !== (int)$p['id_vendeur']): ?>
                            <a class="btn btn-buy" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=buy&id=<?php echo (int)$p['id_produit']; ?>">Acheter</a>
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


