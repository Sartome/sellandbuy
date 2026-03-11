<?php
// config/constants.php

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('HELPERS_PATH', ROOT_PATH . '/helpers');

// URL de base - auto-détectée depuis la requête courante
$_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $_scheme . '://' . $_host);
unset($_scheme, $_host);
// URL des assets (dossier public)
define('ASSETS_URL', BASE_URL . '/public');