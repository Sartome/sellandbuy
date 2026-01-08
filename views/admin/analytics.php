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



<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js" nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>"></script>
<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
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

// Les fonctions d'administration sont maintenant dans app.js
</script>



