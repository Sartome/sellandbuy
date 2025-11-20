<?php
/**
 * Point d'entrée de l'application Sell & Buy
 *
 * Ce fichier agit comme front-controller :
 *  - initialise l'environnement (sessions, constantes, helpers)
 *  - applique les en-têtes de sécurité recommandés
 *  - résout le contrôleur et l'action à partir des paramètres de requête
 *  - délègue le traitement au contrôleur ciblé
 */

// Démarrer la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once HELPERS_PATH . '/functions.php';
require_once HELPERS_PATH . '/session.php';

// En-têtes de sécurité (documentés dans README.md)
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

/**
 * Renvoie une réponse JSON ou HTML simple pour les erreurs critiques
 */
function respondWithError(int $statusCode, string $message): void {
    http_response_code($statusCode);

    if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        echo json_encode([
            'status' => $statusCode,
            'message' => $message,
        ]);
    } else {
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Erreur</title></head><body>';
        echo '<h1>' . htmlspecialchars($message) . '</h1>';
        echo '<p><a href="' . htmlspecialchars(BASE_URL) . '/index.php">Retour à l\'accueil</a></p>';
        echo '</body></html>';
    }
    exit;
}

// Résolution du contrôleur/action depuis l'URL (?controller=product&action=index)
$controllerParam = strtolower($_GET['controller'] ?? 'product');
$actionParam = $_GET['action'] ?? 'index';

// Ne garder que les caractères alphabétiques pour éviter les inclusions arbitraires
$controllerParam = preg_replace('/[^a-z]/', '', $controllerParam);
$actionParam = preg_replace('/[^a-z]/i', '', $actionParam);

if ($controllerParam === '') {
    $controllerParam = 'product';
}

if ($actionParam === '') {
    $actionParam = 'index';
}

$controllerClass = ucfirst($controllerParam) . 'Controller';
$controllerFile = CONTROLLERS_PATH . '/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    respondWithError(404, "Contrôleur introuvable : $controllerClass");
}

require_once $controllerFile;

if (!class_exists($controllerClass)) {
    respondWithError(500, "Classe de contrôleur manquante : $controllerClass");
}

$controllerInstance = new $controllerClass();

if (!method_exists($controllerInstance, $actionParam)) {
    respondWithError(404, "Action introuvable : $controllerClass::$actionParam");
}

try {
    call_user_func([$controllerInstance, $actionParam]);
} catch (Throwable $e) {
    error_log('[Sell&Buy] Fatal error: ' . $e->getMessage() . '\n' . $e->getTraceAsString());
    respondWithError(500, 'Une erreur interne est survenue. Veuillez réessayer plus tard.');
}