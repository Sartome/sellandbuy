<?php $pageTitle = $pageTitle ?? 'Créer une enchère'; ?>
<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Créer une enchère</h1>
    <?php if (!empty($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="form-group">
            <label for="starting_price">Prix de départ (€)</label>
            <input required type="number" step="0.01" min="0" name="starting_price" id="starting_price">
        </div>
        <div class="form-group">
            <label for="ends_at">Date/heure de fin</label>
            <input required type="datetime-local" name="ends_at" id="ends_at">
        </div>
        <button type="submit" class="btn">Créer</button>
    </form>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


