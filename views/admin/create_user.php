<?php $pageTitle = 'Créer un utilisateur'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>Créer un utilisateur</h1>
        <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=vendors" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="admin-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="phone">Téléphone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?>">
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
        </div>

        <div class="form-group">
            <label>Rôle</label>
            <div class="role-options">
                <label>
                    <input type="radio" name="role" value="client" <?php echo (($_POST['role'] ?? 'client') === 'client') ? 'checked' : ''; ?>>
                    Client
                </label>
                <label>
                    <input type="radio" name="role" value="vendeur" <?php echo (($_POST['role'] ?? '') === 'vendeur') ? 'checked' : ''; ?>>
                    Vendeur
                </label>
            </div>
        </div>

        <div class="vendor-section" id="vendor-section" style="display: <?php echo (($_POST['role'] ?? 'client') === 'vendeur') ? 'block' : 'none'; ?>;">
            <h3>Informations Vendeur</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nom_entreprise">Nom de l'entreprise</label>
                    <input type="text" id="nom_entreprise" name="nom_entreprise" value="<?php echo htmlspecialchars($_POST['nom_entreprise'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="siret">SIRET</label>
                    <input type="text" id="siret" name="siret" value="<?php echo htmlspecialchars($_POST['siret'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="adresse_entreprise">Adresse de l'entreprise</label>
                    <input type="text" id="adresse_entreprise" name="adresse_entreprise" value="<?php echo htmlspecialchars($_POST['adresse_entreprise'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email_pro">Email professionnel</label>
                    <input type="email" id="email_pro" name="email_pro" value="<?php echo htmlspecialchars($_POST['email_pro'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Créer l'utilisateur
            </button>
            <a href="<?php echo BASE_URL; ?>/index.php?controller=admin&action=vendors" class="btn btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</main>

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

.admin-form {
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
    padding: 24px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid var(--border);
    background: var(--panel);
    color: var(--text);
}

.role-options {
    display: flex;
    gap: 16px;
}

.vendor-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
}

.vendor-section h3 {
    margin-top: 0;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
}

.alert-error {
    background: linear-gradient(90deg, rgba(239,68,68,0.1) 0%, var(--card) 100%);
    border-left: 4px solid var(--danger);
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Afficher/masquer la section vendeur selon le rôle sélectionné
const roleRadios = document.querySelectorAll('input[name="role"]');
const vendorSection = document.getElementById('vendor-section');

roleRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        if (radio.value === 'vendeur' && radio.checked) {
            vendorSection.style.display = 'block';
        } else if (radio.value === 'client' && radio.checked) {
            vendorSection.style.display = 'none';
        }
    });
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
