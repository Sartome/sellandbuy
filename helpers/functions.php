<?php
// helpers/functions.php

// Auto-load helper classes
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Validator.php';
require_once __DIR__ . '/Logger.php';

/**
 * Sanitize string data (backward compatibility)
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitize($data) {
    return Security::sanitizeString($data);
}

/**
 * Redirect to a location with optional flash message
 * @param string $location Location to redirect to
 * @param string $message Flash message
 * @param string $type Message type (success, error, warning, info)
 */
/**
 * Redirect to a location with optional flash message
 * @param string $location Location to redirect to
 * @param string $message Flash message
 * @param string $type Message type (success, error, warning, info)
 */
function redirect($location, $message = '', $type = 'success') {
    if (!empty($message)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    
    // Log the redirect for debugging
    Logger::debug("Redirecting to: {$location}", ['message' => $message, 'type' => $type]);
    
    header("Location: " . BASE_URL . $location);
    exit;
}

/**
 * Generate nonce for CSP
 * @return string Nonce value
 */
function generateNonce() {
    return base64_encode(random_bytes(16));
}

/**
 * Check if user is logged in
 * @return bool True if logged in
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in (redirect if not)
 * @param string $redirectTo Where to redirect if not logged in
 */
function requireLogin(string $redirectTo = '/index.php?controller=auth&action=login'): void {
    if (!isLoggedIn()) {
        Logger::security('Unauthorized access attempt', [
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]);
        redirect($redirectTo, 'Vous devez être connecté pour accéder à cette page', 'error');
    }
}

/**
 * Get current user ID
 * @return int|null User ID or null
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user email
 * @return string|null User email or null
 */
function getCurrentUserEmail(): ?string {
    return $_SESSION['email'] ?? null;
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