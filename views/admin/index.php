<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>🏠 Tableau de Bord Administrateur</h1>
        <p>Bienvenue dans l'espace d'administration du marketplace</p>
    </div>

    <div class="admin-grid">
        <div class="admin-card">
            <h3>📊 Analyses</h3>
            <p>Consultez les statistiques et analyses du système</p>
            <a href="index.php?controller=admin&action=analytics" class="btn btn-primary">
                <i class="fas fa-chart-line"></i> Voir les Analyses
            </a>
        </div>

        <div class="admin-card">
            <h3>🔧 Debug Système</h3>
            <p>Diagnostic complet du système et des composants</p>
            <a href="index.php?controller=admin&action=debug" class="btn btn-warning">
                <i class="fas fa-bug"></i> Debug Système
            </a>
        </div>

        <div class="admin-card">
            <h3>📦 Produits</h3>
            <p>Gérez les produits et catégories</p>
            <a href="index.php?controller=product&action=index" class="btn btn-success">
                <i class="fas fa-box"></i> Gérer les Produits
            </a>
        </div>

        <div class="admin-card">
            <h3>🏷️ Catégories</h3>
            <p>Créez et gérez les catégories de produits</p>
            <a href="index.php?controller=admin&action=categories" class="btn btn-info">
                <i class="fas fa-tags"></i> Gérer les Catégories
            </a>
        </div>

        <div class="admin-card">
            <h3>👥 Vendeurs & Utilisateurs</h3>
            <p>Gérez tous les utilisateurs et vendeurs</p>
            <a href="index.php?controller=admin&action=vendors" class="btn btn-secondary">
                <i class="fas fa-users"></i> Gérer les Utilisateurs
            </a>
        </div>

        <div class="admin-card">
            <h3>📝 Annonces</h3>
            <p>Modifiez et gérez toutes les annonces</p>
            <a href="index.php?controller=admin&action=ads" class="btn btn-info">
                <i class="fas fa-edit"></i> Gérer les Annonces
            </a>
        </div>

        <div class="admin-card">
            <h3>🎫 Tickets support</h3>
            <p>Consultez et répondez aux tickets des utilisateurs</p>
            <a href="index.php?controller=admin&action=tickets" class="btn btn-primary">
                <i class="fas fa-life-ring"></i> Centre de tickets
            </a>
        </div>

        <div class="admin-card">
            <h3>⚙️ Paramètres</h3>
            <p>Gérez les paramètres du site et les taxes</p>
            <a href="index.php?controller=admin&action=settings" class="btn btn-warning">
                <i class="fas fa-cog"></i> Paramètres du Site
            </a>
        </div>
    </div>
</main>


<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


