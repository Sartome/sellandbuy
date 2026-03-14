    <footer>
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><i class="fas fa-cart-shopping"></i> Sell & Buy</h4>
                    <p>Votre marketplace en ligne pour acheter et vendre en toute confiance. Enchères, ventes groupées et achats directs.</p>
                </div>
                <div class="footer-col">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index"><i class="fas fa-store"></i> Produits</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=index"><i class="fas fa-shopping-cart"></i> Mes Acquisitions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?controller=ticket&action=index"><i class="fas fa-life-ring"></i> Support</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Mon Compte</h4>
                    <ul>
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=account"><i class="fas fa-user-cog"></i> Gestion du compte</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=logout"><i class="fas fa-sign-out-alt"></i> Se déconnecter</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login"><i class="fas fa-sign-in-alt"></i> Se connecter</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register"><i class="fas fa-user-plus"></i> Créer un compte</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Sell & Buy Marketplace. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <button id="scroll-to-top" class="scroll-to-top" title="Retour en haut" aria-label="Retour en haut">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script src="<?php echo ASSETS_URL; ?>/js/app.js" nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/enhanced.js" nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>"></script>
</body>
</html>
