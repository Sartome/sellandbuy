<?php 
require_once MODELS_PATH . '/ProduitImage.php';
$produitImageModel = new ProduitImage();
$productImages = $produitImageModel->getImagesByProduct((int)$product['id_produit']);
?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">Produits</a>
        <span>›</span>
        <span><?php echo htmlspecialchars($product['description']); ?></span>
    </div>

    <div class="product-detail">
        <!-- Galerie d'images améliorée -->
        <div class="image-gallery">
            <?php if (!empty($productImages)): ?>
                <div class="main-image-container">
                    <img id="main-image" src="<?php echo htmlspecialchars($productImages[0]['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($productImages[0]['image_alt'] ?? $product['description']); ?>"
                         onclick="openLightbox(0)">
                    <div class="image-actions">
                        <button class="action-btn" onclick="openLightbox(0)" title="Voir en grand">
                            <i class="fas fa-expand"></i>
                        </button>
                        <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$product['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                            <button class="action-btn" onclick="addMoreImages()" title="Ajouter des images">
                                <i class="fas fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (count($productImages) > 1): ?>
                    <div class="thumbnail-grid">
                        <?php foreach ($productImages as $index => $image): ?>
                            <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 onclick="changeMainImage(<?php echo $index; ?>)">
                                <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($image['image_alt'] ?? ''); ?>">
                                <?php if ($image['is_primary']): ?>
                                    <div class="primary-badge">⭐</div>
                                <?php endif; ?>
                                <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$product['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                                    <div class="thumbnail-actions">
                                        <button class="thumb-action" onclick="setPrimaryImage(<?php echo $image['id_image']; ?>, <?php echo $product['id_produit']; ?>)" title="Définir comme principale">
                                            <i class="fas fa-star"></i>
                                        </button>
                                        <button class="thumb-action danger" onclick="deleteImage(<?php echo $image['id_image']; ?>, <?php echo $product['id_produit']; ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-image">
                    <i class="fas fa-image"></i>
                    <p>Aucune image disponible</p>
                    <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$product['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                        <button class="btn" onclick="addMoreImages()">
                            <i class="fas fa-plus"></i> Ajouter des images
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="product-header">
                <h1><?php echo htmlspecialchars($product['description']); ?></h1>
                <div class="product-meta">
                    <span class="category"><?php echo htmlspecialchars($product['categorie'] ?? 'Non catégorisé'); ?></span>
                    <span class="date">Publié le <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></span>
                </div>
            </div>

            <div class="price-section">
                <div class="price-main money-amount"><?php echo number_format((float)$product['prix'], 2, ',', ' '); ?> €</div>
                <div class="price-details">
                    <small class="price-small">Prix TTC</small>
                </div>
            </div>

            <div class="seller-info">
                <div class="seller-card">
                    <div class="seller-avatar">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="seller-details">
                        <h3><?php echo htmlspecialchars($product['nom_entreprise'] ?? 'Vendeur'); ?></h3>
                        <p>Vendeur vérifié</p>
                    </div>
                </div>
            </div>

            <div class="product-actions">
                <?php if (!empty($_SESSION['user_id']) && (int)($_SESSION['user_id']) !== (int)$product['id_vendeur']): ?>
                    <a class="btn btn-buy btn-large" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=buy&id=<?php echo (int)$product['id_produit']; ?>">
                        <i class="fas fa-shopping-cart"></i> Acheter maintenant
                    </a>
                    <div class="secondary-actions">
                        <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=prepurchase&action=create&id=<?php echo (int)$product['id_produit']; ?>">
                            <i class="fas fa-clock"></i> Pré-commander
                        </a>
                        <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=view&product_id=<?php echo (int)$product['id_produit']; ?>">
                            <i class="fas fa-gavel"></i> Enchères
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($_SESSION['user_id']) && ((int)$_SESSION['user_id'] === (int)$product['id_vendeur'] || !empty($_SESSION['is_admin']))): ?>
                    <div class="owner-actions">
                        <a class="btn btn-danger" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=delete&id=<?php echo (int)$product['id_produit']; ?>" 
                           onclick="return confirm('Supprimer ce produit ?');">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                        <a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=auction&action=create&product_id=<?php echo (int)$product['id_produit']; ?>">
                            <i class="fas fa-gavel"></i> Créer une enchère
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Informations supplémentaires -->
            <div class="product-details">
                <h3>Détails du produit</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <strong>ID Produit:</strong>
                        <span>#<?php echo $product['id_produit']; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Catégorie:</strong>
                        <span><?php echo htmlspecialchars($product['categorie'] ?? 'Non définie'); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Vendeur:</strong>
                        <span><?php echo htmlspecialchars($product['nom_entreprise'] ?? 'Non défini'); ?></span>
                    </div>
                    <?php if (!empty($productImages)): ?>
                        <div class="detail-item">
                            <strong>Images:</strong>
                            <span><?php echo count($productImages); ?> image(s)</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox pour les images -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox()">
        <div class="lightbox-content" onclick="event.stopPropagation()">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <button class="lightbox-nav prev" onclick="previousImage()">&lt;</button>
            <button class="lightbox-nav next" onclick="nextImage()">&gt;</button>
            <img id="lightbox-image" src="" alt="">
            <div class="lightbox-info">
                <span id="lightbox-counter"></span>
            </div>
        </div>
    </div>
</main>

<style>
.breadcrumb {
    margin-bottom: 20px;
    font-size: 0.9rem;
    color: var(--muted);
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
}

.breadcrumb span {
    margin: 0 8px;
}

.product-detail {
    display: grid;
    grid-template-columns: 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.image-gallery {
    background: var(--card);
    border-radius: var(--radius);
    padding: 20px;
    border: 1px solid var(--border);
}

.main-image-container {
    position: relative;
    margin-bottom: 20px;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--panel-2);
}

.main-image-container img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.main-image-container img:hover {
    transform: scale(1.02);
}

.image-actions {
    position: absolute;
    top: 12px;
    right: 12px;
    display: flex;
    gap: 8px;
}

.action-btn {
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.action-btn:hover {
    background: rgba(0,0,0,0.9);
    transform: scale(1.1);
}

.thumbnail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 12px;
}

.thumbnail {
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    aspect-ratio: 1;
}

.thumbnail:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
}

.thumbnail.active {
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(124,58,237,0.3);
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.thumbnail-actions {
    position: absolute;
    top: 4px;
    right: 4px;
    display: flex;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.thumbnail:hover .thumbnail-actions {
    opacity: 1;
}

.thumb-action {
    background: rgba(0,0,0,0.7);
    color: white;
    border: none;
    border-radius: 4px;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.thumb-action.danger {
    background: rgba(239,68,68,0.8);
}

.thumb-action:hover {
    background: rgba(0,0,0,0.9);
}

.thumb-action.danger:hover {
    background: var(--danger);
}

.primary-badge {
    position: absolute;
    bottom: 4px;
    left: 4px;
    background: var(--success);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: bold;
}

.no-image {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}

.no-image i {
    font-size: 4rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.product-header h1 {
    margin: 0 0 12px 0;
    font-size: 2rem;
    line-height: 1.2;
}

.product-meta {
    display: flex;
    gap: 16px;
    font-size: 0.9rem;
    color: var(--muted);
}

.category {
    background: var(--primary);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.price-section {
    background: linear-gradient(135deg, var(--money) 0%, var(--money-dark) 100%);
    color: white;
    padding: 24px;
    border-radius: var(--radius);
    text-align: center;
    box-shadow: 0 8px 25px rgba(16,185,129,0.3);
    position: relative;
    overflow: hidden;
}

.price-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.price-main {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}

.price-details {
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.seller-info {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
}

.seller-card {
    display: flex;
    align-items: center;
    gap: 16px;
}

.seller-avatar {
    width: 60px;
    height: 60px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.seller-details h3 {
    margin: 0 0 4px 0;
    font-size: 1.1rem;
}

.seller-details p {
    margin: 0;
    color: var(--success);
    font-size: 0.9rem;
}

.product-actions {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.btn-large {
    padding: 16px 24px;
    font-size: 1.1rem;
    font-weight: bold;
}

.secondary-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.owner-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.product-details {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
}

.product-details h3 {
    margin: 0 0 16px 0;
    font-size: 1.1rem;
}

.details-grid {
    display: grid;
    gap: 12px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
}

.detail-item:last-child {
    border-bottom: none;
}

/* Lightbox */
.lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: white;
    font-size: 2rem;
    cursor: pointer;
    z-index: 1001;
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 1.5rem;
    padding: 12px 16px;
    cursor: pointer;
    border-radius: 4px;
}

.lightbox-nav.prev {
    left: -60px;
}

.lightbox-nav.next {
    right: -60px;
}

.lightbox-nav:hover {
    background: rgba(255,255,255,0.3);
}

#lightbox-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
}

.lightbox-info {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    font-size: 0.9rem;
}

@media (min-width: 900px) {
    .product-detail {
        grid-template-columns: 1.2fr 1fr;
        align-items: start;
    }
}

@media (max-width: 640px) {
    .product-header h1 {
        font-size: 1.5rem;
    }
    
    .price-main {
        font-size: 2rem;
    }
    
    .secondary-actions,
    .owner-actions {
        flex-direction: column;
    }
    
    .lightbox-nav.prev {
        left: 10px;
    }
    
    .lightbox-nav.next {
        right: 10px;
    }
}
</style>

<script>
let currentImageIndex = 0;
let productImages = <?php echo json_encode($productImages); ?>;

function changeMainImage(index) {
    if (index >= 0 && index < productImages.length) {
        currentImageIndex = index;
        const mainImage = document.getElementById('main-image');
        const thumbnails = document.querySelectorAll('.thumbnail');
        
        mainImage.src = productImages[index].image_path;
        mainImage.alt = productImages[index].image_alt || '<?php echo addslashes($product['description']); ?>';
        
        thumbnails.forEach((thumb, i) => {
            thumb.classList.toggle('active', i === index);
        });
    }
}

function openLightbox(index) {
    if (productImages.length === 0) return;
    
    currentImageIndex = index;
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxCounter = document.getElementById('lightbox-counter');
    
    lightboxImage.src = productImages[index].image_path;
    lightboxImage.alt = productImages[index].image_alt || '<?php echo addslashes($product['description']); ?>';
    lightboxCounter.textContent = `${index + 1} / ${productImages.length}`;
    
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function previousImage() {
    if (productImages.length <= 1) return;
    currentImageIndex = (currentImageIndex - 1 + productImages.length) % productImages.length;
    openLightbox(currentImageIndex);
}

function nextImage() {
    if (productImages.length <= 1) return;
    currentImageIndex = (currentImageIndex + 1) % productImages.length;
    openLightbox(currentImageIndex);
}

function addMoreImages() {
    window.location.href = '<?php echo BASE_URL; ?>/index.php?controller=product&action=addImages&id=<?php echo $product['id_produit']; ?>';
}

function setPrimaryImage(imageId, productId) {
    if (confirm('Définir cette image comme principale ?')) {
        window.location.href = `<?php echo BASE_URL; ?>/index.php?controller=product&action=setPrimaryImage&image_id=${imageId}&product_id=${productId}`;
    }
}

function deleteImage(imageId, productId) {
    if (confirm('Supprimer cette image ? Cette action est irréversible.')) {
        window.location.href = `<?php echo BASE_URL; ?>/index.php?controller=product&action=deleteImage&image_id=${imageId}&product_id=${productId}`;
    }
}

// Keyboard navigation for lightbox
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (lightbox.style.display === 'flex') {
        switch(e.key) {
            case 'Escape':
                closeLightbox();
                break;
            case 'ArrowLeft':
                previousImage();
                break;
            case 'ArrowRight':
                nextImage();
                break;
        }
    }
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


