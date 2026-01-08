<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>⚠️ Signalements</h1>

    <?php if (empty($signals)) : ?>
        <p>Aucun signalement pour le moment.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produit</th>
                    <th>Signalé par</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signals as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['id_signal']) ?></td>
                        <td>
                            <?= htmlspecialchars($s['description'] ?? '—') ?>
                            <?php if (!empty($s['id_produit'])): ?>
                                <br>
                                <a href="index.php?controller=product&action=show&id=<?= (int)$s['id_produit'] ?>">Voir le produit</a>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?></td>
                        <td><?= htmlspecialchars($s['date_signal']) ?></td>
                        <td>
                            <form method="post" action="index.php?controller=admin&action=deleteSignal" style="display:inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($s['id_signal']) ?>">
                                <button class="btn btn-danger" onclick="return confirm('Supprimer le signalement ?')">Supprimer</button>
                            </form>

                            <?php if (!empty($s['id_vendeur'])): ?>
                                <a class="btn btn-warning" href="index.php?controller=admin&action=blockVendor&vendor_id=<?= (int)$s['id_vendeur'] ?>" onclick="return confirm('Bloquer ce vendeur ?')">Bloquer vendeur</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>