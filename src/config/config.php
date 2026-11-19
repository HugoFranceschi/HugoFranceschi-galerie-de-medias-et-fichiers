<?php

// Chemins absolus : le site fonctionne quel que soit le répertoire courant
// (Apache, PHP-FPM, ligne de commande) et quel que soit l'OS.
define('BASE_PATH', dirname(__DIR__, 2));
define('SRC', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CONTROLLERS', SRC . 'Controllers' . DIRECTORY_SEPARATOR);
define('MODELS', SRC . 'Models' . DIRECTORY_SEPARATOR);
define('VIEWS', SRC . 'Views' . DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', BASE_PATH . DIRECTORY_SEPARATOR . 'public');
define('UPLOADS_DIR', PUBLIC_DIR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'Img');

// Préfixe d'URL si le site n'est pas servi à la racine du domaine.
// Laisser vide pour https://exemple.com/
// Mettre par exemple /galerie pour https://exemple.com/galerie/
$baseUrl = getenv('BASE_URL');
$baseUrl = $baseUrl !== false ? $baseUrl : ($_SERVER['BASE_URL'] ?? '');
$baseUrl = trim($baseUrl);

// Si une URL complète est fournie, on ne garde que son chemin.
if (preg_match('#^https?://#i', $baseUrl)) {
    $baseUrl = (string) parse_url($baseUrl, PHP_URL_PATH);
}

// Normalise en /galerie (sans slash final), ou '' pour la racine.
$baseUrl = rtrim('/' . ltrim($baseUrl, '/'), '/');
define('BASE_URL', $baseUrl);

// Les identifiants peuvent être surchargés par des variables d'environnement,
// pour ne pas laisser le mot de passe de production dans le code source.
$dbHost = getenv('DB_HOST');
$dbHost = $dbHost !== false ? $dbHost : ($_SERVER['DB_HOST'] ?? '127.0.0.1');

$dbName = getenv('DB_NAME');
$dbName = $dbName !== false ? $dbName : ($_SERVER['DB_NAME'] ?? 'galeriedephoto');

$dbUser = getenv('DB_USER');
$dbUser = $dbUser !== false ? $dbUser : ($_SERVER['DB_USER'] ?? 'root');

$dbPassword = getenv('DB_PASSWORD');
$dbPassword = $dbPassword !== false ? $dbPassword : ($_SERVER['DB_PASSWORD'] ?? '');

$appDebug = getenv('APP_DEBUG');
$appDebug = $appDebug !== false ? $appDebug : ($_SERVER['APP_DEBUG'] ?? '');

define('HOST', $dbHost);
define('DATABASE', $dbName);
define('USER', $dbUser);
define('PASSWORD', $dbPassword);

// Mettre APP_DEBUG à 0 (ou ne pas définir la variable) en production.
define('APP_DEBUG', $appDebug === '1');

