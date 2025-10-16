<header>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-cart-shopping"></i> Sell & Buy
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">
                <i class="fas fa-store"></i> Produits
            </a>
            <?php if (!empty($_SESSION['is_admin'])): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=index">
                <i class="fas fa-shield-alt"></i> Admin
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register">
                <i class="fas fa-user-plus"></i> Inscription
            </a>
        </div>
        <div class="user-info">
            <span>
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'Invité'); ?>
            </span>
        </div>
    </nav>
</header>
