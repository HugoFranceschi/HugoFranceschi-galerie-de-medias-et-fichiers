<?php
namespace Template;
use Template\Route;

//use App\Controllers\UserController;
/** Class Router **/

class Router {

    private $url;
    private $routes = [];

    public function __construct($url){
        $this->url = $url;
    }

    public function get($path, $callable) {
        $route = new Route($path, $callable);
        $this->routes["GET"][] = $route;
        return $route;
    }

    public function post($path, $callable) {
        $route = new Route($path, $callable);
        $this->routes["POST"][] = $route;
        return $route;
    }

    public function run() {
        // HEAD doit renvoyer les mêmes en-têtes qu'un GET, sans le corps.
        $method = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD'
            ? 'GET'
            : ($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!isset($this->routes[$method])) {
            http_response_code(405);
            header('Allow: GET, POST');
            exit('Méthode non autorisée.');
        }

        foreach ($this->routes[$method] as $route) {
            if ($route->match($this->url)) {
                return $route->call();
            }
        }

        http_response_code(404);
        require VIEWS . '404.php';
    }

}
