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

require '../src/config/config.php';
require '../vendor/autoload.php';
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

$router = new Template\Router($_SERVER["REQUEST_URI"]);

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
