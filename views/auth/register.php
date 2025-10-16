<?php $pageTitle = 'Inscription'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Inscription</h1>
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="grid two">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" />
            </div>
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" />
            </div>
        </div>
        <div class="form-group">
            <label>Adresse</label>
            <input type="text" name="adresse" />
        </div>
        <div class="form-group">
            <label>Téléphone</label>
            <input type="text" name="phone" />
        </div>
        <div class="grid two">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required />
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required />
            </div>
            <div class="form-group">
                <label>Confirmez le mot de passe</label>
                <input type="password" name="password_confirm" required />
            </div>
        </div>
        <div class="form-group">
            <label>Je suis</label>
            <select name="role">
                <option value="client">Client</option>
                <option value="vendeur">Vendeur</option>
            </select>
        </div>

        <fieldset class="vendeur-fields">
            <legend>Informations vendeur (optionnel si client)</legend>
            <div class="form-group">
                <label>Nom d'entreprise</label>
                <input type="text" name="nom_entreprise" />
            </div>
            <div class="form-group">
                <label>SIRET</label>
                <input type="text" name="siret" />
            </div>
            <div class="form-group">
                <label>Adresse entreprise</label>
                <input type="text" name="adresse_entreprise" />
            </div>
            <div class="form-group">
                <label>Email pro</label>
                <input type="email" name="email_pro" />
            </div>
        </fieldset>

        <button class="btn" type="submit">Créer le compte</button>
    </form>
    <p>Déjà un compte ? <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login">Connectez-vous</a></p>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>