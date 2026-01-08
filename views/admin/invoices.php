<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>🧾 Factures</h1>

    <?php if (empty($invoices)) : ?>
        <p>Aucune facture trouvée.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>PDF</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><?= htmlspecialchars($inv['id_facture']) ?></td>
                        <td><?= htmlspecialchars($inv['date_facture']) ?></td>
                        <td>
                            <?php if (!empty($inv['pdf_facture'])): ?>
                                <a href="<?= htmlspecialchars($inv['pdf_facture']) ?>" target="_blank">Télécharger</a>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="index.php?controller=admin&action=invoices&amp;action_type=delete" onsubmit="return confirm('Supprimer cette facture ?');">
                                <input type="hidden" name="id_facture" value="<?= htmlspecialchars($inv['id_facture']) ?>">
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>