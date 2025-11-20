<?php $pageTitle = $pageTitle ?? 'Gestion des Annonces'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>Gestion des Annonces</h1>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=create" class="btn btn-success">
            <i class="fas fa-plus"></i> Ajouter une annonce
        </a>
    </div>
    
    <?php if (!empty($message)): ?>
        <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Barre de recherche -->
    <div class="search-section">
        <form method="GET" action="index.php" class="search-form">
            <input type="hidden" name="controller" value="admin">
            <input type="hidden" name="action" value="ads">
            
            <div class="search-filters">
                <div class="search-input-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" 
                           placeholder="Rechercher par description, vendeur, nom..." 
                           class="search-input">
                    
                    <select name="category" class="search-filter">
                        <option value="">Toutes les catégories</option>
                        <?php foreach (($categories ?? []) as $cat): ?>
                            <option value="<?php echo $cat['id_categorie']; ?>" 
                                    <?php echo ($categoryFilter ?? '') == $cat['id_categorie'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['lib']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="number" name="price_min" value="<?php echo htmlspecialchars($priceMin ?? ''); ?>" 
                           placeholder="Prix min" class="search-filter" step="0.01" min="0">
                    
                    <input type="number" name="price_max" value="<?php echo htmlspecialchars($priceMax ?? ''); ?>" 
                           placeholder="Prix max" class="search-filter" step="0.01" min="0">
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Rechercher
                    </button>
                    
                    <?php if (!empty($search) || !empty($categoryFilter) || !empty($priceMin) || !empty($priceMax)): ?>
                        <a href="index.php?controller=admin&action=ads" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="admin-section">
        <h2>Liste des Annonces (<?php echo count($products ?? []); ?>)</h2>
        
        <?php if (empty($products)): ?>
            <div class="card">
                <div class="body">
                    <p>Aucune annonce trouvée.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Catégorie</th>
                            <th>Type</th>
                            <th>Vendeur</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo (int)$product['id_produit']; ?></td>
                                <td>
                                    <div class="product-description">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 50)); ?>
                                        <?php if (strlen($product['description']) > 50): ?>...<?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo number_format((float)$product['prix'], 2, ',', ' '); ?> €</td>
                                <td>
                                    <?php 
                                    $categoryName = 'N/A';
                                    foreach ($categories as $cat) {
                                        if ($cat['id_categorie'] == $product['id_categorie']) {
                                            $categoryName = $cat['lib'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($categoryName);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Vérifier si c'est une enchère
                                    $pdo = Database::getInstance()->getConnection();
                                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM auctions WHERE id_produit = ? AND status = 'active'");
                                    $stmt->execute([$product['id_produit']]);
                                    $isAuction = $stmt->fetchColumn() > 0;
                                    
                                    if ($isAuction) {
                                        echo '<span class="type-badge auction">🏷️ Enchère</span>';
                                    } else {
                                        echo '<span class="type-badge sale">💰 Vente</span>';
                                    }
                                    ?>
                                </td>
                                <td>ID: <?php echo (int)$product['id_vendeur']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm" onclick="editProduct(<?php echo (int)$product['id_produit']; ?>, '<?php echo htmlspecialchars($product['description'], ENT_QUOTES); ?>', <?php echo (float)$product['prix']; ?>, <?php echo (int)$product['id_categorie']; ?>)">
                                            <i class="fas fa-edit"></i> Modifier
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette annonce ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo (int)$product['id_produit']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
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

<!-- Modal de modification -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier l'Annonce</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="product_id" id="edit_product_id">
            
            <div class="form-group">
                <label for="edit_description">Description :</label>
                <textarea id="edit_description" name="description" required rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="edit_prix">Prix (€) :</label>
                <input type="number" id="edit_prix" name="prix" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="edit_categorie">Catégorie :</label>
                <select id="edit_categorie" name="id_categorie" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id_categorie']; ?>">
                            <?php echo htmlspecialchars($cat['lib']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

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

.admin-section {
    margin: 2rem 0;
}

.table-container {
    overflow-x: auto;
    margin: 1rem 0;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-table th,
.admin-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.admin-table tr:hover {
    background: #f8f9fa;
}

.product-description {
    max-width: 200px;
    word-wrap: break-word;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.875rem;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.75rem;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
}

/* Modal styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close {
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    color: #aaa;
}

.close:hover {
    color: #000;
}

.modal form {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Type badges */
.type-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.type-badge.auction {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fbbf24;
}

.type-badge.sale {
    background: #d1fae5;
    color: #059669;
    border: 1px solid #34d399;
}
</style>

<script>
function editProduct(id, description, prix, categorie) {
    document.getElementById('edit_product_id').value = id;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_prix').value = prix;
    document.getElementById('edit_categorie').value = categorie;
    document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Fermer le modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeModal();
    }
}
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
