<?php
// helpers/functions.php

function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($location, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: " . BASE_URL . $location);
    exit;
}

function generateNonce() {
    return base64_encode(random_bytes(16));
}

/**
 * Vérifie si l'utilisateur a les privilèges d'administrateur
 * Les administrateurs ont accès à toutes les fonctionnalités (vendeur + utilisateur)
 * @return bool True si l'utilisateur est administrateur
 */
function isAdmin() {
    return !empty($_SESSION['is_admin']);
}

/**
 * Vérifie si l'utilisateur peut créer des produits (vendeur ou admin)
 * @return bool True si l'utilisateur peut créer des produits
 */
function canCreateProducts() {
    if (isAdmin()) {
        return true; // Les admins ont tous les privilèges
    }
    
    // Vérifier si l'utilisateur est vendeur
    require_once MODELS_PATH . '/Vendeur.php';
    $vendeurModel = new Vendeur();
    $vendeur = $vendeurModel->findByUserId((int)($_SESSION['user_id'] ?? 0));
    return $vendeur !== false;
}

/**
 * Vérifie si l'utilisateur peut créer des enchères (vendeur ou admin)
 * @return bool True si l'utilisateur peut créer des enchères
 */
function canCreateAuctions() {
    return canCreateProducts(); // Même logique que pour les produits
}

/**
 * Formate une taille de fichier en bytes vers une unité lisible
 * @param float $size Taille en bytes
 * @param int $precision Nombre de décimales
 * @return string Taille formatée avec unité
 */
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}

/**
 * Récupère le taux de taxe actuel
 * @return float
 */
function getTaxRate() {
    require_once MODELS_PATH . '/SiteSettings.php';
    $settings = new SiteSettings();
    return $settings->getTaxRate();
}

/**
 * Vérifie si les taxes sont activées
 * @return bool
 */
function isTaxEnabled() {
    require_once MODELS_PATH . '/SiteSettings.php';
    $settings = new SiteSettings();
    return $settings->isTaxEnabled();
}

/**
 * Calcule le montant de la taxe
 * @param float $amount
 * @return float
 */
function calculateTax($amount) {
    require_once MODELS_PATH . '/SiteSettings.php';
    $settings = new SiteSettings();
    return $settings->calculateTax($amount);
}

/**
 * Calcule le prix TTC
 * @param float $amount
 * @return float
 */
function calculatePriceWithTax($amount) {
    require_once MODELS_PATH . '/SiteSettings.php';
    $settings = new SiteSettings();
    return $settings->calculatePriceWithTax($amount);
}

/**
 * Calcule le prix HT
 * @param float $amount
 * @return float
 */
function calculatePriceWithoutTax($amount) {
    require_once MODELS_PATH . '/SiteSettings.php';
    $settings = new SiteSettings();
    return $settings->calculatePriceWithoutTax($amount);
}