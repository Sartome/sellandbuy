<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Vendre un produit</h1>

    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" required></textarea>
        </div>
        <div class="form-group">
            <label>Prix (€)</label>
            <input type="number" name="prix" min="0" step="0.01" required />
        </div>
        <div class="form-group">
            <label>Image (URL)</label>
            <input type="url" name="image" placeholder="https://..." />
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="id_categorie">
                <?php foreach (($categories ?? []) as $cat): ?>
                    <option value="<?php echo (int)$cat['id_categorie']; ?>">
                        <?php echo htmlspecialchars($cat['lib']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn" type="submit">Publier</button>
    </form>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


