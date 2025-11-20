<?php $pageTitle = 'Gestion des Vendeurs et Utilisateurs'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-section">
        <div class="admin-header">
            <h1>👥 Gestion des Vendeurs et Utilisateurs</h1>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=createUser" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Créer un utilisateur
            </a>
        </div>
        <p>Gérez tous les utilisateurs et vendeurs de votre marketplace</p>
        
        <!-- Barre de recherche -->
        <div class="search-section">
            <form method="GET" action="index.php" class="search-form">
                <input type="hidden" name="controller" value="admin">
                <input type="hidden" name="action" value="vendors">
                
                <div class="search-input-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Rechercher par nom, email, téléphone ou entreprise..." 
                           class="search-input">
                    
                    <select name="type" class="search-type">
                        <option value="all" <?php echo ($type ?? 'all') === 'all' ? 'selected' : ''; ?>>Tous</option>
                        <option value="vendors" <?php echo ($type ?? '') === 'vendors' ? 'selected' : ''; ?>>Vendeurs uniquement</option>
                        <option value="users" <?php echo ($type ?? '') === 'users' ? 'selected' : ''; ?>>Utilisateurs uniquement</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <?php if (!empty($search)): ?>
                        <a href="index.php?controller=admin&action=vendors" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
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

    <!-- Section Vendeurs -->
    <div class="admin-section">
        <h2>🏪 Vendeurs (<?php echo count($vendors ?? []); ?>)</h2>
        
        <?php if (empty($vendors ?? [])): ?>
            <div class="no-results">
                <i class="fas fa-store"></i>
                <p>Aucun vendeur trouvé</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Entreprise</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($vendors ?? []) as $vendor): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($vendor['prenom'] . ' ' . $vendor['nom']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($vendor['email']); ?></td>
                                <td><?php echo htmlspecialchars($vendor['nom_entreprise'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($vendor['is_certified'] ?? false): ?>
                                        <span class="badge certified">
                                            <i class="fas fa-check-circle"></i> Certifié
                                        </span>
                                    <?php else: ?>
                                        <span class="badge not-certified">
                                            <i class="fas fa-times-circle"></i> Non certifié
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($vendor['is_certified'] ?? false): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="uncertify">
                                                <input type="hidden" name="vendor_id" value="<?php echo (int)$vendor['id_user']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm" 
                                                        onclick="return confirm('Retirer la certification ?')">
                                                    <i class="fas fa-times"></i> Décertifier
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="certify">
                                                <input type="hidden" name="vendor_id" value="<?php echo (int)$vendor['id_user']; ?>">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Certifier
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_vendor">
                                            <input type="hidden" name="vendor_id" value="<?php echo (int)$vendor['id_user']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('⚠️ ATTENTION: Supprimer ce vendeur supprimera aussi tous ses produits et enchères ! Cette action est irréversible !')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section Utilisateurs -->
    <div class="admin-section">
        <h2>👤 Utilisateurs (<?php echo count($users ?? []); ?>)</h2>
        
        <?php if (empty($users ?? [])): ?>
            <div class="no-results">
                <i class="fas fa-users"></i>
                <p>Aucun utilisateur trouvé</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Type</th>
                            <th>Inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($users ?? []) as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($user['type'] === 'Administrateur'): ?>
                                        <span class="badge certified">
                                            <i class="fas fa-shield-alt"></i> Administrateur
                                        </span>
                                    <?php elseif ($user['type'] === 'Vendeur'): ?>
                                        <span class="badge certified">
                                            <i class="fas fa-store"></i> Vendeur
                                        </span>
                                    <?php else: ?>
                                        <span class="badge not-certified">
                                            <i class="fas fa-user"></i> Utilisateur
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?php echo (int)$user['id_user']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('⚠️ ATTENTION: Supprimer cet utilisateur supprimera aussi toutes ses données (produits, enchères, participations, etc.) ! Cette action est irréversible !')">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin: 2rem 0 1.5rem;
}

.admin-header h1 {
    margin: 0;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

.search-section {
    background: var(--card);
    padding: 20px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
    margin-bottom: 30px;
}

.search-form {
    width: 100%;
}

.search-input-group {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 300px;
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    background: var(--panel);
    color: var(--text);
    font-size: 16px;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
}

.search-type {
    padding: 12px 16px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    background: var(--panel);
    color: var(--text);
    font-size: 16px;
    min-width: 150px;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: var(--muted);
    background: var(--panel);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.no-results i {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

.alert {
    padding: 15px;
    margin: 15px 0;
    border-radius: 6px;
    border-left: 4px solid;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: linear-gradient(90deg, rgba(16,185,129,0.1) 0%, var(--card) 100%);
    border-color: var(--success);
    color: var(--text);
}

.alert-error {
    background: linear-gradient(90deg, rgba(239,68,68,0.1) 0%, var(--card) 100%);
    border-color: var(--danger);
    color: var(--text);
}

@media (max-width: 768px) {
    .search-input-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-input {
        min-width: auto;
    }
    
    .search-type {
        min-width: auto;
    }
}
</style>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>