<?php
/**
 * Gestionnaire de sessions et d'authentification
 * 
 * Ce fichier contient les fonctions utilitaires pour gérer
 * les sessions utilisateur et les contrôles d'accès.
 */

/**
 * Vérifie si un utilisateur est connecté
 * @return bool True si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


/**
 * Redirige vers la page de connexion si l'utilisateur n'est pas connecté
 * Affiche un message d'erreur approprié
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/index.php?controller=auth&action=login', 'Veuillez vous connecter', 'error');
    }
}

/**
 * Vérifie les droits d'administrateur
 * Redirige vers la page d'accueil si l'utilisateur n'est pas admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('/index.php?controller=product&action=index', 'Accès administrateur requis', 'error');
    }
}
