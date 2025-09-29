<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Lima');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
session_start();
require_once "../vendor/autoload.php";

// Use createImmutable (o createMutable si necesitas sobrescribir) y safeLoad para no fallar si falta .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

use Illuminate\Database\Capsule\Manager as Capsule;
use Aura\Router\RouterContainer;

use App\Model\Job;
use Laminas\Diactoros\ServerRequestFactory;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => env('DB_HOST') ?: '127.0.0.1',
    'database'  => env('DB_NAME') ?: 'db_kapitals_prod',
    'username'  => env('DB_USER') ?: 'root',
    'password'  => env('DB_PASS') ?: '',
    'charset'   => env('DB_CHARSET') ?: 'utf8',
    'collation' => env('DB_COLLATION') ?: 'utf8_unicode_ci',
    'prefix'    => env('DB_PREFIX') ?: '',
    'options'   => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ],
]);

$request = ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
$routerContainer = new RouterContainer();
$map = $routerContainer->getMap();

require_once "../routes/web.php";

$matcher = $routerContainer->getMatcher();
$route = $matcher->match($request);

if (!$route) {
    header("Location: /");
} else {
    // add route attributes to the request
    foreach ($route->attributes as $key => $val) {
        $request = $request->withAttribute($key, $val);
    }
    $handlerData = $route->handler;
    $controllerName = $handlerData['Controller'];
    $actionName = $handlerData['Action'];

    $controller = new $controllerName;
    $response = $controller->$actionName($request);

    if (is_object($response)) {
        echo $response->getBody();
    } else {
        echo json_encode($response);
    }
}
