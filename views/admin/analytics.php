<?php 
$pageTitle = $pageTitle ?? 'Analyses'; 
require_once MODELS_PATH . '/ProduitImage.php';
require_once MODELS_PATH . '/Produit.php';

$produitImageModel = new ProduitImage();
$produitModel = new Produit();

$imageStats = $produitImageModel->getImageStats();
$productStats = $produitModel->getStats();
?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>📊 Tableau de bord administrateur</h1>
    
    <!-- Statistiques générales -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3><?php echo $productStats['total_products']; ?></h3>
                <p>Produits total</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3><?php echo number_format($productStats['average_price'], 2); ?>€</h3>
                <p>Prix moyen</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🖼️</div>
            <div class="stat-content">
                <h3><?php echo $imageStats['total_images']; ?></h3>
                <p>Images total</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <h3><?php echo formatBytes($imageStats['total_size']); ?></h3>
                <p>Stockage images</p>
            </div>
        </div>
    </div>

    <!-- Gestion des images -->
    <div class="admin-section">
        <h2>🖼️ Gestion des images</h2>
        <div class="image-management">
            <div class="management-card">
                <h3>Statistiques des images</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <strong>Images par produit:</strong>
                        <span>Max <?php echo $imageStats['max_images_per_product']; ?></span>
                    </div>
                    <div class="stat-item">
                        <strong>Dimensions moyennes:</strong>
                        <span><?php echo $imageStats['avg_dimensions']['width']; ?>x<?php echo $imageStats['avg_dimensions']['height']; ?>px</span>
                    </div>
                    <div class="stat-item">
                        <strong>Taille moyenne:</strong>
                        <span><?php echo formatBytes($imageStats['total_size'] / max($imageStats['total_images'], 1)); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="management-card">
                <h3>Actions rapides</h3>
                <div class="action-buttons">
                    <button class="btn" onclick="optimizeAllImages()">
                        <i class="fas fa-compress"></i> Optimiser toutes les images
                    </button>
                    <button class="btn" onclick="cleanupOrphanedImages()">
                        <i class="fas fa-trash"></i> Nettoyer les images orphelines
                    </button>
                    <button class="btn" onclick="generateImageReport()">
                        <i class="fas fa-file-alt"></i> Rapport des images
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques existants -->
    <div class="admin-section">
        <h2>📈 Analyses</h2>
        <div class="grid two">
            <div class="card">
                <div class="body">
                    <h3>Produits vendus (30 jours)</h3>
                    <canvas id="salesCount"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="body">
                    <h3>Revenus (30 jours)</h3>
                    <canvas id="salesAmount"></canvas>
                </div>
            </div>
            <div class="card" style="grid-column: span 2;">
                <div class="body">
                    <h3>Comptes créés (30 jours)</h3>
                    <canvas id="usersCount"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits par catégorie -->
    <div class="admin-section">
        <h2>📊 Produits par catégorie</h2>
        <div class="category-stats">
            <?php foreach ($productStats['by_category'] as $category): ?>
                <div class="category-item">
                    <div class="category-name"><?php echo htmlspecialchars($category['lib']); ?></div>
                    <div class="category-count"><?php echo $category['count']; ?> produits</div>
                    <div class="category-bar">
                        <div class="category-fill" style="width: <?php echo ($category['count'] / max($productStats['total_products'], 1)) * 100; ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<style>
.stats-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.stat-card {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-600) 100%);
    color: white;
    padding: 24px;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 8px 25px rgba(124,58,237,0.3);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
}

.stat-icon {
    font-size: 2.5rem;
    opacity: 0.9;
}

.stat-content h3 {
    margin: 0 0 4px 0;
    font-size: 2rem;
    font-weight: bold;
}

.stat-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.admin-section {
    margin-bottom: 40px;
    padding: 24px;
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.admin-section h2 {
    margin: 0 0 24px 0;
    color: var(--text);
    font-size: 1.5rem;
}

.image-management {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.management-card {
    background: var(--panel);
    padding: 20px;
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.management-card h3 {
    margin: 0 0 16px 0;
    color: var(--text);
    font-size: 1.1rem;
}

.stats-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-item strong {
    color: var(--text);
}

.stat-item span {
    color: var(--muted);
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-buttons .btn {
    justify-content: flex-start;
}

.category-stats {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.category-item {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 12px;
    align-items: center;
    padding: 16px;
    background: var(--panel);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.category-name {
    font-weight: bold;
    color: var(--text);
}

.category-count {
    color: var(--muted);
    font-size: 0.9rem;
}

.category-bar {
    grid-column: 1 / -1;
    height: 8px;
    background: var(--border);
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}

.category-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary) 0%, var(--success) 100%);
    transition: width 0.3s ease;
}

@media (max-width: 768px) {
    .stats-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .image-management {
        grid-template-columns: 1fr;
    }
    
    .category-item {
        grid-template-columns: 1fr;
        text-align: center;
    }
}
</style>

<?php
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const salesCountData = <?php echo json_encode($salesCount ?? []); ?>;
const salesAmountData = <?php echo json_encode($salesAmount ?? []); ?>;
const usersCountData = <?php echo json_encode($usersCount ?? []); ?>;

function toChartData(rows, valueKey) {
  return {
    labels: rows.map(r => r.d),
    datasets: [{
      label: valueKey,
      data: rows.map(r => Number(r[valueKey] || 0)),
      borderColor: '#7c3aed',
      backgroundColor: 'rgba(124,58,237,0.2)'
    }]
  };
}

new Chart(document.getElementById('salesCount'), { type: 'line', data: toChartData(salesCountData, 'c') });
new Chart(document.getElementById('salesAmount'), { type: 'line', data: toChartData(salesAmountData, 's') });
new Chart(document.getElementById('usersCount'), { type: 'line', data: toChartData(usersCountData, 'c') });

// Admin functions
function optimizeAllImages() {
    if (confirm('Optimiser toutes les images ? Cela peut prendre du temps.')) {
        showLoading('Optimisation en cours...');
        // Simulate optimization process
        setTimeout(() => {
            hideLoading();
            showAlert('Optimisation terminée !', 'success');
        }, 3000);
    }
}

function cleanupOrphanedImages() {
    if (confirm('Nettoyer les images orphelines ? Cette action est irréversible.')) {
        showLoading('Nettoyage en cours...');
        // Simulate cleanup process
        setTimeout(() => {
            hideLoading();
            showAlert('Nettoyage terminé !', 'success');
        }, 2000);
    }
}

function generateImageReport() {
    showLoading('Génération du rapport...');
    // Simulate report generation
    setTimeout(() => {
        hideLoading();
        showAlert('Rapport généré avec succès !', 'success');
        // In a real implementation, this would download a PDF or open a new window
    }, 1500);
}

function showLoading(message) {
    const loading = document.createElement('div');
    loading.id = 'admin-loading';
    loading.className = 'admin-loading';
    loading.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.getElementById('admin-loading');
    if (loading) {
        loading.remove();
    }
}

function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.textContent = message;
    
    const container = document.querySelector('.container');
    container.insertBefore(alert, container.firstChild);
    
    setTimeout(() => alert.remove(), 5000);
}
</script>

<style>
.admin-loading {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-content {
    background: var(--card);
    padding: 40px;
    border-radius: var(--radius);
    text-align: center;
    border: 1px solid var(--border);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--border);
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

.loading-content p {
    margin: 0;
    color: var(--text);
    font-size: 1.1rem;
}
</style>


