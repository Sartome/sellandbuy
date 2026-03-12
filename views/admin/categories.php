<?php $pageTitle = 'Gestion des Catégories'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>🏷️ Gestion des Catégories</h1>
        <p>Créez et gérez les catégories de produits</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">
        <!-- Formulaire de création -->
        <div class="admin-card">
            <h3>➕ Créer une Catégorie</h3>
            <form method="post" action="index.php?controller=admin&action=categories">
                <?php echo Security::csrfField(); ?>
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="libelle">Nom de la catégorie :</label>
                    <input type="text" id="libelle" name="libelle" required placeholder="Ex: Électronique, Vêtements, etc.">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Créer la Catégorie
                </button>
            </form>
        </div>

        <!-- Liste des catégories -->
        <div class="admin-card">
            <h3>📋 Catégories Existantes</h3>
            <?php if (!empty($categories)): ?>
                <div class="categories-list">
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-item">
                            <div class="category-info">
                                <strong><?php echo htmlspecialchars($cat['lib']); ?></strong>
                                <small>ID: <?php echo $cat['id_categorie']; ?></small>
                            </div>
                            <form method="post" action="index.php?controller=admin&action=categories" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                                <?php echo Security::csrfField(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $cat['id_categorie']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-categories">Aucune catégorie trouvée.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-actions">
        <h3>🔗 Actions Rapides</h3>
        <div class="action-buttons">
            <a href="index.php?controller=admin&action=index" class="btn btn-secondary">
                <i class="fas fa-tachometer-alt"></i> Tableau de Bord
            </a>
            <a href="index.php?controller=admin&action=debug" class="btn btn-warning">
                <i class="fas fa-bug"></i> Debug Système
            </a>
            <a href="index.php?controller=product&action=create" class="btn btn-success">
                <i class="fas fa-plus"></i> Créer un Produit
            </a>
        </div>
    </div>
</main>


<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
