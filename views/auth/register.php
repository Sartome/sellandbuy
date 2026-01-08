<?php $pageTitle = 'Inscription'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h1>🚀 Créer un compte</h1>
            <p>Rejoignez notre marketplace et commencez à vendre ou acheter</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert error" style="animation: shake 0.5s ease-in-out; background: #fee; border: 2px solid #f88; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(204, 51, 51, 0.2);">
                <i class="fas fa-exclamation-triangle" style="margin-right: 8px; color: #c33;"></i>
                <strong>Erreur d'inscription :</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php?controller=auth&action=register" id="register-form">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                <div class="grid two">
                    <div class="form-group">
                        <label for="register-prenom">Prénom *</label>
                        <input type="text" id="register-prenom" name="prenom" autocomplete="given-name" required placeholder="Votre prénom" />
                    </div>
                    <div class="form-group">
                        <label for="register-nom">Nom *</label>
                        <input type="text" id="register-nom" name="nom" autocomplete="family-name" required placeholder="Votre nom" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="register-adresse">Adresse</label>
                    <input type="text" id="register-adresse" name="adresse" autocomplete="street-address" placeholder="Votre adresse complète" />
                </div>
                <div class="form-group">
                    <label for="register-phone">Téléphone</label>
                    <input type="tel" id="register-phone" name="phone" autocomplete="tel" placeholder="Votre numéro de téléphone" />
                </div>
            </div>

            <!-- Informations de connexion -->
            <div class="form-section">
                <h3><i class="fas fa-lock"></i> Informations de connexion</h3>
                <div class="form-group">
                    <label for="register-email">Email *</label>
                    <input type="email" id="register-email" name="email" autocomplete="email" required placeholder="votre@email.com" />
                </div>
                <div class="grid two">
                    <div class="form-group">
                        <label for="register-password">Mot de passe *</label>
                        <input type="password" id="register-password" name="password" autocomplete="new-password" required placeholder="Minimum 6 caractères" minlength="6" />
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    <div class="form-group">
                        <label for="register-password-confirm">Confirmez le mot de passe *</label>
                        <input type="password" id="register-password-confirm" name="password_confirm" autocomplete="new-password" required placeholder="Répétez votre mot de passe" />
                    </div>
                </div>
            </div>

            <!-- Type de compte -->
            <div class="form-section">
                <h3><i class="fas fa-briefcase"></i> Type de compte</h3>
                <div class="role-selection">
                    <div class="role-option">
                        <input type="radio" name="role" value="client" id="role-client" checked />
                        <label for="role-client" class="role-card">
                            <div class="role-icon">🛒</div>
                            <div class="role-content">
                                <h4>Client</h4>
                                <p>Achetez des produits et participez aux enchères</p>
                            </div>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="role" value="vendeur" id="role-vendeur" />
                        <label for="role-vendeur" class="role-card">
                            <div class="role-icon">🏪</div>
                            <div class="role-content">
                                <h4>Vendeur</h4>
                                <p>Vendez vos produits et gérez votre boutique</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Informations vendeur -->
            <fieldset class="vendeur-fields" id="vendeur-fields">
                <legend><i class="fas fa-building"></i> Informations vendeur</legend>
                <div class="form-group">
                    <label>Nom d'entreprise *</label>
                    <input type="text" name="nom_entreprise" placeholder="Nom de votre entreprise" />
                </div>
                <div class="grid two">
                    <div class="form-group">
                        <label>SIRET</label>
                        <input type="text" name="siret" placeholder="Numéro SIRET (14 chiffres)" maxlength="14" />
                    </div>
                    <div class="form-group">
                        <label>Email professionnel</label>
                        <input type="email" name="email_pro" placeholder="contact@votre-entreprise.com" />
                    </div>
                </div>
                <div class="form-group">
                    <label>Adresse entreprise</label>
                    <input type="text" name="adresse_entreprise" placeholder="Adresse de votre entreprise" />
                </div>
            </fieldset>

            <!-- Conditions -->
            <div class="form-section">
                <div class="checkbox-group">
                    <input type="checkbox" id="terms" required />
                    <label for="terms">
                        J'accepte les <a href="#" class="link">conditions d'utilisation</a> et la <a href="#" class="link">politique de confidentialité</a>
                    </label>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-primary btn-large" type="submit">
                    <i class="fas fa-user-plus"></i> Créer mon compte
                </button>
            </div>
        </form>

        <div class="auth-footer">
            <p>Déjà un compte ? <a href="<?php echo BASE_URL; ?>/index.php?controller=auth&action=login" class="link">Connectez-vous</a></p>
        </div>
    </div>
</main>

<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.auth-container {
    max-width: 600px;
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

.form-section h3 {
    margin: 0 0 20px 0;
    color: var(--text);
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section h3 i {
    color: var(--primary);
}

.role-selection {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.role-option input[type="radio"] {
    display: none;
}

.role-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border: 2px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--panel);
}

.role-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(124,58,237,0.15);
}

.role-option input[type="radio"]:checked + .role-card {
    border-color: var(--success);
    background: rgba(16,185,129,0.1);
    box-shadow: 0 8px 25px rgba(16,185,129,0.2);
}

.role-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    border-radius: 50%;
    color: white;
}

.role-option input[type="radio"]:checked + .role-card .role-icon {
    background: var(--success);
}

.role-content h4 {
    margin: 0 0 4px 0;
    color: var(--text);
    font-size: 1.1rem;
}

.role-content p {
    margin: 0;
    color: var(--muted);
    font-size: 0.9rem;
}

.vendeur-fields {
    margin-top: 24px;
    border: 2px dashed var(--border);
    border-radius: var(--radius);
    padding: 20px;
    transition: all 0.3s ease;
    display: none;
}

.vendeur-fields.show {
    display: block;
    border-color: var(--success);
    background: rgba(16,185,129,0.05);
}

.vendeur-fields legend {
    color: var(--success);
    font-weight: bold;
    padding: 0 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.password-strength {
    margin-top: 8px;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.password-strength::after {
    content: '';
    display: block;
    height: 100%;
    width: 0%;
    background: var(--danger);
    transition: all 0.3s ease;
}

.password-strength.weak::after {
    width: 25%;
    background: var(--danger);
}

.password-strength.medium::after {
    width: 50%;
    background: var(--warning);
}

.password-strength.strong::after {
    width: 75%;
    background: var(--success);
}

.password-strength.very-strong::after {
    width: 100%;
    background: var(--success);
}

.checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.checkbox-group input[type="checkbox"] {
    margin-top: 4px;
}

.checkbox-group label {
    color: var(--muted);
    font-size: 0.9rem;
    line-height: 1.4;
}

.link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
}

.link:hover {
    text-decoration: underline;
}

.form-actions {
    text-align: center;
    margin-top: 32px;
}

.btn-large {
    padding: 16px 32px;
    font-size: 1.1rem;
    font-weight: bold;
}

.auth-footer {
    text-align: center;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid var(--border);
}

.auth-footer p {
    color: var(--muted);
    margin: 0;
}

@media (max-width: 640px) {
    .auth-container {
        padding: 20px 16px;
    }
    
    .auth-header h1 {
        font-size: 2rem;
    }
    
    .role-selection {
        grid-template-columns: 1fr;
    }
    
    .role-card {
        flex-direction: column;
        text-align: center;
    }
    
    .form-section {
        padding: 16px;
    }
}
</style>

<script nonce="<?php echo $_SESSION['csp_nonce'] ?? ''; ?>">
document.addEventListener('DOMContentLoaded', function() {
    const roleInputs = document.querySelectorAll('input[name="role"]');
    const vendeurFields = document.getElementById('vendeur-fields');
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordStrength = document.getElementById('password-strength');
    const form = document.getElementById('register-form');

    // Toggle vendeur fields
    roleInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'vendeur') {
                vendeurFields.classList.add('show');
                // Make vendeur fields required
                const vendeurInputs = vendeurFields.querySelectorAll('input');
                vendeurInputs.forEach(input => {
                    if (input.name === 'nom_entreprise') {
                        input.required = true;
                    }
                });
            } else {
                vendeurFields.classList.remove('show');
                // Remove required from vendeur fields
                const vendeurInputs = vendeurFields.querySelectorAll('input');
                vendeurInputs.forEach(input => {
                    input.required = false;
                });
            }
        });
    });

    // Password strength indicator
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        passwordStrength.className = 'password-strength';
        if (password.length > 0) {
            passwordStrength.classList.add(strength);
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const passwordConfirm = document.querySelector('input[name="password_confirm"]').value;
        
        if (password !== passwordConfirm) {
            e.preventDefault();
            showAlert('Les mots de passe ne correspondent pas', 'error');
            return;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            showAlert('Le mot de passe doit contenir au moins 6 caractères', 'error');
            return;
        }
    });

    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 6) score++;
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        if (score < 6) return 'strong';
        return 'very-strong';
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