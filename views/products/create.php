<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Vendre un produit</h1>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="product-form" data-loading>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" required placeholder="Décrivez votre produit en détail..."></textarea>
        </div>
        
        <!-- Type de vente -->
        <div class="form-group">
            <label>Type de vente</label>
            <div class="sale-type-selector">
                <label class="sale-type-option">
                    <input type="radio" name="sale_type" value="buy" checked>
                    <div class="option-content">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>Achat direct</h4>
                        <p>Vente immédiate à prix fixe</p>
                    </div>
                </label>
                <label class="sale-type-option">
                    <input type="radio" name="sale_type" value="auction">
                    <div class="option-content">
                        <i class="fas fa-gavel"></i>
                        <h4>Enchère</h4>
                        <p>Mise aux enchères avec temps limite</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Prix fixe (pour achat direct) -->
        <div class="form-group" id="fixed-price-group">
            <label>Prix HT (€)</label>
            <input type="number" id="prix_ht" name="prix_ht" min="0" step="0.01" placeholder="0.00" oninput="updateTTC()" />
            
            <div style="margin: 10px 0;">
                <?php if (!empty($defaultTaxEnabled)): ?>
                    <div class="tax-info">
                        <span class="tax-name"><?php echo htmlspecialchars($defaultTaxName ?? 'TVA'); ?></span>
                        <span class="tax-rate-value"><?php echo number_format($defaultTaxRate ?? 0, 2, ',', ' '); ?>%</span>
                        <span class="tax-note">(taux défini par l'administration)</span>
                    </div>
                    <input type="hidden" id="taux_tva" name="taux_tva" value="<?php echo htmlspecialchars((string)($defaultTaxRate ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                <?php else: ?>
                    <div class="tax-info">
                        <span class="tax-name">Aucune taxe appliquée</span>
                        <span class="tax-note">(les taxes sont désactivées par l'administration)</span>
                    </div>
                    <input type="hidden" id="taux_tva" name="taux_tva" value="0">
                <?php endif; ?>
            </div>
            
            <label>Prix TTC (€)</label>
            <input type="number" id="prix_ttc" name="prix_ttc" min="0" step="0.01" placeholder="0.00" oninput="updateHT()" />
            <input type="hidden" id="prix" name="prix" value="0" />
        </div>

        <!-- Prix de départ (pour enchère) -->
        <div class="form-group" id="auction-price-group" style="display: none;">
            <label>Prix de départ (€)</label>
            <input type="number" name="starting_price" min="0" step="0.01" placeholder="0.00" />
        </div>

        <!-- Date de fin d'enchère -->
        <div class="form-group" id="auction-end-group" style="display: none;">
            <label>Fin de l'enchère</label>
            <input type="datetime-local" name="auction_end" />
            <small>L'enchère se terminera automatiquement à cette date</small>
        </div>

        <!-- Quantité disponible -->
        <div class="form-group">
            <label>Quantité disponible</label>
            <input type="number" name="quantity" min="1" value="1" required />
            <small>Nombre d'articles disponibles à la vente</small>
        </div>

        <!-- Section Upload d'Images -->
        <div class="form-group">
            <label>Images du produit</label>
            <div class="image-upload-container">
                <div class="upload-area" id="upload-area">
                    <div class="upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Glissez-déposez vos images ici ou cliquez pour sélectionner</p>
                        <small>Formats acceptés: JPEG, PNG, WebP, GIF (max 5MB par image)</small>
                    </div>
                    <input type="file" name="images[]" id="image-input" multiple accept="image/*" style="display: none;">
                </div>
                
                <!-- Recommandations de taille -->
                <div class="size-recommendations">
                    <h4>📏 Recommandations de taille</h4>
                    <div class="recommendations-grid">
                        <div class="recommendation-item">
                            <strong>Thumbnail:</strong> <?php echo $sizeRecommendations['recommendedSizes']['thumbnail']['width']; ?>x<?php echo $sizeRecommendations['recommendedSizes']['thumbnail']['height']; ?>px
                        </div>
                        <div class="recommendation-item">
                            <strong>Moyenne:</strong> <?php echo $sizeRecommendations['recommendedSizes']['medium']['width']; ?>x<?php echo $sizeRecommendations['recommendedSizes']['medium']['height']; ?>px
                        </div>
                        <div class="recommendation-item">
                            <strong>Grande:</strong> <?php echo $sizeRecommendations['recommendedSizes']['large']['width']; ?>x<?php echo $sizeRecommendations['recommendedSizes']['large']['height']; ?>px
                        </div>
                    </div>
                    <div class="tips">
                        <?php foreach ($sizeRecommendations['tips'] as $tip): ?>
                            <div class="tip">💡 <?php echo htmlspecialchars($tip); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Prévisualisation des images -->
                <div class="image-preview-container" id="image-preview-container" style="display: none;">
                    <h4>📸 Aperçu des images</h4>
                    <div class="image-preview-grid" id="image-preview-grid"></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Texte alternatif pour les images</label>
            <input type="text" name="image_alt" placeholder="Description courte des images pour l'accessibilité" />
        </div>

        <div class="form-group">
            <label>Catégorie</label>
            <select name="id_categorie">
                <?php foreach (($categories ?? []) as $cat): ?>
                    <option value="<?php echo (int)$cat['id_categorie']; ?>">
                        <?php echo htmlspecialchars($cat['lib']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">
                <i class="fas fa-plus"></i> Publier le produit
            </button>
            <button class="btn btn-secondary" type="button" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Annuler
            </button>
        </div>
    </form>
</main>

<style>
/* Styles pour le sélecteur de type de vente */
.sale-type-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin: 16px 0;
}

.sale-type-option {
    cursor: pointer;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    transition: all 0.3s ease;
    background: var(--panel);
}

.sale-type-option:hover {
    border-color: var(--primary);
    background: rgba(124,58,237,0.05);
    transform: translateY(-2px);
}

.sale-type-option input[type="radio"] {
    display: none;
}

.sale-type-option input[type="radio"]:checked + .option-content {
    color: var(--primary);
}

.sale-type-option input[type="radio"]:checked {
    border-color: var(--primary);
    background: rgba(124,58,237,0.1);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
}

.option-content {
    text-align: center;
    transition: all 0.3s ease;
}

.option-content i {
    font-size: 2.5rem;
    margin-bottom: 12px;
    color: var(--muted);
    transition: all 0.3s ease;
}

.sale-type-option input[type="radio"]:checked + .option-content i {
    color: var(--primary);
}

.option-content h4 {
    margin: 8px 0 4px 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.option-content p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--muted);
}

.sale-type-option input[type="radio"]:checked + .option-content p {
    color: var(--text);
}

/* Styles pour les champs conditionnels */
.form-group small {
    display: block;
    margin-top: 4px;
    font-size: 0.85rem;
    color: var(--muted);
}

.image-upload-container {
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    padding: 20px;
    background: linear-gradient(110deg, rgba(255,255,255,0.02) 0%, rgba(255,255,255,0) 100%);
    transition: all 0.3s ease;
}

.image-upload-container:hover {
    border-color: var(--primary);
    background: linear-gradient(110deg, rgba(124,58,237,0.05) 0%, rgba(124,58,237,0.02) 100%);
}

.upload-area {
    cursor: pointer;
    text-align: center;
    padding: 40px 20px;
    border-radius: var(--radius);
    transition: all 0.3s ease;
}

.upload-area:hover {
    background: rgba(124,58,237,0.05);
    transform: translateY(-2px);
}

.upload-content i {
    font-size: 3rem;
    color: var(--primary);
    margin-bottom: 16px;
    animation: pulse 2s infinite;
}

.upload-content p {
    font-size: 1.1rem;
    margin-bottom: 8px;
    color: var(--text);
}

.upload-content small {
    color: var(--muted);
    font-size: 0.9rem;
}

.size-recommendations {
    margin-top: 24px;
    padding: 16px;
    background: rgba(16,185,129,0.05);
    border: 1px solid rgba(16,185,129,0.2);
    border-radius: var(--radius);
}

.size-recommendations h4 {
    margin: 0 0 12px 0;
    color: var(--success);
    font-size: 1rem;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.recommendation-item {
    padding: 8px 12px;
    background: rgba(16,185,129,0.1);
    border-radius: 6px;
    font-size: 0.9rem;
    color: var(--text);
}

.tips {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tip {
    font-size: 0.85rem;
    color: var(--muted);
    padding: 4px 0;
}

.tax-info {
    display: inline-flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: baseline;
    padding: 6px 10px;
    border-radius: 6px;
    background: rgba(124,58,237,0.06);
    border: 1px solid rgba(124,58,237,0.35);
    font-size: 0.9rem;
}

.tax-info .tax-name {
    font-weight: 600;
}

.tax-info .tax-rate-value {
    font-weight: 600;
    color: var(--primary);
}

.tax-info .tax-note {
    color: var(--muted);
}

.image-preview-container {
    margin-top: 20px;
    padding: 16px;
    background: rgba(255,255,255,0.02);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.image-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-top: 12px;
}

.image-preview-item {
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--panel-2);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.image-preview-item:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(124,58,237,0.3);
}

.image-preview-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}

.image-preview-item .remove-btn {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(239,68,68,0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.image-preview-item .remove-btn:hover {
    background: var(--danger);
    transform: scale(1.1);
}

.image-preview-item .primary-badge {
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

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-secondary {
    background: linear-gradient(180deg, var(--muted) 0%, #6b7280 100%);
    box-shadow: 0 6px 14px rgba(107,114,128,0.35);
}

.btn-secondary:hover {
    box-shadow: 0 10px 22px rgba(107,114,128,0.4);
}

@media (max-width: 640px) {
    .recommendations-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .image-preview-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>

<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
// Fonction pour mettre à jour le TTC en fonction du HT
function updateTTC() {
    const ht = parseFloat(document.getElementById('prix_ht').value) || 0;
    const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 0;
    const ttc = ht * (1 + (tauxTVA / 100));
    document.getElementById('prix_ttc').value = ttc.toFixed(2);
    document.getElementById('prix').value = ttc.toFixed(2); // Store TTC as the main price
}

// Fonction pour mettre à jour le HT en fonction du TTC
function updateHT() {
    const ttc = parseFloat(document.getElementById('prix_ttc').value) || 0;
    const tauxTVA = parseFloat(document.getElementById('taux_tva').value) || 0;
    const ht = ttc / (1 + (tauxTVA / 100));
    document.getElementById('prix_ht').value = ht.toFixed(2);
    document.getElementById('prix').value = ttc.toFixed(2); // Store TTC as the main price
}

document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const imageInput = document.getElementById('image-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewGrid = document.getElementById('image-preview-grid');
    const form = document.getElementById('product-form');
    
    let selectedFiles = [];
    let primaryImageIndex = 0;

    // Gestion du type de vente
    const saleTypeRadios = document.querySelectorAll('input[name="sale_type"]');
    const fixedPriceGroup = document.getElementById('fixed-price-group');
    const auctionPriceGroup = document.getElementById('auction-price-group');
    const auctionEndGroup = document.getElementById('auction-end-group');
    
    // Fonction pour gérer l'affichage des champs selon le type de vente
    function handleSaleTypeChange() {
        const selectedType = document.querySelector('input[name="sale_type"]:checked').value;
        
        if (selectedType === 'buy') {
            fixedPriceGroup.style.display = 'block';
            auctionPriceGroup.style.display = 'none';
            auctionEndGroup.style.display = 'none';
            
            // Rendre le champ prix obligatoire
            document.querySelector('input[name="prix"]').required = true;
            document.querySelector('input[name="starting_price"]').required = false;
            document.querySelector('input[name="auction_end"]').required = false;
        } else if (selectedType === 'auction') {
            fixedPriceGroup.style.display = 'none';
            auctionPriceGroup.style.display = 'block';
            auctionEndGroup.style.display = 'block';
            
            // Rendre les champs d'enchère obligatoires
            document.querySelector('input[name="prix"]').required = false;
            document.querySelector('input[name="starting_price"]').required = true;
            document.querySelector('input[name="auction_end"]').required = true;
            
            // Définir la date minimum (maintenant + 1 heure)
            const now = new Date();
            now.setHours(now.getHours() + 1);
            const minDateTime = now.toISOString().slice(0, 16);
            document.querySelector('input[name="auction_end"]').min = minDateTime;
        }
    }
    
    // Écouter les changements de type de vente
    saleTypeRadios.forEach(radio => {
        radio.addEventListener('change', handleSaleTypeChange);
    });
    
    // Initialiser l'affichage
    handleSaleTypeChange();

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--primary)';
        uploadArea.style.background = 'rgba(124,58,237,0.1)';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--border)';
        uploadArea.style.background = 'rgba(255,255,255,0.02)';
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.style.borderColor = 'var(--border)';
        uploadArea.style.background = 'rgba(255,255,255,0.02)';
        
        const files = Array.from(e.dataTransfer.files);
        handleFiles(files);
    });

    // Click to select files
    uploadArea.addEventListener('click', function() {
        imageInput.click();
    });

    imageInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        handleFiles(files);
    });

    function handleFiles(files) {
        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length === 0) {
            showAlert('Veuillez sélectionner des fichiers image valides.', 'error');
            return;
        }

        // Validate file sizes
        const maxSize = 5 * 1024 * 1024; // 5MB
        const oversizedFiles = imageFiles.filter(file => file.size > maxSize);
        
        if (oversizedFiles.length > 0) {
            showAlert(`Certains fichiers sont trop volumineux (max 5MB). Fichiers ignorés: ${oversizedFiles.map(f => f.name).join(', ')}`, 'error');
        }

        const validFiles = imageFiles.filter(file => file.size <= maxSize);
        selectedFiles = [...selectedFiles, ...validFiles];
        
        updatePreview();
        updateFileInput();
    }

    function updatePreview() {
        if (selectedFiles.length === 0) {
            previewContainer.style.display = 'none';
            return;
        }

        previewContainer.style.display = 'block';
        previewGrid.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'image-preview-item';
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>
                    ${index === primaryImageIndex ? '<div class="primary-badge">PRINCIPALE</div>' : ''}
                `;
                
                previewItem.addEventListener('click', function(e) {
                    if (e.target.tagName !== 'BUTTON') {
                        setPrimaryImage(index);
                    }
                });
                
                previewGrid.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        });
    }

    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => dt.items.add(file));
        imageInput.files = dt.files;
    }

    function removeImage(index) {
        selectedFiles.splice(index, 1);
        
        if (primaryImageIndex >= index && primaryImageIndex > 0) {
            primaryImageIndex--;
        } else if (primaryImageIndex >= selectedFiles.length) {
            primaryImageIndex = Math.max(0, selectedFiles.length - 1);
        }
        
        updatePreview();
        updateFileInput();
    }

    function setPrimaryImage(index) {
        primaryImageIndex = index;
        updatePreview();
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            showAlert('Veuillez sélectionner au moins une image pour votre produit.', 'error');
            return;
        }
    });

    function showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.textContent = message;
        
        const container = document.querySelector('.container');
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    // Make functions globally available
    window.removeImage = removeImage;
    window.setPrimaryImage = setPrimaryImage;
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


