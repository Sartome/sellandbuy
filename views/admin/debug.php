<?php $pageTitle = 'Debug Système'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <div class="admin-header">
        <h1>🔧 Debug Système</h1>
        <p>Diagnostic complet du système pour les administrateurs</p>
    </div>

    <div class="debug-stats">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($testResults); ?></div>
            <div class="stat-label">Tests Exécutés</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count(array_filter($testResults, function($r) { return $r['status'] === 'success'; })); ?></div>
            <div class="stat-label">Succès</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count(array_filter($testResults, function($r) { return $r['status'] === 'error'; })); ?></div>
            <div class="stat-label">Erreurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo count(array_filter($testResults, function($r) { return $r['status'] === 'warning'; })); ?></div>
            <div class="stat-label">Avertissements</div>
        </div>
    </div>

    <?php
    // Fonction pour afficher les résultats
    function displayResults() {
        global $testResults;
        
        $categories = array_unique(array_column($testResults, 'category'));
        
        foreach ($categories as $category) {
            echo "<div class='debug-category'>";
            echo "<h2>🔍 $category</h2>";
            
            $categoryTests = array_filter($testResults, function($test) use ($category) {
                return $test['category'] === $category;
            });
            
            foreach ($categoryTests as $test) {
                $icon = $test['status'] === 'success' ? '✅' : ($test['status'] === 'warning' ? '⚠️' : '❌');
                $class = $test['status'] === 'success' ? 'success' : ($test['status'] === 'warning' ? 'warning' : 'error');
                
                echo "<div class='debug-result $class'>";
                echo "<span class='icon'>$icon</span>";
                echo "<span class='test-name'>{$test['test']}</span>";
                if ($test['message']) {
                    echo "<span class='test-message'>: {$test['message']}</span>";
                }
                echo "</div>";
            }
            
            echo "</div>";
        }
    }
    
    displayResults();
    ?>

    <div class="debug-actions">
        <h3>🔧 Actions Administrateur</h3>
        <div class="action-buttons">
            <a href="index.php?controller=admin&action=index" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Tableau de Bord
            </a>
            <a href="index.php?controller=admin&action=analytics" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Analyses
            </a>
            <a href="index.php?controller=product&action=index" class="btn btn-success">
                <i class="fas fa-box"></i> Produits
            </a>
            <a href="index.php?controller=auth&action=login" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </a>
        </div>
    </div>

    <?php if (!empty($routeTests)): ?>
        <div class="route-tests">
            <h3>🧪 Tests de pages principales</h3>
            <table class="route-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>URL</th>
                        <th>Statut HTTP</th>
                        <th>Résultat</th>
                        <th>Temps</th>
                        <th>Message PHP (aperçu)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routeTests as $route): ?>
                        <?php
                            $rowClass = '';
                            if (!empty($route['skipped'])) {
                                $rowClass = 'skipped';
                            } elseif ($route['ok'] === true) {
                                $rowClass = 'ok';
                            } elseif ($route['ok'] === false) {
                                $rowClass = 'fail';
                            }
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($route['name']); ?></td>
                            <td><code><?php echo htmlspecialchars($route['url']); ?></code></td>
                            <td><?php echo $route['status_code'] ?? '—'; ?></td>
                            <td>
                                <?php if (!empty($route['skipped'])): ?>
                                    <span class="badge badge-muted">Non testé</span>
                                <?php elseif ($route['ok']): ?>
                                    <span class="badge badge-success">OK</span>
                                <?php else: ?>
                                    <span class="badge badge-error">Erreur</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($route['duration_ms'])): ?>
                                    <?php echo (int)$route['duration_ms']; ?> ms
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="route-message">
                                <?php if (!empty($route['error_snippet'])): ?>
                                    <code><?php echo htmlspecialchars($route['error_snippet']); ?><?php echo strlen($route['error_snippet']) >= 300 ? '…' : ''; ?></code>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="debug-info">
        <h3>📊 Informations Système</h3>
        <div class="info-grid">
            <div class="info-item">
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?>
            </div>
            <div class="info-item">
                <strong>Serveur:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu'; ?>
            </div>
            <div class="info-item">
                <strong>Base de données:</strong> <?php echo DB_NAME; ?>
            </div>
            <div class="info-item">
                <strong>Session active:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Oui' : 'Non'; ?>
            </div>
            <div class="info-item">
                <strong>Utilisateur connecté:</strong> <?php echo $_SESSION['username'] ?? 'Non connecté'; ?>
            </div>
            <div class="info-item">
                <strong>Rôle:</strong> <?php echo $_SESSION['is_admin'] ? 'Administrateur' : 'Utilisateur'; ?>
            </div>
        </div>
    </div>
</main>

<style>
.debug-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: var(--card);
    padding: 20px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    text-align: center;
    border: 1px solid var(--border);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-number {
    font-size: 2em;
    font-weight: bold;
    color: var(--primary);
}

.stat-label {
    color: var(--muted);
    margin-top: 5px;
}

.debug-category {
    margin: 20px 0;
    padding: 20px;
    border-radius: var(--radius);
    background: var(--panel);
    border: 1px solid var(--border);
    border-left: 4px solid var(--primary);
}

.debug-category h2 {
    color: var(--text);
    margin: 0 0 15px 0;
    font-size: 1.2rem;
}

.debug-result {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    margin: 8px 0;
    border-radius: 6px;
    background: var(--card);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
}

.debug-result:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.debug-result.success {
    border-left: 4px solid var(--success);
    background: linear-gradient(90deg, rgba(16,185,129,0.1) 0%, var(--card) 100%);
}

.debug-result.warning {
    border-left: 4px solid var(--warning);
    background: linear-gradient(90deg, rgba(245,158,11,0.1) 0%, var(--card) 100%);
}

.debug-result.error {
    border-left: 4px solid var(--danger);
    background: linear-gradient(90deg, rgba(239,68,68,0.1) 0%, var(--card) 100%);
}

.icon {
    margin-right: 12px;
    font-size: 18px;
}

.test-name {
    font-weight: 600;
    margin-right: 12px;
    color: var(--text);
}

.test-message {
    color: var(--muted);
    font-style: italic;
}

.debug-actions {
    margin: 30px 0;
    padding: 25px;
    background: var(--panel);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.debug-actions h3 {
    color: var(--text);
    margin: 0 0 20px 0;
}

.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 15px;
}

.btn {
    background: var(--primary);
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    background: var(--primary-600);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124,58,237,0.3);
}

.btn-primary { background: var(--primary); }
.btn-info { background: var(--info); }
.btn-success { background: var(--success); }
.btn-secondary { background: var(--muted); }

.debug-info {
    margin: 30px 0;
    padding: 25px;
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.debug-info h3 {
    color: var(--text);
    margin: 0 0 20px 0;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.info-item {
    padding: 15px;
    background: var(--panel);
    border-radius: 6px;
    border-left: 3px solid var(--primary);
    transition: all 0.3s ease;
}

.info-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.info-item strong {
    color: var(--text);
    font-weight: 600;
}

.info-item {
    color: var(--muted);
}

.route-tests {
    margin: 30px 0;
    padding: 25px;
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--border);
}

.route-tests h3 {
    color: var(--text);
    margin: 0 0 16px 0;
}

.route-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.route-table th,
.route-table td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--border);
    text-align: left;
    vertical-align: middle;
}

.route-table .route-message {
    max-width: 360px;
    white-space: normal;
    word-break: break-word;
}

.route-table tr.ok {
    background: rgba(16,185,129,0.05);
}

.route-table tr.fail {
    background: rgba(239,68,68,0.05);
}

.route-table tr.skipped {
    background: rgba(148,163,184,0.05);
}

.badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: rgba(16,185,129,0.15);
    color: #10b981;
}

.badge-error {
    background: rgba(239,68,68,0.15);
    color: #ef4444;
}

.badge-muted {
    background: rgba(148,163,184,0.15);
    color: #9ca3af;
}
</style>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>
