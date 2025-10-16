<?php
// helpers/session.php

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Ancienne notion de directeur supprimée
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/index.php?controller=auth&action=login', 'Veuillez vous connecter', 'error');
    }
}

// Protection directeur désactivée
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('/index.php?controller=product&action=index', 'Accès administrateur requis', 'error');
    }
}
