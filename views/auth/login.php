<?php $pageTitle = 'Connexion'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>🔐 Connexion</h1>
            <p>Accédez à votre compte et gérez vos produits</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Erreur de connexion :</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?controller=auth&action=login" id="login-form">
            <?php echo Security::csrfField(); ?>
            <div class="form-section">
                <div class="form-group">
                    <label for="login-email">Email *</label>
                    <input type="email" id="login-email" name="email" autocomplete="email" required placeholder="votre@email.com" />
                </div>
                <div class="form-group">
                    <label for="login-password">Mot de passe *</label>
                    <input type="password" id="login-password" name="password" autocomplete="current-password" required placeholder="Votre mot de passe" />
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary btn-large" type="submit">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Pas de compte ? <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=register" class="link">Créez-en un</a></p>
            <p><small>Besoin d'aide ? <a href="#" class="link">Contactez le support</a></small></p>
        </div>
    </div>
</main>


<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('login-form');
    
    form.addEventListener('submit', function(e) {
        const email = document.querySelector('input[name="email"]').value;
        const password = document.querySelector('input[name="password"]').value;
        
        if (!email || !password) {
            e.preventDefault();
            showAlert('Veuillez remplir tous les champs', 'error');
            return;
        }
        
        if (!isValidEmail(email)) {
            e.preventDefault();
            showAlert('Veuillez entrer une adresse email valide', 'error');
            return;
        }
    });

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showAlert(message, type) {
        type = type || 'info';
        const alert = document.createElement('div');
        alert.className = 'alert ' + type;
        
        const icon = document.createElement('i');
        icon.className = 'fas fa-exclamation-triangle';
        alert.appendChild(icon);
        alert.appendChild(document.createTextNode(' ' + message));
        
        const container = document.querySelector('.auth-container');
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(function() { alert.remove(); }, 5000);
    }
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>