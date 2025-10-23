<header>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-cart-shopping"></i> Sell & Buy
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">
                <i class="fas fa-store"></i> Produits
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=index">
                <i class="fas fa-shopping-cart"></i> Mes Acquisitions
            </a>
            <?php if (!empty($_SESSION['is_admin'])): ?>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=index">
                <i class="fas fa-shield-alt"></i> Admin
            </a>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=analytics">
                <i class="fas fa-chart-line"></i> Stats
            </a>
            <?php endif; ?>
            <?php if (empty($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </a>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register">
                    <i class="fas fa-user-plus"></i> Inscription
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php endif; ?>
        </div>
        <div class="user-info">
            <span>
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($_SESSION['username'] ?? 'Invité'); ?>
            </span>
        </div>
    </nav>
</header>
