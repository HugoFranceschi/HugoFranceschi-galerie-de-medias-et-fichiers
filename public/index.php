<?php

session_start();

require '../src/config/config.php';
require '../vendor/autoload.php';
require SRC . 'helper.php';

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
