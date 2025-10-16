<?php $pageTitle = 'Connexion'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required />
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" required />
        </div>
        <button class="btn" type="submit">Se connecter</button>
    </form>
    <p>Pas de compte ? <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register">Inscrivez-vous</a></p>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>