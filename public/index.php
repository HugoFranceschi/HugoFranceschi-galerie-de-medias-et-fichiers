<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require BASE_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
require SRC . 'helper.php';

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');

    // On journalise les erreurs techniques mais on n'expose jamais les détails SQL.
    set_exception_handler(function (\Throwable $e) {
        error_log('Erreur non geree : ' . $e->getMessage() . ' dans ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        exit('Une erreur interne est survenue.');
    });
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if (!is_string($requestPath) || $requestPath === '') {
    $requestPath = '/';
}

// Si le site est dans un sous-dossier (BASE_URL=/galerie), on retire
// ce préfixe avant de donner l'URL au routeur.
if (BASE_URL !== '') {
    $basePath = rtrim(BASE_URL, '/');

    if ($requestPath === $basePath || str_starts_with($requestPath, $basePath . '/')) {
        $requestPath = substr($requestPath, strlen($basePath));

        if ($requestPath === '') {
            $requestPath = '/';
        }
    }
}

$router = new Template\Router($requestPath);

// Get Routes
// $router->get('/', "ControllerUser@getLogin");
$router->get('/login', "ControllerUser@getLogin");
$router->get('/logOut', "ControllerUser@getLogOut");
$router->get('/', "ControllerAcceuil@getAcceuil");

// Post Routes
$router->post('/login', "ControllerUser@login");
$router->post('/logOut', "ControllerUser@register");
$router->post('/image', "ControllerAcceuil@postImg");
$router->post('/sup', "ControllerAcceuil@sup");


$router->run();
