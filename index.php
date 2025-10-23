<?php
/**
 * Point d'entrée principal de l'application
 * 
 * Ce fichier gère le routing MVC et initialise l'application.
 * Il charge la configuration, les helpers et route les requêtes
 * vers les contrôleurs appropriés.
 */

session_start();

// Headers de sécurité pour protéger contre les attaques courantes
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Chargement de la configuration et des helpers
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/session.php';

// Récupération des paramètres de routing depuis l'URL
$controller = $_GET['controller'] ?? 'product';
$action = $_GET['action'] ?? 'index';

// Chargement et instanciation du contrôleur approprié
$controllerClass = ucfirst($controller) . 'Controller';
$controllerFile = CONTROLLERS_PATH . '/' . $controllerClass . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    $controllerInstance = new $controllerClass();
    
    // Vérification de l'existence de l'action demandée
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        die("Action non trouvée: $action");
    }
} else {
    die("Contrôleur non trouvé: $controllerClass");
}
