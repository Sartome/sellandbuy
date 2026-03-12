<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>🗺️ Diagramme ER</h1>

    <p>Le diagramme ci-dessous est généré dynamiquement depuis la base de données actuelle.</p>

    <div style="margin:16px 0;">
        <a class="btn btn-primary" href="index.php?controller=admin&action=downloadDiagram">Télécharger SVG</a>
        <a class="btn btn-secondary" href="#" onclick="window.location.reload(); return false;">Actualiser</a>

        <form method="post" action="index.php?controller=admin&action=saveDiagram" style="display:inline; margin-left:8px">
            <?php echo Security::csrfField(); ?>
            <button type="submit" class="btn btn-success" onclick="return confirm('Enregistrer le diagramme ER dans database/er_diagram.svg ?')">Enregistrer dans database/er_diagram.svg</button>
        </form>
    </div>

    <?php if (!empty($savedInfo)): ?>
        <div style="margin:8px 0; padding:8px; background:#f7f7f7; border:1px solid #e0e0e0">
            <strong>Fichier sauvegardé :</strong>
            <div>Chemin: <code><?= htmlspecialchars($savedInfo['path']) ?></code></div>
            <div>Dernière modification: <?= htmlspecialchars($savedInfo['mtime']) ?> — <?= round($savedInfo['size'] / 1024) ?> KB</div>
        </div>
    <?php endif; ?>

    <div class="er-diagram" style="border:1px solid #ddd; padding:10px; overflow:auto; background:#fff;">
        <?php if (!empty($svg)): ?>
            <?php echo $svg; ?>
        <?php else: ?>
            <p>Aucun schéma disponible.</p>
        <?php endif; ?>
    </div>

    <p class="muted">Note: le rendu est basique mais exportable. Si vous souhaitez un rendu plus sophistiqué, je peux ajouter options pour le layout ou une export PNG via Imagick.</p>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>