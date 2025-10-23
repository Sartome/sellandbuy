<?php $pageTitle = 'Paramètres du Site'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>⚙️ Paramètres du Site</h1>
        <p>Gérez les paramètres généraux du marketplace</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="admin-grid">
        <!-- Gestion des taxes -->
        <div class="admin-card">
            <h3>💰 Gestion des Taxes</h3>
            <form method="post" action="index.php?controller=admin&action=settings">
                <input type="hidden" name="action" value="update_taxes">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="tax_enabled" <?php echo $currentSettings['tax_enabled'] ? 'checked' : ''; ?>>
                        Activer les taxes
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="tax_name">Nom de la taxe :</label>
                    <input type="text" id="tax_name" name="tax_name" value="<?php echo htmlspecialchars($currentSettings['tax_name']); ?>" placeholder="Ex: TVA, Tax, etc.">
                </div>
                
                <div class="form-group">
                    <label for="tax_rate">Taux de taxe (%) :</label>
                    <input type="number" id="tax_rate" name="tax_rate" step="0.01" min="0" max="100" value="<?php echo $currentSettings['tax_rate']; ?>" placeholder="Ex: 20.00">
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Sauvegarder les paramètres
                </button>
            </form>
        </div>

        <!-- Aperçu des calculs -->
        <div class="admin-card">
            <h3>📊 Aperçu des Calculs</h3>
            <div class="tax-preview">
                <div class="preview-item">
                    <strong>Prix HT :</strong>
                    <span id="preview-ht">100.00 €</span>
                </div>
                <div class="preview-item">
                    <strong>Taxe (<?php echo htmlspecialchars($currentSettings['tax_name']); ?>) :</strong>
                    <span id="preview-tax"><?php echo number_format($currentSettings['tax_rate'], 2); ?>%</span>
                </div>
                <div class="preview-item total">
                    <strong>Prix TTC :</strong>
                    <span id="preview-ttc"><?php echo number_format(100 + (100 * $currentSettings['tax_rate'] / 100), 2); ?> €</span>
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


<script>
// Mise à jour en temps réel de l'aperçu
document.addEventListener('DOMContentLoaded', function() {
    const taxRateInput = document.getElementById('tax_rate');
    const taxNameInput = document.getElementById('tax_name');
    const previewTax = document.getElementById('preview-tax');
    const previewTtc = document.getElementById('preview-ttc');
    
    function updatePreview() {
        const rate = parseFloat(taxRateInput.value) || 0;
        const name = taxNameInput.value || 'TVA';
        const htPrice = 100;
        const ttcPrice = htPrice + (htPrice * rate / 100);
        
        previewTax.textContent = rate.toFixed(2) + '%';
        previewTtc.textContent = ttcPrice.toFixed(2) + ' €';
    }
    
    taxRateInput.addEventListener('input', updatePreview);
    taxNameInput.addEventListener('input', updatePreview);
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
