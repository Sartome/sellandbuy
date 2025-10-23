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
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?controller=auth&action=login" id="login-form" data-validate>
            <div class="form-section">
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required placeholder="votre@email.com" />
                </div>
                <div class="form-group">
                    <label>Mot de passe *</label>
                    <input type="password" name="password" required placeholder="Votre mot de passe" />
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

<style>
.auth-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 40px 20px;
}

.auth-header {
    text-align: center;
    margin-bottom: 40px;
}

.auth-header h1 {
    margin: 0 0 12px 0;
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--primary) 0%, var(--success) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.auth-header p {
    color: var(--muted);
    font-size: 1.1rem;
    margin: 0;
}

.form-section {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--text);
    font-weight: 500;
}

.form-group input {
    width: 100%;
    padding: 12px 16px;
    background: var(--panel);
    color: var(--text);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    outline: none;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.form-group input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
    background: var(--panel-2);
}

.form-group input::placeholder {
    color: var(--muted);
}

.form-actions {
    text-align: center;
    margin-top: 32px;
}

.btn-large {
    padding: 16px 32px;
    font-size: 1.1rem;
    font-weight: bold;
    width: 100%;
}

.auth-footer {
    text-align: center;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.auth-footer p {
    color: var(--muted);
    margin: 8px 0;
}

.link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.link:hover {
    text-decoration: underline;
}

@media (max-width: 640px) {
    .auth-container {
        padding: 20px 16px;
    }
    
    .auth-header h1 {
        font-size: 2rem;
    }
    
    .form-section {
        padding: 16px;
    }
}
</style>

<script>
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

    function showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        
        const container = document.querySelector('.auth-container');
        container.insertBefore(alert, container.firstChild);
        
        setTimeout(() => alert.remove(), 5000);
    }
});
</script>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>