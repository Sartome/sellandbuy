<?php $pageTitle = $pageTitle ?? 'Mon compte'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<div class="container">
    <div class="account-form">
        <h1><i class="fas fa-user-circle"></i> Mon compte</h1>
        
        <div class="profile-avatar-wrapper">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Photo de profil" class="profile-avatar">
            <?php else: ?>
                <div class="profile-avatar placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" enctype="multipart/form-data" data-validate>
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="adresse">Adresse :</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Téléphone :</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="avatar">Photo de profil :</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
            </div>
            
            <?php if (!empty($user['avatar'])): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="delete_avatar" value="1">
                    Supprimer la photo de profil actuelle
                </label>
            </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
