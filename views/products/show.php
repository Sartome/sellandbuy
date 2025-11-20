<?php 
require_once MODELS_PATH . '/ProduitImage.php';
require_once MODELS_PATH . '/Review.php';
require_once MODELS_PATH . '/Sale.php';

$produitImageModel = new ProduitImage();
$productImages = $produitImageModel->getImagesByProduct((int)$product['id_produit']);

$reviewModel = new Review();
$reviews = $reviewModel->getByProduct((int)$product['id_produit']);
$averageRating = $reviewModel->getAverageForProduct((int)$product['id_produit']);

$userCanReview = false;
$userHasReviewed = false;
if (!empty($_SESSION['user_id'])) {
    $saleModel = new Sale();
    $userId = (int)$_SESSION['user_id'];
    $userCanReview = $saleModel->userHasPurchasedProduct($userId, (int)$product['id_produit']);
    $userHasReviewed = $reviewModel->userHasReviewed((int)$product['id_produit'], $userId);
}
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

            <div class="review-summary">
                <?php if ($averageRating > 0): ?>
                    <?php $rounded = (int)round($averageRating); ?>
                    <span class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $rounded ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </span>
                    <span class="review-count">
                        (<?php echo count($reviews); ?> avis, moyenne <?php echo number_format($averageRating, 1, ',', ' '); ?>/5)
                    </span>
                <?php else: ?>
                    <span class="review-count">Aucun avis pour le moment</span>
                <?php endif; ?>
            </div>

            <div class="seller-info">
                <div class="seller-card">
                    <div class="seller-avatar">
                        <?php if (!empty($product['avatar'])): ?>
                            <img src="<?php echo htmlspecialchars($product['avatar']); ?>" alt="Photo du vendeur">
                        <?php else: ?>
                            <i class="fas fa-store"></i>
                        <?php endif; ?>
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

    <div class="product-reviews">
        <h2>Avis des acheteurs</h2>

        <?php if (!empty($reviews)): ?>
            <ul class="review-list">
                <?php foreach ($reviews as $review): ?>
                    <li class="review-item">
                        <div class="review-header">
                            <?php 
                                $name = trim(($review['prenom'] ?? '') . ' ' . ($review['nom'] ?? ''));
                                if ($name === '') {
                                    $name = 'Client';
                                }
                            ?>
                            <strong><?php echo htmlspecialchars($name); ?></strong>
                            <span class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php echo $i <= (int)$review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </span>
                            <?php if (!empty($review['created_at'])): ?>
                                <span class="review-date"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="no-reviews">Aucun avis pour l'instant.</p>
        <?php endif; ?>

        <?php if ($userCanReview): ?>
            <div class="review-form-wrapper">
                <?php if ($userHasReviewed): ?>
                    <p>Vous avez déjà laissé un avis. Vous pouvez le mettre à jour ci-dessous.</p>
                <?php endif; ?>
                <form method="post" action="<?php echo BASE_URL; ?>/index.php?controller=review&action=create" class="review-form">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id_produit']; ?>">
                    <div class="form-group">
                        <label>Votre note</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star-<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?>>
                                <label for="star-<?php echo $i; ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Votre commentaire</label>
                        <textarea name="comment" rows="3" placeholder="Partagez votre expérience..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-star"></i> Envoyer mon avis
                    </button>
                </form>
            </div>
        <?php elseif (!empty($_SESSION['user_id'])): ?>
            <p class="no-review-permission">Vous devez acheter ce produit pour laisser un avis.</p>
        <?php else: ?>
            <p class="no-review-permission">Connectez-vous pour laisser un avis.</p>
        <?php endif; ?>
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


