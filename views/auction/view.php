<?php $pageTitle = $pageTitle ?? 'Enchères'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Enchères</h1>
    <?php if (!empty($_SESSION['message'])): ?>
        <div class="alert <?php echo $_SESSION['message_type'] ?? 'success'; ?>"><?php echo htmlspecialchars($_SESSION['message']); ?></div>
        <?php $_SESSION['message'] = null; $_SESSION['message_type'] = null; ?>
    <?php endif; ?>

    <?php if (!$auction): ?>
        <div class="card" style="grid-column: span 12;">
            <div class="body">
                <p class="desc">Aucune enchère active pour ce produit.</p>
                <?php if (!empty($_SESSION['is_admin']) || !empty($_SESSION['user_id'])): ?>
                    <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=create&product_id=<?php echo (int)($_GET['product_id'] ?? 0); ?>">Créer une enchère</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="body">
                <p class="desc">Prix de départ: <?php echo number_format((float)$auction['starting_price'], 2, ',', ' '); ?> €</p>
                <p class="price">Prix actuel: <?php echo number_format((float)$auction['current_price'], 2, ',', ' '); ?> €</p>
                <p class="seller">Fin: <?php echo htmlspecialchars($auction['ends_at']); ?></p>
                <?php if (!empty($_SESSION['user_id'])): ?>
                <form method="post" action="<?php echo BASE_URL; ?>/index.php?controller=auction&action=bid">
                    <input type="hidden" name="auction_id" value="<?php echo (int)$auction['id']; ?>">
                    <div class="form-group">
                        <label for="amount">Votre offre (€)</label>
                        <input required type="number" step="0.01" min="0" name="amount" id="amount">
                    </div>
                    <button class="btn" type="submit">Enchérir</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="body">
                <h3>Offres récentes</h3>
                <?php if (empty($bids)): ?>
                    <p class="seller">Aucune offre pour le moment.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($bids as $bid): ?>
                            <li>
                                <?php echo number_format((float)$bid['amount'], 2, ',', ' '); ?> € — <?php echo htmlspecialchars($bid['created_at']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


