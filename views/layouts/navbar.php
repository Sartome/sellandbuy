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
        </div>
        <div class="user-info">
            <div class="account-menu">
                <button class="account-toggle" type="button">
                    <div class="account-label">
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <div class="account-avatar">
                                <?php if (!empty($_SESSION['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Avatar" class="account-avatar-img">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Mon compte'); ?></span>
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                            <span>Se connecter</span>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-chevron-down account-chevron"></i>
                </button>
                <div class="account-dropdown">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <?php if (!empty($_SESSION['is_admin'])): ?>
                            <div style="padding: 8px 16px 4px;">
                                <span class="status-badge active">
                                    <i class="fas fa-shield-alt"></i> Administrateur
                                </span>
                            </div>
                            <div class="account-divider"></div>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=account">
                            <i class="fas fa-user-cog"></i> Gestion du compte
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=acquisition&action=index">
                            <i class="fas fa-receipt"></i> Commandes et factures
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=ticket&action=index">
                            <i class="fas fa-life-ring"></i> Centre de tickets
                        </a>
                        <div class="account-divider"></div>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=logout">
                            <i class="fas fa-sign-out-alt"></i> Se déconnecter
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                        <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register">
                            <i class="fas fa-user-plus"></i> Créer un compte
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
