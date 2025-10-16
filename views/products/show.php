<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Détail du produit</h1>
    <div class="product-detail">
        <div class="image">
            <?php if (!empty($product['image'])): ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Produit">
            <?php else: ?>
                <div class="placeholder">Aucune image</div>
            <?php endif; ?>
        </div>
        <div class="info">
            <p class="desc"><?php echo htmlspecialchars($product['description']); ?></p>
            <p class="price"><?php echo number_format((float)$product['prix'], 2, ',', ' '); ?> €</p>
            <p class="seller">Vendeur: <?php echo htmlspecialchars($product['nom_entreprise'] ?? '—'); ?></p>
            <?php if (!empty($product['categorie'])): ?>
                <p class="seller">Catégorie: <?php echo htmlspecialchars($product['categorie']); ?></p>
            <?php endif; ?>
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                <?php if (!empty($_SESSION['user_id']) && (int)($_SESSION['user_id']) !== (int)$product['id_vendeur']): ?>
                    <a class="btn btn-buy" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=buy&id=<?php echo (int)$product['id_produit']; ?>">Acheter</a>
                    <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=prepurchase&action=create&id=<?php echo (int)$product['id_produit']; ?>">Pré-commander</a>
                    <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=view&product_id=<?php echo (int)$product['id_produit']; ?>">Enchères</a>
                <?php endif; ?>
                <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$product['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                    <a class="btn btn-danger" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=delete&id=<?php echo (int)$product['id_produit']; ?>" onclick="return confirm('Supprimer ce produit ?');">Supprimer</a>
                    <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=create&product_id=<?php echo (int)$product['id_produit']; ?>">Créer une enchère</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <p>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">Retour</a>
    </p>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


