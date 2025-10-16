<?php
session_start();

// Charger la configuration
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/helpers/session.php';

// Récupérer les paramètres de routing
$controller = $_GET['controller'] ?? 'product';
$action = $_GET['action'] ?? 'index';

// Charger le contrôleur approprié
$controllerClass = ucfirst($controller) . 'Controller';
$controllerFile = CONTROLLERS_PATH . '/' . $controllerClass . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    $controllerInstance = new $controllerClass();
    
    if (method_exists($controllerInstance, $action)) {
        $controllerInstance->$action();
    } else {
        die("Action non trouvée: $action");
    }
} else {
    die("Contrôleur non trouvé: $controllerClass");
}
