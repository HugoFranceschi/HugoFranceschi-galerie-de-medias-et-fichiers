<?php
define("SRC", '../src/');
define("CONTROLLERS", '../src/Controllers/');
define("MODELS", '../src/Models/');
define("VIEWS", '../src/Views/');

// Les identifiants peuvent être surchargés par des variables d'environnement,
// pour ne pas laisser le mot de passe de production dans le code source.
define('HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DATABASE', getenv('DB_NAME') ?: 'galeriedephoto');
define('USER', getenv('DB_USER') ?: 'root');
define('PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '');

// Mettre APP_DEBUG à 0 (ou ne pas définir la variable) en production.
define('APP_DEBUG', getenv('APP_DEBUG') === '1');
