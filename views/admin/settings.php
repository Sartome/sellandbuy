<?php $pageTitle = 'Gestion des Taxes'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>💰 Gestion des Taxes</h1>
        <p>Configurez et gérez toutes les taxes du marketplace</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Liste des taxes existantes -->
    <div class="admin-section">
        <h2>📋 Liste des Taxes (<?php echo count($allTaxes ?? []); ?>)</h2>
        
        <?php if (empty($allTaxes)): ?>
            <div class="no-results">
                <p>Aucune taxe personnalisée trouvée.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Taux</th>
                            <th>Description</th>
                            <th>Créée le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($allTaxes ?? []) as $tax): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tax['tax_name'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($tax['tax_rate'] ?? 0, 2); ?>%</td>
                                <td><?php echo htmlspecialchars($tax['tax_description'] ?? 'Aucune description'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($tax['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_tax">
                                        <input type="hidden" name="tax_id" value="<?php echo (int)$tax['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette taxe ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Configuration des taxes par défaut -->
    <div class="admin-section">
        <h2>⚙️ Configuration des Taxes par Défaut</h2>
        
        <form method="post" action="index.php?controller=admin&action=settings">
            <input type="hidden" name="action" value="update_taxes">
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="tax_enabled" <?php echo $currentSettings['tax_enabled'] ? 'checked' : ''; ?>>
                    Activer les taxes par défaut
                </label>
            </div>
            
            <div class="form-group">
                <label for="tax_name">Nom de la taxe par défaut :</label>
                <input type="text" id="tax_name" name="tax_name" value="<?php echo htmlspecialchars($currentSettings['tax_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="tax_rate">Taux de taxe par défaut (%) :</label>
                <input type="number" id="tax_rate" name="tax_rate" value="<?php echo $currentSettings['tax_rate']; ?>" 
                       step="0.01" min="0" max="100" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour les paramètres
            </button>
        </form>
    </div>

    <!-- Ajouter une nouvelle taxe -->
    <div class="admin-section">
        <h2>➕ Ajouter une Nouvelle Taxe</h2>
        
        <form method="post" action="index.php?controller=admin&action=settings">
            <input type="hidden" name="action" value="add_tax">
            
            <div class="form-group">
                <label for="new_tax_name">Nom de la taxe :</label>
                <input type="text" id="new_tax_name" name="tax_name" placeholder="Ex: TVA, Taxe locale, etc." required>
            </div>
            
            <div class="form-group">
                <label for="new_tax_rate">Taux (%) :</label>
                <input type="number" id="new_tax_rate" name="tax_rate" step="0.01" min="0" max="100" required>
            </div>
            
            <div class="form-group">
                <label for="tax_description">Description (optionnel) :</label>
                <textarea id="tax_description" name="tax_description" placeholder="Description de cette taxe..."></textarea>
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-plus"></i> Ajouter la taxe
            </button>
        </form>
    </div>

    <!-- Aperçu des calculs -->
    <div class="admin-section">
        <h2>🧮 Aperçu des Calculs</h2>
        <div class="tax-preview">
            <div class="preview-item">
                <label>Prix HT :</label>
                <input type="number" id="preview_amount" value="100" step="0.01" min="0">
                <span>€</span>
            </div>
            
            <div class="preview-results">
                <div class="result-item">
                    <span>Taxe par défaut (<?php echo $currentSettings['tax_name']; ?>) :</span>
                    <span id="preview_tax"><?php echo number_format($currentSettings['tax_rate'], 2); ?>%</span>
                </div>
                <div class="result-item">
                    <span>Montant de la taxe :</span>
                    <span id="preview_tax_amount">0.00 €</span>
                </div>
                <div class="result-item total">
                    <span>Prix TTC :</span>
                    <span id="preview_total">100.00 €</span>
                </div>
            </div>
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
            <a href="index.php?controller=admin&action=analytics" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Analyses
            </a>
        </div>
    </div>
</main>

<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
// Mise à jour en temps réel de l'aperçu
document.addEventListener('DOMContentLoaded', function() {
    const taxRateInput = document.getElementById('tax_rate');
    const taxNameInput = document.getElementById('tax_name');
    const previewAmount = document.getElementById('preview_amount');
    const previewTax = document.getElementById('preview_tax');
    const previewTaxAmount = document.getElementById('preview_tax_amount');
    const previewTotal = document.getElementById('preview_total');
    
    function updatePreview() {
        const rate = parseFloat(taxRateInput.value) || 0;
        const name = taxNameInput.value || 'TVA';
        const amount = parseFloat(previewAmount.value) || 0;
        const taxAmount = amount * rate / 100;
        const total = amount + taxAmount;
        
        previewTax.textContent = rate.toFixed(2) + '%';
        previewTaxAmount.textContent = taxAmount.toFixed(2) + ' €';
        previewTotal.textContent = total.toFixed(2) + ' €';
    }
    
    taxRateInput.addEventListener('input', updatePreview);
    taxNameInput.addEventListener('input', updatePreview);
    previewAmount.addEventListener('input', updatePreview);
    
    // Initialiser l'aperçu
    updatePreview();
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>