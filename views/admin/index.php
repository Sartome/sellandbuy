<?php require VIEWS_PATH . '/layouts/header.php'; ?>
<?php require VIEWS_PATH . '/layouts/navbar.php'; ?>

<main class="container">
    <h1>Tableau de bord administrateur</h1>
    <p>Bienvenue dans l'espace d'administration.</p>
    <ul>
        <li><a class="btn" href="<?php echo BASE_URL; ?>/index.php?controller=product&action=index">Voir les produits</a></li>
    </ul>
</main>

<?php require VIEWS_PATH . '/layouts/footer.php'; ?>


