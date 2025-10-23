<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="breadcrumb">
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">Produits</a>
        <span>›</span>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo $productId; ?>"><?php echo htmlspecialchars($product['description']); ?></a>
        <span>›</span>
        <span>Ajouter des images</span>
    </div>

    <h1>Ajouter des images</h1>
    <p class="subtitle">Ajoutez des images supplémentaires à votre produit</p>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="add-images-form" data-loading>
        <!-- Section Upload d'Images -->
        <div class="form-group">
            <label>Nouvelles images</label>
            <div class="image-upload-container">
                <div class="upload-area" id="upload-area">
                    <div class="upload-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Glissez-déposez vos nouvelles images ici ou cliquez pour sélectionner</p>
                        <small>Formats acceptés: JPEG, PNG, WebP, GIF (max 5MB par image)</small>
                    </div>
                    <input type="file" name="images[]" id="image-input" multiple accept="image/*" style="display: none;">
                </div>
                
                <!-- Recommandations de taille -->
                <div class="size-recommendations">
                    <h4>📏 Recommandations de taille</h4>
                    <div class="recommendations-grid">
                        <div class="recommendation-item">
                            <strong>Thumbnail:</strong> 300x300px
                        </div>
                        <div class="recommendation-item">
                            <strong>Moyenne:</strong> 800x600px
                        </div>
                        <div class="recommendation-item">
                            <strong>Grande:</strong> 1200x900px
                        </div>
                    </div>
                    <div class="tips">
                        <div class="tip">💡 Pour de meilleures performances, utilisez des images de 800x600px maximum</div>
                        <div class="tip">💡 Les formats WebP et JPEG offrent le meilleur compromis qualité/taille</div>
                        <div class="tip">💡 Évitez les images trop lourdes pour améliorer le temps de chargement</div>
                    </div>
                </div>

                <!-- Prévisualisation des images -->
                <div class="image-preview-container" id="image-preview-container" style="display: none;">
                    <h4>📸 Aperçu des nouvelles images</h4>
                    <div class="image-preview-grid" id="image-preview-grid"></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Texte alternatif pour les nouvelles images</label>
            <input type="text" name="image_alt" placeholder="Description courte des images pour l'accessibilité" />
        </div>

        <div class="form-actions">
            <button class="btn" type="submit">
                <i class="fas fa-plus"></i> Ajouter les images
            </button>
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=show&id=<?php echo $productId; ?>">
                <i class="fas fa-arrow-left"></i> Retour au produit
            </a>
        </div>
    </form>

    <!-- Images existantes -->
    <?php if (!empty($existingImages)): ?>
        <div class="existing-images">
            <h3>Images actuelles</h3>
            <div class="existing-images-grid">
                <?php foreach ($existingImages as $index => $image): ?>
                    <div class="existing-image-item">
                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($image['image_alt'] ?? ''); ?>">
                        <?php if ($image['is_primary']): ?>
                            <div class="primary-badge">⭐ PRINCIPALE</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
.subtitle {
    color: var(--muted);
    margin-bottom: 24px;
    font-size: 1.1rem;
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

.existing-images {
    margin-top: 40px;
    padding: 20px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
}

.existing-images h3 {
    margin: 0 0 16px 0;
    color: var(--text);
}

.existing-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 12px;
}

.existing-image-item {
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
    border: 1px solid var(--border);
}

.existing-image-item img {
    width: 100%;
    height: 100px;
    object-fit: cover;
    display: block;
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
    
    .existing-images-grid {
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.getElementById('upload-area');
    const imageInput = document.getElementById('image-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewGrid = document.getElementById('image-preview-grid');
    const form = document.getElementById('add-images-form');
    
    let selectedFiles = [];

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
                `;
                
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
        updatePreview();
        updateFileInput();
    }

    // Form validation
    form.addEventListener('submit', function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            showAlert('Veuillez sélectionner au moins une image à ajouter.', 'error');
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
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
